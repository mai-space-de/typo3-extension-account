<?php

declare(strict_types=1);

use TYPO3\CMS\Scheduler\Task\TableGarbageCollectionTask;

defined('TYPO3') or die();

$GLOBALS['TCA']['tx_scheduler_task']['columns']['task_type']['config']['items'][] = [
    'label' => 'LLL:EXT:mai_account/Resources/Private/Language/locallang.xlf:task.reminder.title',
    'value' => \Maispace\MaiAccount\Task\ReminderTask::class,
    'icon' => 'EXT:mai_account/Resources/Public/Icons/Extension.svg',
    'group' => 'mai_account',
];

$GLOBALS['TCA']['tx_scheduler_task']['types'][\Maispace\MaiAccount\Task\ReminderTask::class] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            task_type,
            description,
        --div--;LLL:EXT:scheduler/Resources/Private/Language/locallang.xlf:tab.execution,
            execution,
            execution_period,
        --div--;LLL:EXT:scheduler/Resources/Private/Language/locallang.xlf:tab.email,
            email_on_completion,
            email_on_failure,
    ',
];
