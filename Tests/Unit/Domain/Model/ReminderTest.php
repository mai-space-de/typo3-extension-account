<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Tests\Unit\Domain\Model;

use Maispace\MaiAccount\Domain\Model\Reminder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ReminderTest extends TestCase
{
    // ── Default values ──────────────────────────────────────────────────────

    #[Test]
    public function defaultFeUserIsZero(): void
    {
        $subject = new Reminder();
        self::assertSame(0, $subject->getFeUser());
    }

    #[Test]
    public function defaultTitleIsEmptyString(): void
    {
        $subject = new Reminder();
        self::assertSame('', $subject->getTitle());
    }

    #[Test]
    public function defaultRemindAtIsNull(): void
    {
        $subject = new Reminder();
        self::assertNull($subject->getRemindAt());
    }

    #[Test]
    public function defaultSentIsFalse(): void
    {
        $subject = new Reminder();
        self::assertFalse($subject->isSent());
    }

    // ── feUser getter / setter ──────────────────────────────────────────────

    #[Test]
    public function setFeUserStoresTheValue(): void
    {
        $subject = new Reminder();
        $subject->setFeUser(42);
        self::assertSame(42, $subject->getFeUser());
    }

    #[Test]
    public function setFeUserOverwritesPreviousValue(): void
    {
        $subject = new Reminder();
        $subject->setFeUser(42);
        $subject->setFeUser(99);
        self::assertSame(99, $subject->getFeUser());
    }

    // ── title getter / setter ───────────────────────────────────────────────

    #[Test]
    public function setTitleStoresTheValue(): void
    {
        $subject = new Reminder();
        $subject->setTitle('Arzttermin');
        self::assertSame('Arzttermin', $subject->getTitle());
    }

    #[Test]
    public function setTitleOverwritesPreviousValue(): void
    {
        $subject = new Reminder();
        $subject->setTitle('Arzttermin');
        $subject->setTitle('Meeting');
        self::assertSame('Meeting', $subject->getTitle());
    }

    // ── remindAt getter / setter ────────────────────────────────────────────

    #[Test]
    public function setRemindAtStoresTheDateTimeImmutable(): void
    {
        $subject = new Reminder();
        $date = new \DateTimeImmutable('2026-06-01 10:00:00');
        $subject->setRemindAt($date);
        self::assertSame($date, $subject->getRemindAt());
    }

    #[Test]
    public function setRemindAtNullResetsToNull(): void
    {
        $subject = new Reminder();
        $subject->setRemindAt(new \DateTimeImmutable('2026-06-01'));
        $subject->setRemindAt(null);
        self::assertNull($subject->getRemindAt());
    }

    #[Test]
    public function remindAtReturnedDateTimeImmutableIsUnchanged(): void
    {
        $subject = new Reminder();
        $date = new \DateTimeImmutable('2026-06-15 08:30:00');
        $subject->setRemindAt($date);
        self::assertSame('2026-06-15 08:30:00', $subject->getRemindAt()?->format('Y-m-d H:i:s'));
    }

    // ── sent getter / setter ────────────────────────────────────────────────

    #[Test]
    public function setSentTrueStoresTrue(): void
    {
        $subject = new Reminder();
        $subject->setSent(true);
        self::assertTrue($subject->isSent());
    }

    #[Test]
    public function setSentFalseStoresFalse(): void
    {
        $subject = new Reminder();
        $subject->setSent(true);
        $subject->setSent(false);
        self::assertFalse($subject->isSent());
    }

    // ── instance isolation ──────────────────────────────────────────────────

    #[Test]
    public function twoInstancesHaveIndependentFeUsers(): void
    {
        $subject1 = new Reminder();
        $subject2 = new Reminder();
        $subject1->setFeUser(7);
        self::assertSame(0, $subject2->getFeUser());
    }

    #[Test]
    public function twoInstancesHaveIndependentTitles(): void
    {
        $subject1 = new Reminder();
        $subject2 = new Reminder();
        $subject1->setTitle('Test');
        self::assertSame('', $subject2->getTitle());
    }

    #[Test]
    public function twoInstancesHaveIndependentSentFlags(): void
    {
        $subject1 = new Reminder();
        $subject2 = new Reminder();
        $subject1->setSent(true);
        self::assertFalse($subject2->isSent());
    }
}
