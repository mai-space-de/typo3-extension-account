<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Tests\Unit\Domain\Repository;

use Maispace\MaiAccount\Domain\Repository\StoryRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

final class StoryRepositoryTest extends TestCase
{
    #[Test]
    public function storyRepositoryExtendsExtbaseRepository(): void
    {
        self::assertInstanceOf(Repository::class, $this->createPartialMock(StoryRepository::class, []));
    }

    #[Test]
    public function defaultOrderingsContainsSubmittedAtDescending(): void
    {
        $repository = $this->createPartialMock(StoryRepository::class, []);

        $reflection = new \ReflectionProperty(StoryRepository::class, 'defaultOrderings');
        $reflection->setAccessible(true);
        $orderings = $reflection->getValue($repository);

        self::assertArrayHasKey('submittedAt', $orderings);
        self::assertSame(QueryInterface::ORDER_DESCENDING, $orderings['submittedAt']);
    }

    #[Test]
    public function defaultOrderingsHasExactlyOneSortKey(): void
    {
        $repository = $this->createPartialMock(StoryRepository::class, []);

        $reflection = new \ReflectionProperty(StoryRepository::class, 'defaultOrderings');
        $reflection->setAccessible(true);
        $orderings = $reflection->getValue($repository);

        self::assertCount(1, $orderings);
    }
}
