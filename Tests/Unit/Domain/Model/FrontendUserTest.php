<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Tests\Unit\Domain\Model;

use Maispace\MaiAccount\Domain\Model\FrontendUser;
use Maispace\MaiAccount\Domain\Model\Interest;
use Maispace\MaiMember\Domain\Model\Member;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

final class FrontendUserTest extends TestCase
{
    // ── Class hierarchy ─────────────────────────────────────────────────────

    #[Test]
    public function frontendUserExtendsAbstractEntity(): void
    {
        self::assertInstanceOf(AbstractEntity::class, new FrontendUser());
    }

    // ── Default values — standard fe_users fields ────────────────────────────

    #[Test]
    public function defaultUsernameIsEmptyString(): void
    {
        $subject = new FrontendUser();
        self::assertSame('', $subject->getUsername());
    }

    #[Test]
    public function defaultFirstNameIsEmptyString(): void
    {
        $subject = new FrontendUser();
        self::assertSame('', $subject->getFirstName());
    }

    #[Test]
    public function defaultLastNameIsEmptyString(): void
    {
        $subject = new FrontendUser();
        self::assertSame('', $subject->getLastName());
    }

    #[Test]
    public function defaultEmailIsEmptyString(): void
    {
        $subject = new FrontendUser();
        self::assertSame('', $subject->getEmail());
    }

    // ── Default values — extension fields ───────────────────────────────────

    #[Test]
    public function defaultMfaEnabledIsFalse(): void
    {
        $subject = new FrontendUser();
        self::assertFalse($subject->isTxMaiaccountMfaEnabled());
    }

    #[Test]
    public function defaultMfaSecretIsEmptyString(): void
    {
        $subject = new FrontendUser();
        self::assertSame('', $subject->getTxMaiaccountMfaSecret());
    }

    #[Test]
    public function defaultNewsletterOptinIsFalse(): void
    {
        $subject = new FrontendUser();
        self::assertFalse($subject->isTxMaiaccountNewsletterOptin());
    }

    #[Test]
    public function interestsIsObjectStorageAfterConstruction(): void
    {
        $subject = new FrontendUser();
        self::assertInstanceOf(ObjectStorage::class, $subject->getTxMaiaccountInterests());
    }

    #[Test]
    public function interestsIsEmptyAfterConstruction(): void
    {
        $subject = new FrontendUser();
        self::assertCount(0, $subject->getTxMaiaccountInterests());
    }

    // ── initializeObject ────────────────────────────────────────────────────

    #[Test]
    public function initializeObjectCreatesFreshObjectStorage(): void
    {
        $subject = new FrontendUser();
        $original = $subject->getTxMaiaccountInterests();
        $subject->initializeObject();
        self::assertNotSame($original, $subject->getTxMaiaccountInterests());
    }

    #[Test]
    public function initializeObjectCreatesEmptyObjectStorage(): void
    {
        $subject = new FrontendUser();
        $subject->initializeObject();
        self::assertCount(0, $subject->getTxMaiaccountInterests());
    }

    // ── username getter / setter ────────────────────────────────────────────

    #[Test]
    public function setUsernameStoresTheValue(): void
    {
        $subject = new FrontendUser();
        $subject->setUsername('john.doe');
        self::assertSame('john.doe', $subject->getUsername());
    }

    #[Test]
    public function setUsernameOverwritesPreviousValue(): void
    {
        $subject = new FrontendUser();
        $subject->setUsername('john.doe');
        $subject->setUsername('jane.doe');
        self::assertSame('jane.doe', $subject->getUsername());
    }

    // ── firstName getter / setter ───────────────────────────────────────────

    #[Test]
    public function setFirstNameStoresTheValue(): void
    {
        $subject = new FrontendUser();
        $subject->setFirstName('John');
        self::assertSame('John', $subject->getFirstName());
    }

    // ── lastName getter / setter ────────────────────────────────────────────

    #[Test]
    public function setLastNameStoresTheValue(): void
    {
        $subject = new FrontendUser();
        $subject->setLastName('Doe');
        self::assertSame('Doe', $subject->getLastName());
    }

    // ── email getter / setter ───────────────────────────────────────────────

    #[Test]
    public function setEmailStoresTheValue(): void
    {
        $subject = new FrontendUser();
        $subject->setEmail('john@example.com');
        self::assertSame('john@example.com', $subject->getEmail());
    }

    // ── mfaEnabled getter / setter ──────────────────────────────────────────

    #[Test]
    public function setMfaEnabledTrueStoresTrue(): void
    {
        $subject = new FrontendUser();
        $subject->setTxMaiaccountMfaEnabled(true);
        self::assertTrue($subject->isTxMaiaccountMfaEnabled());
    }

    #[Test]
    public function setMfaEnabledFalseStoresFalse(): void
    {
        $subject = new FrontendUser();
        $subject->setTxMaiaccountMfaEnabled(true);
        $subject->setTxMaiaccountMfaEnabled(false);
        self::assertFalse($subject->isTxMaiaccountMfaEnabled());
    }

    // ── mfaSecret getter / setter ───────────────────────────────────────────

    #[Test]
    public function setMfaSecretStoresTheValue(): void
    {
        $subject = new FrontendUser();
        $subject->setTxMaiaccountMfaSecret('JBSWY3DPEHPK3PXP');
        self::assertSame('JBSWY3DPEHPK3PXP', $subject->getTxMaiaccountMfaSecret());
    }

    // ── newsletterOptin getter / setter ─────────────────────────────────────

    #[Test]
    public function setNewsletterOptinTrueStoresTrue(): void
    {
        $subject = new FrontendUser();
        $subject->setTxMaiaccountNewsletterOptin(true);
        self::assertTrue($subject->isTxMaiaccountNewsletterOptin());
    }

    #[Test]
    public function setNewsletterOptinFalseStoresFalse(): void
    {
        $subject = new FrontendUser();
        $subject->setTxMaiaccountNewsletterOptin(true);
        $subject->setTxMaiaccountNewsletterOptin(false);
        self::assertFalse($subject->isTxMaiaccountNewsletterOptin());
    }

    // ── memberUid getter / setter ───────────────────────────────────────────

    #[Test]
    public function defaultMemberUidIsNull(): void
    {
        $subject = new FrontendUser();
        self::assertNull($subject->getTxMaiaccountMemberUid());
    }

    #[Test]
    public function setMemberUidStoresMemberInstance(): void
    {
        $subject = new FrontendUser();
        $member = new Member();
        $subject->setTxMaiaccountMemberUid($member);
        self::assertSame($member, $subject->getTxMaiaccountMemberUid());
    }

    #[Test]
    public function setMemberUidOverwritesPreviousValue(): void
    {
        $subject = new FrontendUser();
        $first = new Member();
        $second = new Member();
        $subject->setTxMaiaccountMemberUid($first);
        $subject->setTxMaiaccountMemberUid($second);
        self::assertSame($second, $subject->getTxMaiaccountMemberUid());
    }

    // ── interests getter / setter ───────────────────────────────────────────

    #[Test]
    public function setInterestsStoresTheObjectStorage(): void
    {
        $subject = new FrontendUser();
        $storage = new ObjectStorage();
        $interest = new Interest();
        $storage->attach($interest);
        $subject->setTxMaiaccountInterests($storage);
        self::assertSame($storage, $subject->getTxMaiaccountInterests());
    }

    // ── instance isolation ──────────────────────────────────────────────────

    #[Test]
    public function twoInstancesHaveIndependentUsernames(): void
    {
        $subject1 = new FrontendUser();
        $subject2 = new FrontendUser();
        $subject1->setUsername('alice');
        self::assertSame('', $subject2->getUsername());
    }

    #[Test]
    public function twoInstancesHaveIndependentMfaFlags(): void
    {
        $subject1 = new FrontendUser();
        $subject2 = new FrontendUser();
        $subject1->setTxMaiaccountMfaEnabled(true);
        self::assertFalse($subject2->isTxMaiaccountMfaEnabled());
    }

    #[Test]
    public function twoInstancesHaveIndependentInterestsStorages(): void
    {
        $subject1 = new FrontendUser();
        $subject2 = new FrontendUser();
        self::assertNotSame($subject1->getTxMaiaccountInterests(), $subject2->getTxMaiaccountInterests());
    }
}
