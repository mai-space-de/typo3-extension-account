<?php

declare(strict_types=1);

namespace Maispace\Account\Domain\Model;

use TYPO3\CMS\Extbase\Domain\Model\FrontendUser as BaseFrontendUser;

class FrontendUser extends BaseFrontendUser
{
    protected string $txAccountMemberRef = '';

    /**
     * Comma-separated interest identifiers
     */
    protected string $txAccountInterests = '';

    protected bool $txAccountNewsletterOptin = false;

    protected int $txAccountNewsletterOptinDate = 0;

    protected bool $txAccountRemindersOptin = false;

    protected bool $txAccountEmailConfirmed = false;

    protected string $txAccountConfirmationToken = '';

    protected int $txAccountConfirmationTokenExpires = 0;

    protected string $txAccountPasswordResetToken = '';

    protected int $txAccountPasswordResetTokenExpires = 0;

    /**
     * Encrypted TOTP secret
     */
    protected string $txAccountMfaSecret = '';

    protected bool $txAccountMfaEnabled = false;

    /**
     * JSON-encoded array of hashed backup codes
     */
    protected string $txAccountMfaBackupCodes = '';

    // --- Member reference ---

    public function getMemberRef(): string
    {
        return $this->txAccountMemberRef;
    }

    public function setMemberRef(string $memberRef): void
    {
        $this->txAccountMemberRef = $memberRef;
    }

    // --- Interests ---

    public function getInterests(): array
    {
        if ($this->txAccountInterests === '') {
            return [];
        }
        return array_filter(array_map('trim', explode(',', $this->txAccountInterests)));
    }

    public function setInterests(array $interests): void
    {
        $this->txAccountInterests = implode(',', array_map('trim', $interests));
    }

    public function getTxAccountInterests(): string
    {
        return $this->txAccountInterests;
    }

    public function setTxAccountInterests(string $interests): void
    {
        $this->txAccountInterests = $interests;
    }

    // --- Newsletter ---

    public function isNewsletterOptin(): bool
    {
        return $this->txAccountNewsletterOptin;
    }

    public function setNewsletterOptin(bool $newsletterOptin): void
    {
        $this->txAccountNewsletterOptin = $newsletterOptin;
    }

    public function getNewsletterOptinDate(): int
    {
        return $this->txAccountNewsletterOptinDate;
    }

    public function setNewsletterOptinDate(int $date): void
    {
        $this->txAccountNewsletterOptinDate = $date;
    }

    // --- Reminders ---

    public function isRemindersOptin(): bool
    {
        return $this->txAccountRemindersOptin;
    }

    public function setRemindersOptin(bool $remindersOptin): void
    {
        $this->txAccountRemindersOptin = $remindersOptin;
    }

    // --- Email confirmation ---

    public function isEmailConfirmed(): bool
    {
        return $this->txAccountEmailConfirmed;
    }

    public function setEmailConfirmed(bool $emailConfirmed): void
    {
        $this->txAccountEmailConfirmed = $emailConfirmed;
    }

    public function getConfirmationToken(): string
    {
        return $this->txAccountConfirmationToken;
    }

    public function setConfirmationToken(string $token): void
    {
        $this->txAccountConfirmationToken = $token;
    }

    public function getConfirmationTokenExpires(): int
    {
        return $this->txAccountConfirmationTokenExpires;
    }

    public function setConfirmationTokenExpires(int $expires): void
    {
        $this->txAccountConfirmationTokenExpires = $expires;
    }

    // --- Password reset ---

    public function getPasswordResetToken(): string
    {
        return $this->txAccountPasswordResetToken;
    }

    public function setPasswordResetToken(string $token): void
    {
        $this->txAccountPasswordResetToken = $token;
    }

    public function getPasswordResetTokenExpires(): int
    {
        return $this->txAccountPasswordResetTokenExpires;
    }

    public function setPasswordResetTokenExpires(int $expires): void
    {
        $this->txAccountPasswordResetTokenExpires = $expires;
    }

    // --- MFA ---

    public function getMfaSecret(): string
    {
        return $this->txAccountMfaSecret;
    }

    public function setMfaSecret(string $secret): void
    {
        $this->txAccountMfaSecret = $secret;
    }

    public function isMfaEnabled(): bool
    {
        return $this->txAccountMfaEnabled;
    }

    public function setMfaEnabled(bool $mfaEnabled): void
    {
        $this->txAccountMfaEnabled = $mfaEnabled;
    }

    public function getMfaBackupCodes(): array
    {
        if ($this->txAccountMfaBackupCodes === '') {
            return [];
        }
        return json_decode($this->txAccountMfaBackupCodes, true) ?? [];
    }

    public function setMfaBackupCodes(array $codes): void
    {
        $this->txAccountMfaBackupCodes = json_encode($codes);
    }

    public function getTxAccountMfaBackupCodes(): string
    {
        return $this->txAccountMfaBackupCodes;
    }

    public function setTxAccountMfaBackupCodes(string $codes): void
    {
        $this->txAccountMfaBackupCodes = $codes;
    }
}
