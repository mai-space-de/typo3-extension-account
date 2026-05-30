<?php

declare(strict_types=1);

return [
    \Maispace\MaiAccount\Domain\Model\Interest::class => [
        'tableName' => 'tx_maiaccount_interest',
    ],
    \Maispace\MaiAccount\Domain\Model\Reminder::class => [
        'tableName' => 'tx_maiaccount_reminder',
    ],
    \Maispace\MaiAccount\Domain\Model\Story::class => [
        'tableName' => 'tx_maiaccount_story',
    ],
    \Maispace\MaiAccount\Domain\Model\FrontendUser::class => [
        'tableName' => 'fe_users',
    ],
];
