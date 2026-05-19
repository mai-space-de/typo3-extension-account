<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Task;

use Maispace\MaiAccount\Domain\Model\Reminder;
use Maispace\MaiAccount\Service\AccountMailer;
use Maispace\MaiAccount\Service\ReminderService;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

class ReminderTask extends AbstractTask
{
    public function execute(): bool
    {
        $reminderService = GeneralUtility::makeInstance(ReminderService::class);
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $accountMailer = GeneralUtility::makeInstance(AccountMailer::class);

        $now = new \DateTimeImmutable();
        $dueReminders = $reminderService->findDueReminders($now);

        foreach ($dueReminders as $reminder) {
            $this->sendReminderNotification($reminder, $connectionPool, $accountMailer);
            $reminderService->markAsSent($reminder);
        }

        return true;
    }

    private function sendReminderNotification(
        Reminder $reminder,
        ConnectionPool $connectionPool,
        AccountMailer $accountMailer,
    ): void {
        $queryBuilder = $connectionPool->getQueryBuilderForTable('fe_users');
        $row = $queryBuilder
            ->select('email', 'first_name')
            ->from('fe_users')
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($reminder->getFeUser())))
            ->executeQuery()
            ->fetchAssociative();

        if ($row === false || empty($row['email'])) {
            return;
        }

        $remindAt = $reminder->getRemindAt() ?? new \DateTimeImmutable();

        $accountMailer->sendReminderNotification(
            (string) $row['email'],
            (string) ($row['first_name'] ?? ''),
            $reminder->getTitle(),
            $remindAt,
        );
    }
}
