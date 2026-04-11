<?php

declare(strict_types=1);

use Maispace\MaiBase\TableConfigurationArray\Helper;
use Maispace\MaiBase\TableConfigurationArray\Table;

$lang = Helper::localLangHelperFactory('mai_account', 'Default/locallang_tca.xlf');

return (new Table($lang('table.tx_maiaccount_interest')))
    ->setDefaultConfig()
    ->setLabel('title')
    ->setSearchFields('title, identifier')
    ->setIconFile('EXT:mai_account/Resources/Public/Icons/tx_maiaccount_interest.svg')
    ->setSortingField()
    ->addColumn(
        'title',
        $lang('tx_maiaccount_interest.title'),
        ['type' => 'input', 'size' => 50, 'max' => 255, 'eval' => 'trim,required']
    )
    ->addColumn(
        'identifier',
        $lang('tx_maiaccount_interest.identifier'),
        [
            'type' => 'slug',
            'size' => 50,
            'generatorOptions' => [
                'fields' => ['title'],
                'replacements' => [' ' => '-'],
            ],
            'fallbackCharacter' => '-',
            'eval' => 'uniqueInSite',
        ]
    )
    ->addTypeShowItem(
        '0',
        'title, identifier,
        --div--;' . $lang('tab.language') . ', --palette--;;language,
        --div--;' . $lang('tab.access') . ', --palette--;;hidden, --palette--;;access'
    )
    ->getConfig();
