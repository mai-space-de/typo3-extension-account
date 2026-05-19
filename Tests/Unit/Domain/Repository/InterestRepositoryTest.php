<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Tests\Unit\Domain\Repository;

use Maispace\MaiAccount\Domain\Repository\InterestRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Extbase\Persistence\Repository;

final class InterestRepositoryTest extends TestCase
{
    #[Test]
    public function interestRepositoryExtendsExtbaseRepository(): void
    {
        self::assertInstanceOf(Repository::class, $this->createPartialMock(InterestRepository::class, []));
    }

    #[Test]
    public function interestRepositoryHasNoStaticDefaultOrderings(): void
    {
        $repository = $this->createPartialMock(InterestRepository::class, []);

        $reflection = new \ReflectionProperty(InterestRepository::class, 'defaultOrderings');
        $reflection->setAccessible(true);
        $orderings = $reflection->getValue($repository);

        self::assertSame([], $orderings);
    }
}
