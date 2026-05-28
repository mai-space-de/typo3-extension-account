<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Tests\Unit\Service;

use Doctrine\DBAL\Result;
use Maispace\MaiAccount\Service\PasswordResetLogService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Expression\ExpressionBuilder;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

final class PasswordResetLogServiceTest extends TestCase
{
    private ConnectionPool&MockObject $connectionPool;
    private PasswordResetLogService $subject;

    protected function setUp(): void
    {
        $this->connectionPool = $this->createMock(ConnectionPool::class);
        $this->subject = new PasswordResetLogService($this->connectionPool);
    }

    #[Test]
    public function logResetRequestInsertsRowAndReturnsLastInsertId(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->expects(self::once())->method('insert')->with(
            PasswordResetLogService::TABLE,
            self::callback(
                static fn(array $row): bool =>
                    $row['email'] === 'user@example.com'
                    && $row['ip_address'] === '192.168.1.1'
                    && $row['fe_user'] === 42
                    && $row['status'] === 'requested'
                    && is_int($row['crdate'])
                    && is_int($row['tstamp'])
                    && $row['pid'] === 0,
            ),
        );
        $connection->method('lastInsertId')->willReturn('17');

        $this->connectionPool->method('getConnectionForTable')->willReturn($connection);

        $uid = $this->subject->logResetRequest('user@example.com', '192.168.1.1', 42);

        self::assertSame(17, $uid);
    }

    #[Test]
    public function logResetCompletedUpdatesMostRecentRequestedEntry(): void
    {
        $row = ['uid' => 5];

        $selectResult = $this->createMock(Result::class);
        $selectResult->method('fetchAssociative')->willReturn($row);

        $selectExpr = $this->createMock(ExpressionBuilder::class);
        $selectExpr->method('eq')->willReturnArgument(0);

        $subQb = $this->createMock(QueryBuilder::class);
        $subQb->method('select')->willReturnSelf();
        $subQb->method('from')->willReturnSelf();
        $subQb->method('where')->willReturnSelf();
        $subQb->method('orderBy')->willReturnSelf();
        $subQb->method('setMaxResults')->willReturnSelf();
        $subQb->method('expr')->willReturn($selectExpr);
        $subQb->method('createNamedParameter')->willReturnCallback(self::createNamedParameterCallback());
        $subQb->method('executeQuery')->willReturn($selectResult);

        $updateExpr = $this->createMock(ExpressionBuilder::class);
        $updateExpr->method('eq')->willReturnArgument(0);

        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('update')->willReturnSelf();
        $qb->method('set')->willReturnSelf();
        $qb->method('where')->willReturnSelf();
        $qb->method('expr')->willReturn($updateExpr);
        $qb->method('createNamedParameter')->willReturnCallback(self::createNamedParameterCallback());
        $qb->expects(self::once())->method('executeStatement')->willReturn(1);

        $this->connectionPool
            ->method('getQueryBuilderForTable')
            ->willReturnOnConsecutiveCalls($qb, $subQb);

        $this->subject->logResetCompleted('user@example.com');
    }

    #[Test]
    public function logResetCompletedDoesNothingWhenNoMatchingEntryExists(): void
    {
        $selectResult = $this->createMock(Result::class);
        $selectResult->method('fetchAssociative')->willReturn(false);

        $expr = $this->createMock(ExpressionBuilder::class);
        $expr->method('eq')->willReturnArgument(0);

        $subQb = $this->createMock(QueryBuilder::class);
        $subQb->method('select')->willReturnSelf();
        $subQb->method('from')->willReturnSelf();
        $subQb->method('where')->willReturnSelf();
        $subQb->method('orderBy')->willReturnSelf();
        $subQb->method('setMaxResults')->willReturnSelf();
        $subQb->method('expr')->willReturn($expr);
        $subQb->method('createNamedParameter')->willReturnCallback(self::createNamedParameterCallback());
        $subQb->method('executeQuery')->willReturn($selectResult);

        $qb = $this->createMock(QueryBuilder::class);
        $qb->expects(self::never())->method('executeStatement');

        $this->connectionPool
            ->method('getQueryBuilderForTable')
            ->willReturnOnConsecutiveCalls($qb, $subQb);

        $this->subject->logResetCompleted('nonexistent@example.com');
    }

    #[Test]
    public function findFailedSequencesReturnsEntriesPastCutoff(): void
    {
        $now = time();
        $expected = [
            [
                'uid' => 1,
                'email' => 'old@example.com',
                'ip_address' => '10.0.0.1',
                'fe_user' => 7,
                'crdate' => $now - 100000,
            ],
            [
                'uid' => 2,
                'email' => 'older@example.com',
                'ip_address' => '10.0.0.2',
                'fe_user' => 8,
                'crdate' => $now - 200000,
            ],
        ];

        $result = $this->createMock(Result::class);
        $result->method('fetchAllAssociative')->willReturn($expected);

        $expr = $this->createMock(ExpressionBuilder::class);
        $expr->method('eq')->willReturnArgument(0);
        $expr->method('lt')->willReturnArgument(0);

        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('select')->willReturnSelf();
        $qb->method('from')->willReturnSelf();
        $qb->method('where')->willReturnSelf();
        $qb->method('orderBy')->willReturnSelf();
        $qb->method('expr')->willReturn($expr);
        $qb->method('createNamedParameter')->willReturnCallback(self::createNamedParameterCallback());
        $qb->method('executeQuery')->willReturn($result);

        $this->connectionPool->method('getQueryBuilderForTable')->willReturn($qb);

        $failed = $this->subject->findFailedSequences();

        self::assertCount(2, $failed);
        self::assertSame(1, $failed[0]['uid']);
        self::assertSame('old@example.com', $failed[0]['email']);
    }

    #[Test]
    public function findFailedSequencesReturnsEmptyWhenNoneExist(): void
    {
        $result = $this->createMock(Result::class);
        $result->method('fetchAllAssociative')->willReturn([]);

        $expr = $this->createMock(ExpressionBuilder::class);
        $expr->method('eq')->willReturnArgument(0);
        $expr->method('lt')->willReturnArgument(0);

        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('select')->willReturnSelf();
        $qb->method('from')->willReturnSelf();
        $qb->method('where')->willReturnSelf();
        $qb->method('orderBy')->willReturnSelf();
        $qb->method('expr')->willReturn($expr);
        $qb->method('createNamedParameter')->willReturnCallback(self::createNamedParameterCallback());
        $qb->method('executeQuery')->willReturn($result);

        $this->connectionPool->method('getQueryBuilderForTable')->willReturn($qb);

        $failed = $this->subject->findFailedSequences();

        self::assertCount(0, $failed);
    }

    #[Test]
    public function findFailedSequencesRespectsCustomWindow(): void
    {
        $result = $this->createMock(Result::class);
        $result->method('fetchAllAssociative')->willReturn([]);

        $expr = $this->createMock(ExpressionBuilder::class);
        $expr->method('eq')->willReturnArgument(0);
        $expr->method('lt')->willReturnArgument(0);

        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('select')->willReturnSelf();
        $qb->method('from')->willReturnSelf();
        $qb->method('where')->willReturnSelf();
        $qb->method('orderBy')->willReturnSelf();
        $qb->method('expr')->willReturn($expr);
        $qb->method('createNamedParameter')->willReturnCallback(self::createNamedParameterCallback());
        $qb->method('executeQuery')->willReturn($result);

        $this->connectionPool->method('getQueryBuilderForTable')->willReturn($qb);

        $failed = $this->subject->findFailedSequences(3600);

        self::assertCount(0, $failed);
    }

    #[Test]
    public function findRecentAttemptsByEmailReturnsEntries(): void
    {
        $expected = [
            ['uid' => 3, 'status' => 'requested', 'ip_address' => '10.0.0.3', 'crdate' => time() - 100],
            ['uid' => 2, 'status' => 'completed', 'ip_address' => '10.0.0.3', 'crdate' => time() - 200],
        ];

        $result = $this->createMock(Result::class);
        $result->method('fetchAllAssociative')->willReturn($expected);

        $expr = $this->createMock(ExpressionBuilder::class);
        $expr->method('eq')->willReturnArgument(0);

        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('select')->willReturnSelf();
        $qb->method('from')->willReturnSelf();
        $qb->method('where')->willReturnSelf();
        $qb->method('orderBy')->willReturnSelf();
        $qb->method('setMaxResults')->willReturnSelf();
        $qb->method('expr')->willReturn($expr);
        $qb->method('createNamedParameter')->willReturnCallback(self::createNamedParameterCallback());
        $qb->method('executeQuery')->willReturn($result);

        $this->connectionPool->method('getQueryBuilderForTable')->willReturn($qb);

        $recent = $this->subject->findRecentAttemptsByEmail('user@example.com');

        self::assertCount(2, $recent);
        self::assertSame('requested', $recent[0]['status']);
    }

    #[Test]
    public function findRecentAttemptsByEmailRespectsCustomLimit(): void
    {
        $expected = [
            ['uid' => 1, 'status' => 'completed', 'ip_address' => '10.0.0.1', 'crdate' => time()],
        ];

        $result = $this->createMock(Result::class);
        $result->method('fetchAllAssociative')->willReturn($expected);

        $expr = $this->createMock(ExpressionBuilder::class);
        $expr->method('eq')->willReturnArgument(0);

        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('select')->willReturnSelf();
        $qb->method('from')->willReturnSelf();
        $qb->method('where')->willReturnSelf();
        $qb->method('orderBy')->willReturnSelf();
        $qb->method('setMaxResults')->willReturnSelf();
        $qb->method('expr')->willReturn($expr);
        $qb->method('createNamedParameter')->willReturnCallback(self::createNamedParameterCallback());
        $qb->method('executeQuery')->willReturn($result);

        $this->connectionPool->method('getQueryBuilderForTable')->willReturn($qb);

        $recent = $this->subject->findRecentAttemptsByEmail('user@example.com', 1);

        self::assertCount(1, $recent);
        self::assertSame('completed', $recent[0]['status']);
    }

    #[Test]
    public function defaultFailedWindowIs86400Seconds(): void
    {
        self::assertSame(86400, PasswordResetLogService::DEFAULT_FAILED_WINDOW_SECONDS);
    }

    private static function createNamedParameterCallback(): callable
    {
        return static fn(mixed $v, mixed $_type = null): string => (string) $v;
    }
}
