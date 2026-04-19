<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Service;

use Maispace\MaiAccount\Domain\Model\Reminder;
use Maispace\MaiAccount\Domain\Repository\ReminderRepository;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

class ReminderService
{
    public function __construct(
        private readonly ReminderRepository $reminderRepository,
        private readonly PersistenceManagerInterface $persistenceManager,
        private readonly ConnectionPool $connectionPool,
    ) {
    }

    public function markAsSent(Reminder $reminder): void
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_maiaccount_reminder');
        $queryBuilder->update('tx_maiaccount_reminder')
            ->set('sent', 1)
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($reminder->getUid())))
            ->executeStatement();
    }

    public function findDueReminders(\DateTimeImmutable $now): array
    {
        return $this->reminderRepository->findDueReminders($now)->toArray();
    }
}
