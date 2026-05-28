<?php

declare(strict_types=1);

use Maispace\MaiAccount\Middleware\MfaMiddleware;

return [
    'frontend' => [
        'maispace/mai-account/mfa' => [
            'target' => MfaMiddleware::class,
            'after' => [
                'typo3/cms-frontend/authentication',
            ],
            'before' => [
                'typo3/cms-frontend/prepare-tsfe-rendering',
            ],
        ],
    ],
];
