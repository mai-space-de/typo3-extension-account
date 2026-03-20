<?php

declare(strict_types=1);

namespace Maispace\Account\Service;

use Maispace\Account\Domain\Model\FrontendUser;
use Maispace\Account\Domain\Repository\FrontendUserRepository;
use Maispace\Account\Event\AccountConfirmedEvent;
use Maispace\Account\Event\AccountRegisteredEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Crypto\Random;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

class RegistrationService
{
    public function __construct(
        private readonly FrontendUserRepository $frontendUserRepository,
        private readonly PersistenceManagerInterface $persistenceManager,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly Random $random
    ) {}

    /**
     * Register a new frontend user. The account is disabled until confirmed.
     *
     * @param array{username: string, email: string, password: string, firstName?: string, lastName?: string} $userData
     */
    public function register(array $userData, int $storagePid, string $confirmationBaseUrl, string $senderAddress, string $senderName): FrontendUser
    {
        $user = GeneralUtility::makeInstance(FrontendUser::class);
        $user->setPid($storagePid);
        $user->setUsername($userData['username']);
        $user->setEmail($userData['email']);
        $user->setPassword($this->hashPassword($userData['password']));
        $user->setFirstName($userData['firstName'] ?? '');
        $user->setLastName($userData['lastName'] ?? '');
        $user->setDisable(true);
        $user->setConfirmed(false);

        $token = $this->random->generateRandomHexString(64);
        $user->setConfirmationToken($token);

        $this->frontendUserRepository->add($user);
        $this->persistenceManager->persistAll();

        $this->sendConfirmationEmail($user, $confirmationBaseUrl, $senderAddress, $senderName);

        $this->eventDispatcher->dispatch(new AccountRegisteredEvent($user));

        return $user;
    }

    /**
     * Confirm a user account by token.
     */
    public function confirm(string $token): ?FrontendUser
    {
        $user = $this->frontendUserRepository->findByConfirmationToken($token);
        if ($user === null) {
            return null;
        }

        $user->setConfirmed(true);
        $user->setConfirmationToken('');
        $user->setDisable(false);

        $this->frontendUserRepository->update($user);
        $this->persistenceManager->persistAll();

        $this->eventDispatcher->dispatch(new AccountConfirmedEvent($user));

        return $user;
    }

    /**
     * Send a password reset email.
     */
    public function initiatePasswordReset(string $email, string $resetBaseUrl, string $senderAddress, string $senderName): bool
    {
        $user = $this->frontendUserRepository->findByEmail($email);
        if ($user === null) {
            return false;
        }

        $token = $this->random->generateRandomHexString(64);
        $user->setConfirmationToken($token);

        $this->frontendUserRepository->update($user);
        $this->persistenceManager->persistAll();

        $this->sendPasswordResetEmail($user, $resetBaseUrl, $senderAddress, $senderName);

        return true;
    }

    /**
     * Reset the password using a token.
     */
    public function resetPassword(string $token, string $newPassword): ?FrontendUser
    {
        $user = $this->frontendUserRepository->findByConfirmationToken($token);
        if ($user === null) {
            return null;
        }

        $user->setPassword($this->hashPassword($newPassword));
        $user->setConfirmationToken('');

        $this->frontendUserRepository->update($user);
        $this->persistenceManager->persistAll();

        return $user;
    }

    private function sendConfirmationEmail(FrontendUser $user, string $baseUrl, string $senderAddress, string $senderName): void
    {
        $confirmationUrl = str_replace('{token}', $user->getConfirmationToken(), $baseUrl);

        $mail = GeneralUtility::makeInstance(MailMessage::class);
        $mail->setFrom([$senderAddress => $senderName])
            ->setTo([$user->getEmail() => $user->getFirstName() . ' ' . $user->getLastName()])
            ->setSubject('Bitte bestätige deine E-Mail-Adresse')
            ->html('<p>Hallo ' . htmlspecialchars($user->getFirstName()) . ',</p>'
                . '<p>Bitte bestätige deine E-Mail-Adresse durch Klick auf den folgenden Link:</p>'
                . '<p><a href="' . htmlspecialchars($confirmationUrl) . '">' . htmlspecialchars($confirmationUrl) . '</a></p>')
            ->send();
    }

    private function sendPasswordResetEmail(FrontendUser $user, string $baseUrl, string $senderAddress, string $senderName): void
    {
        $resetUrl = str_replace('{token}', $user->getConfirmationToken(), $baseUrl);

        $mail = GeneralUtility::makeInstance(MailMessage::class);
        $mail->setFrom([$senderAddress => $senderName])
            ->setTo([$user->getEmail() => $user->getFirstName() . ' ' . $user->getLastName()])
            ->setSubject('Passwort zurücksetzen')
            ->html('<p>Hallo ' . htmlspecialchars($user->getFirstName()) . ',</p>'
                . '<p>Klicke auf den folgenden Link um dein Passwort zurückzusetzen:</p>'
                . '<p><a href="' . htmlspecialchars($resetUrl) . '">' . htmlspecialchars($resetUrl) . '</a></p>')
            ->send();
    }

    private function hashPassword(string $plainPassword): string
    {
        return password_hash($plainPassword, PASSWORD_BCRYPT);
    }
}
