<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Tests\Unit\Domain\Model;

use Maispace\MaiAccount\Domain\Model\Story;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

final class StoryTest extends TestCase
{
    // ── Status constants ────────────────────────────────────────────────────

    #[Test]
    public function statusSubmittedConstantHasExpectedValue(): void
    {
        self::assertSame('submitted', Story::STATUS_SUBMITTED);
    }

    #[Test]
    public function statusReviewingConstantHasExpectedValue(): void
    {
        self::assertSame('reviewing', Story::STATUS_REVIEWING);
    }

    #[Test]
    public function statusPublishedConstantHasExpectedValue(): void
    {
        self::assertSame('published', Story::STATUS_PUBLISHED);
    }

    #[Test]
    public function statusRejectedConstantHasExpectedValue(): void
    {
        self::assertSame('rejected', Story::STATUS_REJECTED);
    }

    // ── Default values ──────────────────────────────────────────────────────

    #[Test]
    public function defaultTitleIsEmptyString(): void
    {
        $subject = new Story();
        self::assertSame('', $subject->getTitle());
    }

    #[Test]
    public function defaultContentIsEmptyString(): void
    {
        $subject = new Story();
        self::assertSame('', $subject->getContent());
    }

    #[Test]
    public function defaultFeUserIsZero(): void
    {
        $subject = new Story();
        self::assertSame(0, $subject->getFeUser());
    }

    #[Test]
    public function defaultStatusIsSubmitted(): void
    {
        $subject = new Story();
        self::assertSame(Story::STATUS_SUBMITTED, $subject->getStatus());
    }

    #[Test]
    public function defaultSubmittedAtIsNull(): void
    {
        $subject = new Story();
        self::assertNull($subject->getSubmittedAt());
    }

    #[Test]
    public function defaultPublishedAtIsNull(): void
    {
        $subject = new Story();
        self::assertNull($subject->getPublishedAt());
    }

    #[Test]
    public function mediaIsObjectStorageAfterConstruction(): void
    {
        $subject = new Story();
        self::assertInstanceOf(ObjectStorage::class, $subject->getMedia());
    }

    #[Test]
    public function mediaIsEmptyAfterConstruction(): void
    {
        $subject = new Story();
        self::assertCount(0, $subject->getMedia());
    }

    // ── initializeObject ────────────────────────────────────────────────────

    #[Test]
    public function initializeObjectCreatesFreshObjectStorage(): void
    {
        $subject = new Story();
        $original = $subject->getMedia();
        $subject->initializeObject();
        self::assertNotSame($original, $subject->getMedia());
    }

    #[Test]
    public function initializeObjectCreatesEmptyObjectStorage(): void
    {
        $subject = new Story();
        $subject->initializeObject();
        self::assertCount(0, $subject->getMedia());
    }

    // ── title getter / setter ───────────────────────────────────────────────

    #[Test]
    public function setTitleStoresTheValue(): void
    {
        $subject = new Story();
        $subject->setTitle('My Story');
        self::assertSame('My Story', $subject->getTitle());
    }

    #[Test]
    public function setTitleOverwritesPreviousValue(): void
    {
        $subject = new Story();
        $subject->setTitle('First');
        $subject->setTitle('Second');
        self::assertSame('Second', $subject->getTitle());
    }

    // ── content getter / setter ─────────────────────────────────────────────

    #[Test]
    public function setContentStoresTheValue(): void
    {
        $subject = new Story();
        $subject->setContent('Once upon a time...');
        self::assertSame('Once upon a time...', $subject->getContent());
    }

    // ── feUser getter / setter ──────────────────────────────────────────────

    #[Test]
    public function setFeUserStoresTheValue(): void
    {
        $subject = new Story();
        $subject->setFeUser(5);
        self::assertSame(5, $subject->getFeUser());
    }

    // ── status getter / setter ──────────────────────────────────────────────

    #[Test]
    public function setStatusStoresTheValue(): void
    {
        $subject = new Story();
        $subject->setStatus(Story::STATUS_PUBLISHED);
        self::assertSame(Story::STATUS_PUBLISHED, $subject->getStatus());
    }

    #[Test]
    public function setStatusToReviewingStoresTheValue(): void
    {
        $subject = new Story();
        $subject->setStatus(Story::STATUS_REVIEWING);
        self::assertSame(Story::STATUS_REVIEWING, $subject->getStatus());
    }

    #[Test]
    public function setStatusToRejectedStoresTheValue(): void
    {
        $subject = new Story();
        $subject->setStatus(Story::STATUS_REJECTED);
        self::assertSame(Story::STATUS_REJECTED, $subject->getStatus());
    }

    // ── submittedAt getter / setter ─────────────────────────────────────────

    #[Test]
    public function setSubmittedAtStoresTheDateTimeImmutable(): void
    {
        $subject = new Story();
        $date = new \DateTimeImmutable('2026-01-15 12:00:00');
        $subject->setSubmittedAt($date);
        self::assertSame($date, $subject->getSubmittedAt());
    }

    #[Test]
    public function setSubmittedAtNullResetsToNull(): void
    {
        $subject = new Story();
        $subject->setSubmittedAt(new \DateTimeImmutable());
        $subject->setSubmittedAt(null);
        self::assertNull($subject->getSubmittedAt());
    }

    // ── publishedAt getter / setter ─────────────────────────────────────────

    #[Test]
    public function setPublishedAtStoresTheDateTimeImmutable(): void
    {
        $subject = new Story();
        $date = new \DateTimeImmutable('2026-03-01 09:00:00');
        $subject->setPublishedAt($date);
        self::assertSame($date, $subject->getPublishedAt());
    }

    #[Test]
    public function setPublishedAtNullResetsToNull(): void
    {
        $subject = new Story();
        $subject->setPublishedAt(new \DateTimeImmutable());
        $subject->setPublishedAt(null);
        self::assertNull($subject->getPublishedAt());
    }

    // ── media getter / setter ───────────────────────────────────────────────

    #[Test]
    public function setMediaStoresTheObjectStorage(): void
    {
        $subject = new Story();
        $storage = new ObjectStorage();
        $subject->setMedia($storage);
        self::assertSame($storage, $subject->getMedia());
    }

    // ── instance isolation ──────────────────────────────────────────────────

    #[Test]
    public function twoInstancesHaveIndependentTitles(): void
    {
        $subject1 = new Story();
        $subject2 = new Story();
        $subject1->setTitle('First Story');
        self::assertSame('', $subject2->getTitle());
    }

    #[Test]
    public function twoInstancesHaveIndependentStatuses(): void
    {
        $subject1 = new Story();
        $subject2 = new Story();
        $subject1->setStatus(Story::STATUS_PUBLISHED);
        self::assertSame(Story::STATUS_SUBMITTED, $subject2->getStatus());
    }

    #[Test]
    public function twoInstancesHaveIndependentMediaStorages(): void
    {
        $subject1 = new Story();
        $subject2 = new Story();
        self::assertNotSame($subject1->getMedia(), $subject2->getMedia());
    }
}
