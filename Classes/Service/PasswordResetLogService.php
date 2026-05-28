<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Service;

use Doctrine\DBAL\ParameterType;
use TYPO3\CMS\Core\Database\ConnectionPool;

/**
 * Security audit log for frontend password-reset sequences.
 *
 * Every felogin-triggered password-reset request is recorded. When the
 * reset is completed the corresponding log entry is marked as complete.
 * Entries that stay incomplete past a configurable window are flagged as
 * failed, enabling security monitoring and incident response.
 */
class PasswordResetLogService
{
    public const TABLE = 'tx_maiaccount_password_reset_log';

    /** Reset entries older than this many seconds without a completed marker are considered failed. */
    public const DEFAULT_FAILED_WINDOW_SECONDS = 86400;

    public function __construct(
        private readonly ConnectionPool $connectionPool,
    ) {}

    /**
     * Record a password-reset request (felogin recovery email was dispatched).
     *
     * @return int The UID of the inserted log row.
     */
    public function logResetRequest(string $email, string $ipAddress, int $feUserUid): int
    {
        $now = time();

        $connection = $this->connectionPool->getConnectionForTable(self::TABLE);
        $connection->insert(self::TABLE, [
            'pid' => 0,
            'crdate' => $now,
            'tstamp' => $now,
            'email' => $email,
            'ip_address' => $ipAddress,
            'fe_user' => $feUserUid,
            'status' => 'requested',
        ]);

        return (int) $connection->lastInsertId();
    }

    /**
     * Mark the most recent incomplete reset-request for the given email as completed.
     */
    public function logResetCompleted(string $email): void
    {
        $qb = $this->connectionPool->getQueryBuilderForTable(self::TABLE);

        $sub = $this->connectionPool->getQueryBuilderForTable(self::TABLE);
        $sub->select('uid')
            ->from(self::TABLE)
            ->where(
                $sub->expr()->eq('email', $sub->createNamedParameter($email)),
                $sub->expr()->eq('status', $sub->createNamedParameter('requested')),
            )
            ->orderBy('crdate', 'DESC')
            ->setMaxResults(1);

        $row = $sub->executeQuery()->fetchAssociative();
        if ($row === false) {
            return;
        }

        $qb->update(self::TABLE)
            ->set('status', 'completed')
            ->set('tstamp', time())
            ->where($qb->expr()->eq('uid', $qb->createNamedParameter((int) $row['uid'], ParameterType::INTEGER)))
            ->executeStatement();
    }

    /**
     * Find reset sequences that were requested but never completed within
     * the given window (default: 24 h). These are considered "failed".
     *
     * @return list<array{uid: int, email: string, ip_address: string, fe_user: int, crdate: int}>
     */
    public function findFailedSequences(int $maximumAgeSeconds = self::DEFAULT_FAILED_WINDOW_SECONDS): array
    {
        $cutoff = time() - $maximumAgeSeconds;

        $qb = $this->connectionPool->getQueryBuilderForTable(self::TABLE);
        $result = $qb->select('uid', 'email', 'ip_address', 'fe_user', 'crdate')
            ->from(self::TABLE)
            ->where(
                $qb->expr()->eq('status', $qb->createNamedParameter('requested')),
                $qb->expr()->lt('crdate', $qb->createNamedParameter($cutoff, ParameterType::INTEGER)),
            )
            ->orderBy('crdate', 'DESC')
            ->executeQuery();

        /** @var list<array{uid: int, email: string, ip_address: string, fe_user: int, crdate: int}> */
        return $result->fetchAllAssociative();
    }

    /**
     * Return the most recent reset attempts for a given email address.
     *
     * @return list<array{uid: int, status: string, ip_address: string, crdate: int}>
     */
    public function findRecentAttemptsByEmail(string $email, int $limit = 10): array
    {
        $qb = $this->connectionPool->getQueryBuilderForTable(self::TABLE);
        $result = $qb->select('uid', 'status', 'ip_address', 'crdate')
            ->from(self::TABLE)
            ->where(
                $qb->expr()->eq('email', $qb->createNamedParameter($email)),
            )
            ->orderBy('crdate', 'DESC')
            ->setMaxResults($limit)
            ->executeQuery();

        /** @var list<array{uid: int, status: string, ip_address: string, crdate: int}> */
        return $result->fetchAllAssociative();
    }
}
