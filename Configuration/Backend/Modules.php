<?php

declare(strict_types=1);

use Maispace\MaiAccount\Controller\Backend\StoryBackendController;

return [
    'mai_stories' => [
        'parent' => 'web',
        'access' => 'user',
        'workspaces' => 'online',
        'path' => '/module/mai-stories',
        'iconIdentifier' => 'mai-backend-module',
        'labels' => 'LLL:EXT:mai_account/Resources/Private/Language/locallang_mod.xlf',
        'extensionName' => 'MaiAccount',
        'controllerActions' => [
            StoryBackendController::class => ['index', 'approve', 'reject'],
        ],
    ],
];
