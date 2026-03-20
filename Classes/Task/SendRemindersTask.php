<?php

declare(strict_types=1);

namespace Maispace\Account\Task;

use Maispace\Account\Service\ReminderService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

class SendRemindersTask extends AbstractTask
{
    public function execute(): bool
    {
        /** @var ReminderService $reminderService */
        $reminderService = GeneralUtility::makeInstance(ReminderService::class);
        $sent = $reminderService->sendPendingReminders();

        $this->logger?->info(
            sprintf('maispace Account: SendRemindersTask sent %d reminder(s).', $sent)
        );

        return true;
    }
}
