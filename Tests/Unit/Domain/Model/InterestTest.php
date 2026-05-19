<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Tests\Unit\Domain\Model;

use Maispace\MaiAccount\Domain\Model\Interest;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class InterestTest extends TestCase
{
    // ── Default values ──────────────────────────────────────────────────────

    #[Test]
    public function defaultTitleIsEmptyString(): void
    {
        $subject = new Interest();
        self::assertSame('', $subject->getTitle());
    }

    #[Test]
    public function defaultIdentifierIsEmptyString(): void
    {
        $subject = new Interest();
        self::assertSame('', $subject->getIdentifier());
    }

    // ── title getter / setter ───────────────────────────────────────────────

    #[Test]
    public function setTitleStoresTheValue(): void
    {
        $subject = new Interest();
        $subject->setTitle('Sport');
        self::assertSame('Sport', $subject->getTitle());
    }

    #[Test]
    public function setTitleOverwritesPreviousValue(): void
    {
        $subject = new Interest();
        $subject->setTitle('Sport');
        $subject->setTitle('Musik');
        self::assertSame('Musik', $subject->getTitle());
    }

    // ── identifier getter / setter ──────────────────────────────────────────

    #[Test]
    public function setIdentifierStoresTheValue(): void
    {
        $subject = new Interest();
        $subject->setIdentifier('sport');
        self::assertSame('sport', $subject->getIdentifier());
    }

    #[Test]
    public function setIdentifierOverwritesPreviousValue(): void
    {
        $subject = new Interest();
        $subject->setIdentifier('sport');
        $subject->setIdentifier('musik');
        self::assertSame('musik', $subject->getIdentifier());
    }

    // ── instance isolation ──────────────────────────────────────────────────

    #[Test]
    public function twoInstancesHaveIndependentTitles(): void
    {
        $subject1 = new Interest();
        $subject2 = new Interest();
        $subject1->setTitle('Sport');
        self::assertSame('', $subject2->getTitle());
    }

    #[Test]
    public function twoInstancesHaveIndependentIdentifiers(): void
    {
        $subject1 = new Interest();
        $subject2 = new Interest();
        $subject1->setIdentifier('sport');
        self::assertSame('', $subject2->getIdentifier());
    }
}
