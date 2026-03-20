<?php

declare(strict_types=1);

namespace Maispace\Account\Tests\Unit\Domain\Model;

use Maispace\Account\Domain\Model\FrontendUser;
use PHPUnit\Framework\TestCase;

class FrontendUserTest extends TestCase
{
    private FrontendUser $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new FrontendUser();
    }

    public function testGetInterestsReturnsEmptyArrayByDefault(): void
    {
        self::assertSame([], $this->subject->getInterests());
    }

    public function testSetAndGetInterests(): void
    {
        $this->subject->setInterests(['culture', 'sports', 'nature']);

        self::assertSame(['culture', 'sports', 'nature'], $this->subject->getInterests());
    }

    public function testSetInterestsTrimsEntries(): void
    {
        $this->subject->setInterests([' culture ', ' sports ']);

        self::assertSame(['culture', 'sports'], $this->subject->getInterests());
    }

    public function testSetInterestsFiltersEmptyEntries(): void
    {
        $this->subject->setInterests(['culture', '', 'sports']);

        self::assertSame(['culture', 'sports'], $this->subject->getInterests());
    }

    public function testGetMfaBackupCodesReturnsEmptyArrayByDefault(): void
    {
        self::assertSame([], $this->subject->getMfaBackupCodes());
    }

    public function testSetAndGetMfaBackupCodes(): void
    {
        $codes = ['AAAAA-BBBBB', 'CCCCC-DDDDD'];
        $this->subject->setMfaBackupCodes($codes);

        self::assertSame($codes, $this->subject->getMfaBackupCodes());
    }

    public function testMfaEnabledIsFalseByDefault(): void
    {
        self::assertFalse($this->subject->isMfaEnabled());
    }

    public function testNewsletterOptinIsFalseByDefault(): void
    {
        self::assertFalse($this->subject->isNewsletterOptin());
    }

    public function testRemindersOptinIsFalseByDefault(): void
    {
        self::assertFalse($this->subject->isRemindersOptin());
    }

    public function testEmailConfirmedIsFalseByDefault(): void
    {
        self::assertFalse($this->subject->isEmailConfirmed());
    }

    public function testMemberRefDefaultIsEmptyString(): void
    {
        self::assertSame('', $this->subject->getMemberRef());
    }

    public function testSetAndGetMemberRef(): void
    {
        $this->subject->setMemberRef('MS-2024-00042');

        self::assertSame('MS-2024-00042', $this->subject->getMemberRef());
    }
}
