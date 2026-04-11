<?php

declare(strict_types=1);

use Maispace\MaiBase\TableConfigurationArray\Helper;
use Maispace\MaiBase\TableConfigurationArray\Table;

$lang = Helper::localLangHelperFactory('mai_account', 'Default/locallang_tca.xlf');

return (new Table($lang('table.tx_maiaccount_reminder')))
    ->setDefaultConfig()
    ->setLabel('title')
    ->setAlternativeLabelFields('remind_at')
    ->setSearchFields('title, message')
    ->setIconFile('EXT:mai_account/Resources/Public/Icons/tx_maiaccount_reminder.svg')
    ->setDefaultSorting('ORDER BY remind_at ASC')
    ->addColumn(
        'title',
        $lang('tx_maiaccount_reminder.title'),
        ['type' => 'input', 'size' => 50, 'max' => 255, 'eval' => 'trim,required']
    )
    ->addColumn(
        'message',
        $lang('tx_maiaccount_reminder.message'),
        ['type' => 'text', 'rows' => 5, 'cols' => 50, 'eval' => 'trim']
    )
    ->addColumn(
        'remind_at',
        $lang('tx_maiaccount_reminder.remind_at'),
        ['type' => 'datetime', 'format' => 'datetime', 'eval' => 'required']
    )
    ->addColumn(
        'fe_user',
        $lang('tx_maiaccount_reminder.fe_user'),
        [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'foreign_table' => 'fe_users',
            'foreign_table_where' => 'ORDER BY fe_users.username',
            'minitems' => 1,
            'maxitems' => 1,
        ]
    )
    ->addColumn(
        'status',
        $lang('tx_maiaccount_reminder.status'),
        [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                ['label' => $lang('tx_maiaccount_reminder.status.pending'), 'value' => 'pending'],
                ['label' => $lang('tx_maiaccount_reminder.status.sent'), 'value' => 'sent'],
                ['label' => $lang('tx_maiaccount_reminder.status.dismissed'), 'value' => 'dismissed'],
            ],
            'default' => 'pending',
        ]
    )
    ->addTypeShowItem(
        '0',
        'title, message, remind_at, fe_user, status,
        --div--;' . $lang('tab.language') . ', --palette--;;language,
        --div--;' . $lang('tab.access') . ', --palette--;;hidden, --palette--;;access'
    )
    ->getConfig();
