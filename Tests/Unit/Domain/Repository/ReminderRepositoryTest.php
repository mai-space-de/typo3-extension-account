<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Tests\Unit\Domain\Repository;

use Maispace\MaiAccount\Domain\Repository\ReminderRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

final class ReminderRepositoryTest extends TestCase
{
    #[Test]
    public function reminderRepositoryExtendsExtbaseRepository(): void
    {
        self::assertInstanceOf(Repository::class, $this->createPartialMock(ReminderRepository::class, []));
    }

    #[Test]
    public function defaultOrderingsContainsRemindAtAscending(): void
    {
        $repository = $this->createPartialMock(ReminderRepository::class, []);

        $reflection = new \ReflectionProperty(ReminderRepository::class, 'defaultOrderings');
        $reflection->setAccessible(true);
        $orderings = $reflection->getValue($repository);

        self::assertArrayHasKey('remindAt', $orderings);
        self::assertSame(QueryInterface::ORDER_ASCENDING, $orderings['remindAt']);
    }

    #[Test]
    public function defaultOrderingsHasExactlyOneSortKey(): void
    {
        $repository = $this->createPartialMock(ReminderRepository::class, []);

        $reflection = new \ReflectionProperty(ReminderRepository::class, 'defaultOrderings');
        $reflection->setAccessible(true);
        $orderings = $reflection->getValue($repository);

        self::assertCount(1, $orderings);
    }
}
