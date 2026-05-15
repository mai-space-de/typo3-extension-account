<?php

declare(strict_types=1);

use Maispace\MaiBase\TableConfigurationArray\FieldConfig\DatetimeConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\FileConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\InputConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\SelectSingleConfig;
use Maispace\MaiBase\TableConfigurationArray\FieldConfig\TextConfig;
use Maispace\MaiBase\TableConfigurationArray\Helper;
use Maispace\MaiBase\TableConfigurationArray\Table;

$lang = Helper::localLangHelperFactory('mai_account', 'Default/locallang_tca.xlf');

return (new Table($lang('table.tx_maiaccount_story')))
    ->setDefaultConfig()
    ->setLabel('title')
    ->setIconFile('EXT:mai_base/Resources/Public/Icons/generic_table.svg')
    ->setDefaultSorting('ORDER BY submitted_at DESC')
    ->addColumn(
        'title',
        $lang('tx_maiaccount_story.title'),
        (new InputConfig())->setSize(50)->setMax(255)->setEval('trim')->setRequired()
    )
    ->addColumn(
        'content',
        $lang('tx_maiaccount_story.content'),
        (new TextConfig())->setRows(15)->setCols(50)->enableRte()->setRichtextConfiguration('default')
    )
    ->addColumn(
        'media',
        $lang('tx_maiaccount_story.media'),
        (new FileConfig())
            ->setAllowed('common-image-types,mp4,webm')
            ->setMaxItems(10)
            ->setAppearance([
                'createNewRelationLinkTitle' => $lang('tx_maiaccount_story.media.addFile'),
            ])
    )
    ->addColumn(
        'fe_user',
        $lang('tx_maiaccount_story.fe_user'),
        (new SelectSingleConfig())
            ->setForeignTable('fe_users')
            ->setForeignTableWhere('ORDER BY fe_users.username')
            ->setMinItems(1)
            ->setMaxItems(1)
    )
    ->addColumn(
        'status',
        $lang('tx_maiaccount_story.status'),
        (new SelectSingleConfig())
            ->setItems([
                ['label' => $lang('tx_maiaccount_story.status.submitted'), 'value' => 'submitted'],
                ['label' => $lang('tx_maiaccount_story.status.reviewing'), 'value' => 'reviewing'],
                ['label' => $lang('tx_maiaccount_story.status.published'), 'value' => 'published'],
                ['label' => $lang('tx_maiaccount_story.status.rejected'), 'value' => 'rejected'],
            ])
            ->setDefault('submitted')
    )
    ->addColumn(
        'submitted_at',
        $lang('tx_maiaccount_story.submitted_at'),
        (new DatetimeConfig())->setFormat('datetime')->setReadOnly()
    )
    ->addColumn(
        'published_at',
        $lang('tx_maiaccount_story.published_at'),
        (new DatetimeConfig())->setFormat('datetime')
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
