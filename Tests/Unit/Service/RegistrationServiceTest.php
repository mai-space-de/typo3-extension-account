<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Tests\Unit\Service;

use Doctrine\DBAL\Result;
use Maispace\MaiAccount\Service\RegistrationService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashInterface;
use TYPO3\CMS\Core\Crypto\Random;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Expression\ExpressionBuilder;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\QueryRestrictionContainerInterface;

/**
 * Unit tests for RegistrationService.
 *
 * ConnectionPool and its QueryBuilder chain are mocked end-to-end so the
 * tests run without a database. The PasswordHashFactory and Random services
 * are likewise mocked; only the business-logic paths are exercised here.
 */
final class RegistrationServiceTest extends TestCase
{
    private ConnectionPool&MockObject $connectionPool;
    private PasswordHashFactory&MockObject $passwordHashFactory;
    private Random&MockObject $random;
    private RegistrationService $subject;

    protected function setUp(): void
    {
        $this->connectionPool = $this->createMock(ConnectionPool::class);
        $this->passwordHashFactory = $this->createMock(PasswordHashFactory::class);
        $this->random = $this->createMock(Random::class);

        $this->subject = new RegistrationService(
            $this->passwordHashFactory,
            $this->connectionPool,
            $this->random,
        );
    }

    // ── isUsernameAvailable ────────────────────────────────────────────────────

    #[Test]
    public function isUsernameAvailableReturnsTrueWhenNoMatchingUserExists(): void
    {
        $this->connectionPool
            ->method('getQueryBuilderForTable')
            ->willReturn($this->buildCountQueryBuilder(0));

        self::assertTrue($this->subject->isUsernameAvailable('newuser'));
    }

    #[Test]
    public function isUsernameAvailableReturnsFalseWhenUsernameIsTaken(): void
    {
        $this->connectionPool
            ->method('getQueryBuilderForTable')
            ->willReturn($this->buildCountQueryBuilder(1));

        self::assertFalse($this->subject->isUsernameAvailable('existinguser'));
    }

    // ── isEmailAvailable ───────────────────────────────────────────────────────

    #[Test]
    public function isEmailAvailableReturnsTrueWhenNoMatchingUserExists(): void
    {
        $this->connectionPool
            ->method('getQueryBuilderForTable')
            ->willReturn($this->buildCountQueryBuilder(0));

        self::assertTrue($this->subject->isEmailAvailable('new@example.com'));
    }

    #[Test]
    public function isEmailAvailableReturnsFalseWhenEmailIsTaken(): void
    {
        $this->connectionPool
            ->method('getQueryBuilderForTable')
            ->willReturn($this->buildCountQueryBuilder(1));

        self::assertFalse($this->subject->isEmailAvailable('existing@example.com'));
    }

    // ── confirm — input guard ──────────────────────────────────────────────────

    #[Test]
    public function confirmReturnsFalseForEmptyToken(): void
    {
        // An empty token short-circuits before any database access.
        $this->connectionPool->expects(self::never())->method('getQueryBuilderForTable');

        self::assertFalse($this->subject->confirm(''));
    }

    // ── confirm — user lookup ──────────────────────────────────────────────────

    #[Test]
    public function confirmReturnsFalseWhenTokenIsNotFound(): void
    {
        $this->connectionPool
            ->method('getQueryBuilderForTable')
            ->willReturn($this->buildSelectQueryBuilder(false));

        self::assertFalse($this->subject->confirm('unknown-token'));
    }

    // ── confirm — expiry guard ─────────────────────────────────────────────────

    #[Test]
    public function confirmReturnsFalseWhenTokenHasExpired(): void
    {
        $row = ['uid' => 5, 'tx_maiaccount_confirm_expires' => time() - 3600];

        $this->connectionPool
            ->method('getQueryBuilderForTable')
            ->willReturn($this->buildSelectQueryBuilder($row));

        self::assertFalse($this->subject->confirm('expired-token'));
    }

    // ── confirm — success paths ────────────────────────────────────────────────

    #[Test]
    public function confirmReturnsTrueWhenExpiresAtIsZero(): void
    {
        // expires = 0 means "no expiry enforced" — the condition is `0 > 0 && …` which
        // is always false, so the check is skipped and confirmation succeeds.
        $row = ['uid' => 7, 'tx_maiaccount_confirm_expires' => 0];
        [$selectQb, $updateQb] = $this->buildSelectAndUpdateQueryBuilders($row);

        $this->connectionPool
            ->method('getQueryBuilderForTable')
            ->willReturnOnConsecutiveCalls($selectQb, $updateQb);

        self::assertTrue($this->subject->confirm('valid-token'));
    }

    #[Test]
    public function confirmReturnsTrueWhenExpiresAtIsInTheFuture(): void
    {
        $row = ['uid' => 9, 'tx_maiaccount_confirm_expires' => time() + 86400];
        [$selectQb, $updateQb] = $this->buildSelectAndUpdateQueryBuilders($row);

        $this->connectionPool
            ->method('getQueryBuilderForTable')
            ->willReturnOnConsecutiveCalls($selectQb, $updateQb);

        self::assertTrue($this->subject->confirm('valid-token'));
    }

    #[Test]
    public function confirmExecutesUpdateQueryOnSuccess(): void
    {
        $row = ['uid' => 11, 'tx_maiaccount_confirm_expires' => 0];
        [$selectQb, $updateQb] = $this->buildSelectAndUpdateQueryBuilders($row);

        $updateQb->expects(self::once())->method('executeStatement');

        $this->connectionPool
            ->method('getQueryBuilderForTable')
            ->willReturnOnConsecutiveCalls($selectQb, $updateQb);

        $this->subject->confirm('valid-token');
    }

    // ── register ───────────────────────────────────────────────────────────────

    #[Test]
    public function registerReturnsArrayWithUidTokenAndExpiresAt(): void
    {
        $this->random
            ->method('generateRandomHexString')
            ->willReturn('aabbccddeeff00112233445566778899');

        $hashInstance = $this->createMock(PasswordHashInterface::class);
        $hashInstance->method('getHashedPassword')->willReturn('$2y$10$hashed');
        $this->passwordHashFactory
            ->method('getDefaultHashInstance')
            ->willReturn($hashInstance);

        $insertQb = $this->buildInsertQueryBuilder();
        $this->connectionPool
            ->method('getQueryBuilderForTable')
            ->willReturn($insertQb);

        $connection = $this->createMock(Connection::class);
        $connection->method('lastInsertId')->willReturn('42');
        $this->connectionPool
            ->method('getConnectionForTable')
            ->willReturn($connection);

        $result = $this->subject->register(
            'john',
            'john@example.com',
            'secret123',
            'John',
            'Doe',
            5,
        );

        self::assertArrayHasKey('uid', $result);
        self::assertArrayHasKey('token', $result);
        self::assertArrayHasKey('expiresAt', $result);
    }

    #[Test]
    public function registerReturnsUidFromLastInsertId(): void
    {
        $this->random->method('generateRandomHexString')->willReturn('deadbeef12345678');

        $hashInstance = $this->createMock(PasswordHashInterface::class);
        $hashInstance->method('getHashedPassword')->willReturn('$2y$10$hash');
        $this->passwordHashFactory->method('getDefaultHashInstance')->willReturn($hashInstance);

        $this->connectionPool->method('getQueryBuilderForTable')->willReturn($this->buildInsertQueryBuilder());

        $connection = $this->createMock(Connection::class);
        $connection->method('lastInsertId')->willReturn('99');
        $this->connectionPool->method('getConnectionForTable')->willReturn($connection);

        $result = $this->subject->register('jane', 'jane@example.com', 'pass', 'Jane', 'Doe', 1);

        self::assertSame(99, $result['uid']);
    }

    #[Test]
    public function registerReturnsTokenFromRandomService(): void
    {
        $expectedToken = 'cafebabe12345678cafebabe12345678';
        $this->random->method('generateRandomHexString')->willReturn($expectedToken);

        $hashInstance = $this->createMock(PasswordHashInterface::class);
        $hashInstance->method('getHashedPassword')->willReturn('$2y$10$hash');
        $this->passwordHashFactory->method('getDefaultHashInstance')->willReturn($hashInstance);

        $this->connectionPool->method('getQueryBuilderForTable')->willReturn($this->buildInsertQueryBuilder());

        $connection = $this->createMock(Connection::class);
        $connection->method('lastInsertId')->willReturn('1');
        $this->connectionPool->method('getConnectionForTable')->willReturn($connection);

        $result = $this->subject->register('user', 'user@example.com', 'pw', 'First', 'Last', 1);

        self::assertSame($expectedToken, $result['token']);
    }

    #[Test]
    public function registerSetsExpiresAtToOneDayFromNow(): void
    {
        $this->random->method('generateRandomHexString')->willReturn('token123456789abc');

        $hashInstance = $this->createMock(PasswordHashInterface::class);
        $hashInstance->method('getHashedPassword')->willReturn('$2y$10$hash');
        $this->passwordHashFactory->method('getDefaultHashInstance')->willReturn($hashInstance);

        $this->connectionPool->method('getQueryBuilderForTable')->willReturn($this->buildInsertQueryBuilder());

        $connection = $this->createMock(Connection::class);
        $connection->method('lastInsertId')->willReturn('1');
        $this->connectionPool->method('getConnectionForTable')->willReturn($connection);

        $before = time();
        $result = $this->subject->register('u', 'u@example.com', 'p', 'F', 'L', 1);
        $after = time();

        // TTL is 86 400 s (24 h). Allow ±2 s for clock drift in the test.
        self::assertGreaterThanOrEqual($before + 86400 - 2, $result['expiresAt']);
        self::assertLessThanOrEqual($after + 86400 + 2, $result['expiresAt']);
    }

    // ── helpers ────────────────────────────────────────────────────────────────

    private function buildCountQueryBuilder(int $count): QueryBuilder
    {
        $fetchResult = $this->createMock(Result::class);
        $fetchResult->method('fetchOne')->willReturn($count);

        $expr = $this->createMock(ExpressionBuilder::class);
        $expr->method('eq')->willReturnArgument(0);

        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('count')->willReturnSelf();
        $qb->method('from')->willReturnSelf();
        $qb->method('where')->willReturnSelf();
        $qb->method('expr')->willReturn($expr);
        $qb->method('createNamedParameter')->willReturnCallback(static fn(mixed $v): string => (string) $v);
        $qb->method('executeQuery')->willReturn($fetchResult);

        return $qb;
    }

    /** @param array<string, mixed>|false $row */
    private function buildSelectQueryBuilder(array|false $row): QueryBuilder
    {
        $fetchResult = $this->createMock(Result::class);
        $fetchResult->method('fetchAssociative')->willReturn($row);

        $restrictions = $this->createMock(QueryRestrictionContainerInterface::class);

        $expr = $this->createMock(ExpressionBuilder::class);
        $expr->method('eq')->willReturnArgument(0);

        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('getRestrictions')->willReturn($restrictions);
        $qb->method('select')->willReturnSelf();
        $qb->method('from')->willReturnSelf();
        $qb->method('where')->willReturnSelf();
        $qb->method('expr')->willReturn($expr);
        $qb->method('createNamedParameter')->willReturnCallback(static fn(mixed $v): string => (string) $v);
        $qb->method('executeQuery')->willReturn($fetchResult);

        return $qb;
    }

    /**
     * Returns a [selectQb, updateQb] pair to cover both DB calls made by confirm()
     * on the success path (one SELECT, one UPDATE).
     *
     * @param array<string, mixed> $row
     * @return array{0: QueryBuilder, 1: QueryBuilder}
     */
    private function buildSelectAndUpdateQueryBuilders(array $row): array
    {
        $selectQb = $this->buildSelectQueryBuilder($row);

        $expr = $this->createMock(ExpressionBuilder::class);
        $expr->method('eq')->willReturnArgument(0);

        $updateQb = $this->createMock(QueryBuilder::class);
        $updateQb->method('update')->willReturnSelf();
        $updateQb->method('set')->willReturnSelf();
        $updateQb->method('where')->willReturnSelf();
        $updateQb->method('expr')->willReturn($expr);
        $updateQb->method('createNamedParameter')->willReturnCallback(static fn(mixed $v): string => (string) $v);
        $updateQb->method('executeStatement')->willReturn(1);

        return [$selectQb, $updateQb];
    }

    private function buildInsertQueryBuilder(): QueryBuilder
    {
        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('insert')->willReturnSelf();
        $qb->method('values')->willReturnSelf();
        $qb->method('executeStatement')->willReturn(1);

        return $qb;
    }
}
