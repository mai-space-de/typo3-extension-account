<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Tests\Unit\Domain\Repository;

use Maispace\MaiAccount\Domain\Repository\FrontendUserRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Extbase\Persistence\Repository;

final class FrontendUserRepositoryTest extends TestCase
{
    #[Test]
    public function frontendUserRepositoryExtendsExtbaseRepository(): void
    {
        self::assertInstanceOf(Repository::class, $this->createPartialMock(FrontendUserRepository::class, []));
    }

    #[Test]
    public function frontendUserRepositoryHasNoStaticDefaultOrderings(): void
    {
        $repository = $this->createPartialMock(FrontendUserRepository::class, []);

        $reflection = new \ReflectionProperty(FrontendUserRepository::class, 'defaultOrderings');
        $reflection->setAccessible(true);
        $orderings = $reflection->getValue($repository);

        self::assertSame([], $orderings);
    }
}
