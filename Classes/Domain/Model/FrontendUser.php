<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Domain\Model;

use Maispace\MaiMember\Domain\Model\Member;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Extends the TYPO3 fe_users record as an Extbase domain model.
 * TYPO3\CMS\Extbase\Domain\Model\FrontendUser was removed in TYPO3 v14;
 * standard fe_users fields are declared here directly.
 */
class FrontendUser extends AbstractEntity
{
    // ── Standard fe_users fields ────────────────────────────────────────────

    protected string $username = '';

    protected string $firstName = '';

    protected string $lastName = '';

    protected string $email = '';

    // ── Extension fields ────────────────────────────────────────────────────

    protected bool $txMaiaccountMfaEnabled = false;

    protected string $txMaiaccountMfaSecret = '';

    protected bool $txMaiaccountNewsletterOptin = false;

    #[Lazy]
    protected ?Member $txMaiaccountMemberUid = null;

    /**
     * @var ObjectStorage<Interest>
     */
    protected ObjectStorage $txMaiaccountInterests;

    public function __construct()
    {
        $this->txMaiaccountInterests = new ObjectStorage();
    }

    public function initializeObject(): void
    {
        $this->txMaiaccountInterests = new ObjectStorage();
    }

    // ── Standard fe_users getters / setters ─────────────────────────────────

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
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

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    // ── Extension getters / setters ─────────────────────────────────────────

    public function isTxMaiaccountMfaEnabled(): bool
    {
        return $this->txMaiaccountMfaEnabled;
    }

    public function setTxMaiaccountMfaEnabled(bool $enabled): void
    {
        $this->txMaiaccountMfaEnabled = $enabled;
    }

    public function getTxMaiaccountMfaSecret(): string
    {
        return $this->txMaiaccountMfaSecret;
    }

    public function setTxMaiaccountMfaSecret(string $secret): void
    {
        $this->txMaiaccountMfaSecret = $secret;
    }

    public function isTxMaiaccountNewsletterOptin(): bool
    {
        return $this->txMaiaccountNewsletterOptin;
    }

    public function setTxMaiaccountNewsletterOptin(bool $optin): void
    {
        $this->txMaiaccountNewsletterOptin = $optin;
    }

    public function getTxMaiaccountMemberUid(): ?Member
    {
        return $this->txMaiaccountMemberUid;
    }

    public function setTxMaiaccountMemberUid(?Member $member): void
    {
        $this->txMaiaccountMemberUid = $member;
    }

    /**
     * @return ObjectStorage<Interest>
     */
    public function getTxMaiaccountInterests(): ObjectStorage
    {
        return $this->txMaiaccountInterests;
    }

    /**
     * @param ObjectStorage<Interest> $interests
     */
    public function setTxMaiaccountInterests(ObjectStorage $interests): void
    {
        $this->txMaiaccountInterests = $interests;
    }
}
