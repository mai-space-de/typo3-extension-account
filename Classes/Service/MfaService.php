<?php

declare(strict_types=1);

namespace Maispace\Account\Service;

use Maispace\Account\Domain\Model\FrontendUser;
use Maispace\Account\Domain\Repository\FrontendUserRepository;
use OTPHP\TOTP;
use TYPO3\CMS\Core\Crypto\Random;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

class MfaService
{
    private const BACKUP_CODE_COUNT = 8;
    private const BACKUP_CODE_LENGTH = 8;

    public function __construct(
        private readonly FrontendUserRepository $frontendUserRepository,
        private readonly PersistenceManagerInterface $persistenceManager,
        private readonly Random $random
    ) {}

    /**
     * Generate a new TOTP secret and return setup data.
     *
     * @return array{secret: string, provisioningUri: string}
     */
    public function generateSetupData(FrontendUser $user): array
    {
        $totp = TOTP::generate();
        $totp->setLabel($user->getEmail());
        $totp->setIssuer('Maispace');

        return [
            'secret' => $totp->getSecret(),
            'provisioningUri' => $totp->getProvisioningUri(),
        ];
    }

    /**
     * Enable MFA after verifying the first TOTP code.
     *
     * @return string[] Plain backup codes to show once to the user
     * @throws \InvalidArgumentException When TOTP code is invalid
     */
    public function enable(FrontendUser $user, string $secret, string $code): array
    {
        $totp = TOTP::createFromSecret($secret);
        $totp->setLabel($user->getEmail());
        $totp->setIssuer('Maispace');

        if (!$totp->verify($code)) {
            throw new \InvalidArgumentException('Invalid TOTP code.', 1700000001);
        }

        $plainCodes = $this->generatePlainBackupCodes();
        $hashedCodes = array_map(static fn(string $c) => hash('sha256', $c), $plainCodes);

        $user->setMfaSecret($secret);
        $user->setMfaEnabled(true);
        $user->setMfaBackupCodes($hashedCodes);

        $this->frontendUserRepository->update($user);
        $this->persistenceManager->persistAll();

        return $plainCodes;
    }

    /**
     * Disable MFA for a user.
     */
    public function disable(FrontendUser $user): void
    {
        $user->setMfaEnabled(false);
        $user->setMfaSecret('');
        $user->setMfaBackupCodes([]);

        $this->frontendUserRepository->update($user);
        $this->persistenceManager->persistAll();
    }

    /**
     * Verify a TOTP code or a backup code.
     */
    public function verify(FrontendUser $user, string $code): bool
    {
        if (!$user->isMfaEnabled()) {
            return false;
        }

        $totp = TOTP::createFromSecret($user->getMfaSecret());
        if ($totp->verify($code)) {
            return true;
        }

        return $this->consumeBackupCode($user, $code);
    }

    private function consumeBackupCode(FrontendUser $user, string $code): bool
    {
        $backupCodes = $user->getMfaBackupCodes();
        $hashedInput = hash('sha256', strtoupper($code));

        foreach ($backupCodes as $index => $stored) {
            if (hash_equals($stored, $hashedInput)) {
                unset($backupCodes[$index]);
                $user->setMfaBackupCodes(array_values($backupCodes));
                $this->frontendUserRepository->update($user);
                $this->persistenceManager->persistAll();
                return true;
            }
        }

        return false;
    }

    private function generatePlainBackupCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < self::BACKUP_CODE_COUNT; $i++) {
            $codes[] = strtoupper($this->random->generateRandomHexString(self::BACKUP_CODE_LENGTH));
        }
        return $codes;
    }
}
