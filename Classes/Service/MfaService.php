<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Service;

use Maispace\MaiAccount\Domain\Model\FrontendUser;
use Maispace\MaiAccount\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Core\Crypto\Random;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use OTPHP\TOTP;

class MfaService
{
    private const BACKUP_CODE_COUNT = 8;
    private const BACKUP_CODE_LENGTH = 10;

    public function __construct(
        private readonly FrontendUserRepository $frontendUserRepository,
        private readonly PersistenceManagerInterface $persistenceManager,
        private readonly Random $random,
    ) {
    }

    /**
     * Generate a new TOTP instance for the user (without persisting).
     * Returns the TOTP object so the controller can render the QR code / secret.
     */
    public function initSetup(FrontendUser $user, string $issuer = 'maispace'): TOTP
    {
        $totp = TOTP::generate();
        $totp->setLabel($user->getEmail());
        $totp->setIssuer($issuer);

        return $totp;
    }

    /**
     * Verify a TOTP code against a given secret (before MFA is enabled).
     */
    public function verifyCode(string $secret, string $code): bool
    {
        try {
            $totp = TOTP::createFromSecret($secret);
            return $totp->verify($code, null, 1); // allow 1 period window
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Enable MFA for a user after successful TOTP verification.
     * Stores encrypted secret and generates backup codes.
     *
     * @return string[] Plain-text backup codes (shown once to user)
     */
    public function enableMfa(FrontendUser $user, string $secret): array
    {
        $backupCodes = $this->generateBackupCodes();
        $hashedBackupCodes = array_map(
            static fn(string $code): string => password_hash($code, PASSWORD_BCRYPT),
            $backupCodes
        );

        $user->setMfaSecret($this->encryptSecret($secret));
        $user->setMfaEnabled(true);
        $user->setMfaBackupCodes($hashedBackupCodes);

        $this->frontendUserRepository->update($user);
        $this->persistenceManager->persistAll();

        return $backupCodes;
    }

    /**
     * Verify a TOTP code for an MFA-enabled user.
     */
    public function verifyMfa(FrontendUser $user, string $code): bool
    {
        if (!$user->isMfaEnabled()) {
            return false;
        }

        $secret = $this->decryptSecret($user->getMfaSecret());

        return $this->verifyCode($secret, $code);
    }

    /**
     * Verify and consume a backup code (one-time use).
     */
    public function verifyBackupCode(FrontendUser $user, string $code): bool
    {
        $storedCodes = $user->getMfaBackupCodes();
        $normalizedInput = strtolower(str_replace('-', '', trim($code)));

        foreach ($storedCodes as $index => $hashedCode) {
            if (password_verify($normalizedInput, $hashedCode)) {
                // Remove used backup code
                unset($storedCodes[$index]);
                $user->setMfaBackupCodes(array_values($storedCodes));

                $this->frontendUserRepository->update($user);
                $this->persistenceManager->persistAll();

                return true;
            }
        }

        return false;
    }

    /**
     * Disable MFA and clear all MFA data.
     */
    public function disableMfa(FrontendUser $user): void
    {
        $user->setMfaEnabled(false);
        $user->setMfaSecret('');
        $user->setMfaBackupCodes([]);

        $this->frontendUserRepository->update($user);
        $this->persistenceManager->persistAll();
    }

    /**
     * Regenerate backup codes for an already MFA-enabled user.
     *
     * @return string[] New plain-text backup codes
     */
    public function regenerateBackupCodes(FrontendUser $user): array
    {
        $backupCodes = $this->generateBackupCodes();
        $hashedBackupCodes = array_map(
            static fn(string $code): string => password_hash($code, PASSWORD_BCRYPT),
            $backupCodes
        );

        $user->setMfaBackupCodes($hashedBackupCodes);

        $this->frontendUserRepository->update($user);
        $this->persistenceManager->persistAll();

        return $backupCodes;
    }

    /**
     * @return string[]
     */
    private function generateBackupCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < self::BACKUP_CODE_COUNT; $i++) {
            $raw = bin2hex($this->random->generateRandomBytes(self::BACKUP_CODE_LENGTH / 2));
            // Format as XXXXX-XXXXX for readability
            $codes[] = strtoupper(substr($raw, 0, 5) . '-' . substr($raw, 5, 5));
        }
        return $codes;
    }

    /**
     * Simple symmetric encryption for the TOTP secret at rest.
     * In production replace with a proper key management solution.
     */
    private function encryptSecret(string $secret): string
    {
        $key = $this->getEncryptionKey();
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($secret, 'AES-256-CBC', $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    private function decryptSecret(string $encryptedSecret): string
    {
        $key = $this->getEncryptionKey();
        $decoded = base64_decode($encryptedSecret);
        $iv = substr($decoded, 0, 16);
        $data = substr($decoded, 16);
        return (string)openssl_decrypt($data, 'AES-256-CBC', $key, 0, $iv);
    }

    private function getEncryptionKey(): string
    {
        $key = $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] ?? '';
        if ($key === '') {
            throw new \RuntimeException('TYPO3 encryptionKey is not set', 1700000001);
        }
        // Derive a 32-byte key from the TYPO3 encryption key
        return substr(hash('sha256', 'maispace_mfa_' . $key), 0, 32);
    }
}
