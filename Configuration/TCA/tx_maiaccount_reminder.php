<?php

declare(strict_types=1);

use Maispace\MaiBase\TableConfigurationArray\FieldConfig\DatetimeConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\InputConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\SelectSingleConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\TextConfig;
use Maispace\MaiBase\TableConfigurationArray\Helper;
use Maispace\MaiBase\TableConfigurationArray\Table;

$lang = Helper::localLangHelperFactory('mai_account', 'Default/locallang_tca.xlf');

return (new Table($lang('table.tx_maiaccount_reminder')))
    ->setDefaultConfig()
    ->setLabel('title')
    ->setAlternativeLabelFields('remind_at')
    ->setIconFile('EXT:mai_account/Resources/Public/Icons/tx_maiaccount_reminder.svg')
    ->setDefaultSorting('ORDER BY remind_at ASC')
    ->addColumn(
        'title',
        $lang('tx_maiaccount_reminder.title'),
        (new InputConfig())->setSize(50)->setMax(255)->setEval('trim')->setRequired()
    )
    ->addColumn(
        'message',
        $lang('tx_maiaccount_reminder.message'),
        (new TextConfig())->setRows(5)->setCols(50)->setEval('trim')
    )
    ->addColumn(
        'remind_at',
        $lang('tx_maiaccount_reminder.remind_at'),
        (new DatetimeConfig())->setFormat('datetime')->setRequired()
    )
    ->addColumn(
        'fe_user',
        $lang('tx_maiaccount_reminder.fe_user'),
        (new SelectSingleConfig())
            ->setForeignTable('fe_users')
            ->setForeignTableWhere('ORDER BY fe_users.username')
            ->setMinItems(1)
            ->setMaxItems(1)
    )
    ->addColumn(
        'status',
        $lang('tx_maiaccount_reminder.status'),
        (new SelectSingleConfig())
            ->setItems([
                ['label' => $lang('tx_maiaccount_reminder.status.pending'), 'value' => 'pending'],
                ['label' => $lang('tx_maiaccount_reminder.status.sent'), 'value' => 'sent'],
                ['label' => $lang('tx_maiaccount_reminder.status.dismissed'), 'value' => 'dismissed'],
            ])
            ->setDefault('pending')
    )
    ->addTypeShowItem(
        '0',
        'title, message, remind_at, fe_user, status,
        --div--;' . $lang('tab.language') . ', --palette--;;language,
        --div--;' . $lang('tab.access') . ', --palette--;;hidden, --palette--;;access'
    )
    ->getConfig();
