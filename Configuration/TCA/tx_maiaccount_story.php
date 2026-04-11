<?php

declare(strict_types=1);

use Maispace\MaiBase\TableConfigurationArray\Helper;
use Maispace\MaiBase\TableConfigurationArray\Table;

$lang = Helper::localLangHelperFactory('mai_account', 'Default/locallang_tca.xlf');

return (new Table($lang('table.tx_maiaccount_story')))
    ->setDefaultConfig()
    ->setLabel('title')
    ->setSearchFields('title, content')
    ->setIconFile('EXT:mai_account/Resources/Public/Icons/tx_maiaccount_story.svg')
    ->setDefaultSorting('ORDER BY submitted_at DESC')
    ->addColumn(
        'title',
        $lang('tx_maiaccount_story.title'),
        ['type' => 'input', 'size' => 50, 'max' => 255, 'eval' => 'trim,required']
    )
    ->addColumn(
        'content',
        $lang('tx_maiaccount_story.content'),
        [
            'type' => 'text',
            'rows' => 15,
            'cols' => 50,
            'enableRichtext' => true,
            'richtextConfiguration' => 'default',
        ]
    )
    ->addColumn(
        'media',
        $lang('tx_maiaccount_story.media'),
        [
            'type' => 'file',
            'allowed' => 'common-image-types,mp4,webm',
            'maxitems' => 10,
            'appearance' => [
                'createNewRelationLinkTitle' => $lang('tx_maiaccount_story.media.addFile'),
            ],
        ]
    )
    ->addColumn(
        'fe_user',
        $lang('tx_maiaccount_story.fe_user'),
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
        $lang('tx_maiaccount_story.status'),
        [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                ['label' => $lang('tx_maiaccount_story.status.submitted'), 'value' => 'submitted'],
                ['label' => $lang('tx_maiaccount_story.status.reviewing'), 'value' => 'reviewing'],
                ['label' => $lang('tx_maiaccount_story.status.published'), 'value' => 'published'],
                ['label' => $lang('tx_maiaccount_story.status.rejected'), 'value' => 'rejected'],
            ],
            'default' => 'submitted',
        ]
    )
    ->addColumn(
        'submitted_at',
        $lang('tx_maiaccount_story.submitted_at'),
        ['type' => 'datetime', 'format' => 'datetime', 'readOnly' => true]
    )
    ->addColumn(
        'published_at',
        $lang('tx_maiaccount_story.published_at'),
        ['type' => 'datetime', 'format' => 'datetime']
    )
    ->addPalette(
        'dates',
        $lang('palette.dates'),
        'submitted_at, published_at'
    )
    ->addTypeShowItem(
        '0',
        'title, content, media,
        --div--;' . $lang('tab.meta') . ', fe_user, status, --palette--;;dates,
        --div--;' . $lang('tab.language') . ', --palette--;;language,
        --div--;' . $lang('tab.access') . ', --palette--;;hidden, --palette--;;access'
    )
    ->getConfig();
