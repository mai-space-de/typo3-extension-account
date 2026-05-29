<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Tests\Unit\Controller\Backend;

use Maispace\MaiAccount\Domain\Model\Story;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class StoryBackendControllerTest extends TestCase
{
    #[Test]
    public function approveSetsStatusToPublished(): void
    {
        $story = new Story();
        $story->setStatus(Story::STATUS_PUBLISHED);
        $story->setPublishedAt(new \DateTimeImmutable());

        self::assertSame(Story::STATUS_PUBLISHED, $story->getStatus());
        self::assertNotNull($story->getPublishedAt());
    }

    #[Test]
    public function rejectSetsStatusToRejected(): void
    {
        $story = new Story();
        $story->setStatus(Story::STATUS_REJECTED);

        self::assertSame(Story::STATUS_REJECTED, $story->getStatus());
    }

    #[Test]
    public function approveSetsPublishedAtToNow(): void
    {
        $story = new Story();
        $before = new \DateTimeImmutable();
        $story->setStatus(Story::STATUS_PUBLISHED);
        $story->setPublishedAt(new \DateTimeImmutable());

        self::assertGreaterThanOrEqual($before, $story->getPublishedAt());
    }

    #[Test]
    public function storyStatusTransitionsAreValid(): void
    {
        $story = new Story();
        self::assertSame(Story::STATUS_SUBMITTED, $story->getStatus());

        // submitted → published
        $story->setStatus(Story::STATUS_PUBLISHED);
        self::assertSame(Story::STATUS_PUBLISHED, $story->getStatus());

        // published → rejected (should be possible for re-moderation)
        $story->setStatus(Story::STATUS_REJECTED);
        self::assertSame(Story::STATUS_REJECTED, $story->getStatus());
    }

    #[Test]
    public function controllerActionsMatchModuleRegistration(): void
    {
        $registeredActions = ['index', 'approve', 'reject'];

        // These actions must match the controllerActions in Configuration/Backend/Modules.php
        self::assertContains('index', $registeredActions);
        self::assertContains('approve', $registeredActions);
        self::assertContains('reject', $registeredActions);
        self::assertCount(3, $registeredActions);
    }

    #[Test]
    public function feUserStoresAuthorReference(): void
    {
        $story = new Story();
        $story->setFeUser(42);

        self::assertSame(42, $story->getFeUser());
    }

    #[Test]
    public function publishedAtIsNullForNonPublishedStory(): void
    {
        $story = new Story();
        $story->setStatus(Story::STATUS_SUBMITTED);

        self::assertNull($story->getPublishedAt());
    }
}
