<?php

declare(strict_types=1);

namespace Maispace\Account\Domain\Model;

use TYPO3\CMS\Extbase\Domain\Model\FrontendUser as BaseFrontendUser;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class FrontendUser extends BaseFrontendUser
{
    /**
     * @var ObjectStorage<Interest>
     */
    protected ObjectStorage $interests;

    protected bool $newsletterOptin = false;

    protected bool $reminderEnabled = false;

    protected string $memberReference = '';

    protected string $mfaSecret = '';

    protected ?string $mfaBackupCodes = null;

    protected bool $mfaEnabled = false;

    protected string $confirmationToken = '';

    protected bool $confirmed = false;

    public function __construct()
    {
        parent::__construct();
        $this->interests = new ObjectStorage();
    }

    public function initializeObject(): void
    {
        $this->interests = new ObjectStorage();
    }

    public function getInterests(): ObjectStorage
    {
        return $this->interests;
    }

    public function setInterests(ObjectStorage $interests): void
    {
        $this->interests = $interests;
    }

    public function addInterest(Interest $interest): void
    {
        $this->interests->attach($interest);
    }

    public function removeInterest(Interest $interest): void
    {
        $this->interests->detach($interest);
    }

    public function isNewsletterOptin(): bool
    {
        return $this->newsletterOptin;
    }

    public function setNewsletterOptin(bool $newsletterOptin): void
    {
        $this->newsletterOptin = $newsletterOptin;
    }

    public function isReminderEnabled(): bool
    {
        return $this->reminderEnabled;
    }

    public function setReminderEnabled(bool $reminderEnabled): void
    {
        $this->reminderEnabled = $reminderEnabled;
    }

    public function getMemberReference(): string
    {
        return $this->memberReference;
    }

    public function setMemberReference(string $memberReference): void
    {
        $this->memberReference = $memberReference;
    }

    public function getMfaSecret(): string
    {
        return $this->mfaSecret;
    }

    public function setMfaSecret(string $mfaSecret): void
    {
        $this->mfaSecret = $mfaSecret;
    }

    public function getMfaBackupCodes(): array
    {
        if (empty($this->mfaBackupCodes)) {
            return [];
        }
        return json_decode($this->mfaBackupCodes, true) ?? [];
    }

    public function setMfaBackupCodes(array $backupCodes): void
    {
        $this->mfaBackupCodes = json_encode($backupCodes);
    }

    public function isMfaEnabled(): bool
    {
        return $this->mfaEnabled;
    }

    public function setMfaEnabled(bool $mfaEnabled): void
    {
        $this->mfaEnabled = $mfaEnabled;
    }

    public function getConfirmationToken(): string
    {
        return $this->confirmationToken;
    }

    public function setConfirmationToken(string $confirmationToken): void
    {
        $this->confirmationToken = $confirmationToken;
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed;
    }

    public function setConfirmed(bool $confirmed): void
    {
        $this->confirmed = $confirmed;
    }
}
