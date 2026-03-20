<?php

declare(strict_types=1);

namespace Maispace\Account\Task;

use Maispace\Account\Service\ReminderService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

class SendRemindersTask extends AbstractTask
{
    public string $senderAddress = '';
    public string $senderName = '';

    public function execute(): bool
    {
        $reminderService = GeneralUtility::makeInstance(ReminderService::class);
        $reminderService->sendPendingReminders($this->senderAddress, $this->senderName);
        return true;
    }
}
