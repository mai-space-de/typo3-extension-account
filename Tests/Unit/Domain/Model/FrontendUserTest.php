<?php

declare(strict_types=1);

namespace Maispace\Account\Tests\Unit\Domain\Model;

use Maispace\Account\Domain\Model\FrontendUser;
use Maispace\Account\Domain\Model\Interest;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class FrontendUserTest extends TestCase
{
    private FrontendUser $subject;

    protected function setUp(): void
    {
        $this->subject = new FrontendUser();
    }

    public function testNewsletterOptinDefaultIsFalse(): void
    {
        self::assertFalse($this->subject->isNewsletterOptin());
    }

    public function testReminderEnabledDefaultIsFalse(): void
    {
        self::assertFalse($this->subject->isReminderEnabled());
    }

    public function testMfaEnabledDefaultIsFalse(): void
    {
        self::assertFalse($this->subject->isMfaEnabled());
    }

    public function testConfirmedDefaultIsFalse(): void
    {
        self::assertFalse($this->subject->isConfirmed());
    }

    public function testMemberReferenceDefaultIsEmpty(): void
    {
        self::assertSame('', $this->subject->getMemberReference());
    }

    public function testSetAndGetNewsletterOptin(): void
    {
        $this->subject->setNewsletterOptin(true);
        self::assertTrue($this->subject->isNewsletterOptin());
    }

    public function testSetAndGetMemberReference(): void
    {
        $this->subject->setMemberReference('REF-001');
        self::assertSame('REF-001', $this->subject->getMemberReference());
    }

    public function testMfaBackupCodesRoundTrip(): void
    {
        $codes = ['CODE1', 'CODE2'];
        $this->subject->setMfaBackupCodes($codes);
        self::assertSame($codes, $this->subject->getMfaBackupCodes());
    }

    public function testMfaBackupCodesEmptyByDefault(): void
    {
        self::assertSame([], $this->subject->getMfaBackupCodes());
    }

    public function testInterestsIsObjectStorageByDefault(): void
    {
        self::assertInstanceOf(ObjectStorage::class, $this->subject->getInterests());
    }

    public function testAddAndRemoveInterest(): void
    {
        $interest = new Interest();
        $this->subject->addInterest($interest);
        self::assertCount(1, $this->subject->getInterests());

        $this->subject->removeInterest($interest);
        self::assertCount(0, $this->subject->getInterests());
    }
}
