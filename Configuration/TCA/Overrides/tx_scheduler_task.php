<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Schema\Struct\SelectItem;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

ExtensionManagementUtility::addRecordType(
    new SelectItem(
        type: 'select',
        label: 'LLL:EXT:mai_account/Resources/Private/Language/locallang.xlf:task.reminder.title',
        value: \Maispace\MaiAccount\Task\ReminderTask::class,
        icon: 'EXT:mai_account/Resources/Public/Icons/Extension.svg',
    ),
    '
        --div--;core.form.tabs:general,
            tasktype,
            task_group,
            description,
            parameters,
        --div--;core.form.tabs:timing,
            --palette--;;execution,
        --div--;core.form.tabs:access,
            disable,
        --div--;core.form.tabs:extended,
    ',
    [],
    '',
    'tx_scheduler_task'
);
