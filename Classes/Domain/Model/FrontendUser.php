<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Domain\Model;

use TYPO3\CMS\Extbase\Domain\Model\FrontendUser as CoreFrontendUser;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class FrontendUser extends CoreFrontendUser
{
    protected bool $txMaiaccountMfaEnabled = false;

    protected string $txMaiaccountMfaSecret = '';

    protected bool $txMaiaccountNewsletterOptin = false;

    protected int $txMaiaccountMemberUid = 0;

    /**
     * @var ObjectStorage<Interest>
     */
    protected ObjectStorage $txMaiaccountInterests;

    public function __construct()
    {
        parent::__construct();
        $this->txMaiaccountInterests = new ObjectStorage();
    }

    public function initializeObject(): void
    {
        $this->txMaiaccountInterests = new ObjectStorage();
    }

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

    public function getTxMaiaccountMemberUid(): int
    {
        return $this->txMaiaccountMemberUid;
    }

    public function setTxMaiaccountMemberUid(int $uid): void
    {
        $this->txMaiaccountMemberUid = $uid;
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
