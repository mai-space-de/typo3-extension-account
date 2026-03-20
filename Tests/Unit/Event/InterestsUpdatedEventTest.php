<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Tests\Unit\Event;

use Maispace\MaiAccount\Event\InterestsUpdatedEvent;
use PHPUnit\Framework\TestCase;

class InterestsUpdatedEventTest extends TestCase
{
    public function testGettersReturnConstructorValues(): void
    {
        $event = new InterestsUpdatedEvent(
            feUserUid: 7,
            feUserEmail: 'user@example.com',
            newInterests: ['sports', 'culture'],
            previousInterests: ['culture', 'nature'],
        );

        self::assertSame(7, $event->getFeUserUid());
        self::assertSame('user@example.com', $event->getFeUserEmail());
        self::assertSame(['sports', 'culture'], $event->getNewInterests());
        self::assertSame(['culture', 'nature'], $event->getPreviousInterests());
    }

    public function testGetAddedInterests(): void
    {
        $event = new InterestsUpdatedEvent(
            feUserUid: 1,
            feUserEmail: 'a@b.com',
            newInterests: ['sports', 'culture', 'technology'],
            previousInterests: ['culture', 'nature'],
        );

        self::assertSame(['sports', 'technology'], $event->getAddedInterests());
    }

    public function testGetRemovedInterests(): void
    {
        $event = new InterestsUpdatedEvent(
            feUserUid: 1,
            feUserEmail: 'a@b.com',
            newInterests: ['sports', 'culture'],
            previousInterests: ['culture', 'nature', 'education'],
        );

        self::assertSame(['nature', 'education'], $event->getRemovedInterests());
    }

    public function testNoChangesResultsInEmptyDiff(): void
    {
        $interests = ['sports', 'culture'];
        $event = new InterestsUpdatedEvent(
            feUserUid: 1,
            feUserEmail: 'a@b.com',
            newInterests: $interests,
            previousInterests: $interests,
        );

        self::assertSame([], $event->getAddedInterests());
        self::assertSame([], $event->getRemovedInterests());
    }
}
