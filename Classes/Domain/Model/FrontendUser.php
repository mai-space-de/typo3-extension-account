<?php

declare(strict_types=1);

namespace Maispace\Account\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class FrontendUser extends AbstractEntity
{
    // Standard fe_users fields
    protected string $username = '';
    protected string $password = '';
    protected string $email = '';
    protected string $firstName = '';
    protected string $lastName = '';
    protected string $name = '';
    protected bool $disable = false;

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
        $this->interests = new ObjectStorage();
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function isDisabled(): bool
    {
        return $this->disable;
    }

    public function setDisable(bool $disable): void
    {
        $this->disable = $disable;
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
