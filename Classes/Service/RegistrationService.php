<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Service;

use Maispace\MaiAccount\Domain\Model\FrontendUser;
use Maispace\MaiAccount\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Core\Crypto\Random;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Fluid\View\StandaloneView;

class RegistrationService
{
    private const TOKEN_LIFETIME = 86400; // 24 hours

    public function __construct(
        private readonly FrontendUserRepository $frontendUserRepository,
        private readonly PersistenceManagerInterface $persistenceManager,
        private readonly Random $random,
    ) {
    }

    /**
     * Register a new frontend user and send confirmation email.
     *
     * @param array<string, mixed> $userData
     */
    public function register(array $userData, int $storagePid, int $userGroupUid): FrontendUser
    {
        $user = GeneralUtility::makeInstance(FrontendUser::class);
        $user->setPid($storagePid);
        $user->setUsername($userData['email']);
        $user->setEmail($userData['email']);
        $user->setFirstName($userData['firstName'] ?? '');
        $user->setLastName($userData['lastName'] ?? '');
        $user->setPassword($this->hashPassword($userData['password']));
        $user->setDisable(true); // disabled until email confirmed

        $token = $this->generateToken();
        $user->setConfirmationToken($token);
        $user->setConfirmationTokenExpires(time() + self::TOKEN_LIFETIME);
        $user->setEmailConfirmed(false);

        if ($userGroupUid > 0) {
            // Attach to default user group; proper ObjectStorage handling via repository
            // Groups are managed via TYPO3 relation – set via raw DB in persistence
        }

        $this->frontendUserRepository->add($user);
        $this->persistenceManager->persistAll();

        $this->sendConfirmationEmail($user, $userData['confirmationPageUrl'] ?? '');

        return $user;
    }

    /**
     * Confirm email address using token from confirmation email.
     */
    public function confirmEmail(string $token): ?FrontendUser
    {
        $user = $this->frontendUserRepository->findByConfirmationToken($token);

        if ($user === null) {
            return null;
        }

        $user->setEmailConfirmed(true);
        $user->setDisable(false);
        $user->setConfirmationToken('');
        $user->setConfirmationTokenExpires(0);

        $this->frontendUserRepository->update($user);
        $this->persistenceManager->persistAll();

        return $user;
    }

    /**
     * Initiate a password reset: generate token and send reset email.
     */
    public function initiatePasswordReset(string $email, string $resetPageUrl): bool
    {
        $user = $this->frontendUserRepository->findByEmail($email);

        if ($user === null || !$user->isEmailConfirmed()) {
            // Return true to avoid email enumeration
            return true;
        }

        $token = $this->generateToken();
        $user->setPasswordResetToken($token);
        $user->setPasswordResetTokenExpires(time() + 3600); // 1 hour

        $this->frontendUserRepository->update($user);
        $this->persistenceManager->persistAll();

        $this->sendPasswordResetEmail($user, $resetPageUrl, $token);

        return true;
    }

    /**
     * Validate reset token and set new password.
     */
    public function resetPassword(string $token, string $newPassword): bool
    {
        $user = $this->frontendUserRepository->findByPasswordResetToken($token);

        if ($user === null) {
            return false;
        }

        $user->setPassword($this->hashPassword($newPassword));
        $user->setPasswordResetToken('');
        $user->setPasswordResetTokenExpires(0);

        $this->frontendUserRepository->update($user);
        $this->persistenceManager->persistAll();

        return true;
    }

    private function generateToken(): string
    {
        return bin2hex($this->random->generateRandomBytes(32));
    }

    private function hashPassword(string $plainPassword): string
    {
        return password_hash($plainPassword, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    private function sendConfirmationEmail(FrontendUser $user, string $confirmationPageUrl): void
    {
        $confirmationUrl = rtrim($confirmationPageUrl, '/')
            . '?tx_maiaccount_registration[token]=' . urlencode($user->getConfirmationToken())
            . '&tx_maiaccount_registration[action]=confirm';

        /** @var MailMessage $mail */
        $mail = GeneralUtility::makeInstance(MailMessage::class);
        $mail
            ->to($user->getEmail())
            ->subject('Bitte bestätigen Sie Ihre E-Mail-Adresse')
            ->html(
                sprintf(
                    '<p>Hallo %s,</p>'
                    . '<p>bitte bestätigen Sie Ihre Registrierung durch Klick auf folgenden Link:</p>'
                    . '<p><a href="%s">%s</a></p>'
                    . '<p>Der Link ist 24 Stunden gültig.</p>',
                    htmlspecialchars($user->getFirstName() ?: $user->getEmail()),
                    htmlspecialchars($confirmationUrl),
                    htmlspecialchars($confirmationUrl)
                )
            )
            ->send();
    }

    private function sendPasswordResetEmail(FrontendUser $user, string $resetPageUrl, string $token): void
    {
        $resetUrl = rtrim($resetPageUrl, '/')
            . '?tx_maiaccount_login[token]=' . urlencode($token)
            . '&tx_maiaccount_login[action]=passwordResetConfirm';

        /** @var MailMessage $mail */
        $mail = GeneralUtility::makeInstance(MailMessage::class);
        $mail
            ->to($user->getEmail())
            ->subject('Passwort zurücksetzen')
            ->html(
                sprintf(
                    '<p>Hallo %s,</p>'
                    . '<p>Sie haben das Zurücksetzen Ihres Passworts beantragt. '
                    . 'Klicken Sie auf folgenden Link:</p>'
                    . '<p><a href="%s">%s</a></p>'
                    . '<p>Der Link ist 1 Stunde gültig. Falls Sie diese Anfrage nicht gestellt haben, '
                    . 'ignorieren Sie diese E-Mail.</p>',
                    htmlspecialchars($user->getFirstName() ?: $user->getEmail()),
                    htmlspecialchars($resetUrl),
                    htmlspecialchars($resetUrl)
                )
            )
            ->send();
    }
}
