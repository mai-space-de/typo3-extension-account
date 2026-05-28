<?php

declare(strict_types=1);

// Load the extension's own autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Manually add mai_member autoloading (optional dependency in suggests, not in composer require)
// The symlink vendor/maispace/mai-member → ../../../typo3-extension-member must exist
spl_autoload_register(function (string $class): void {
    if (str_starts_with($class, 'Maispace\\MaiMember\\')) {
        $relativePath = str_replace('\\', '/', substr($class, strlen('Maispace\\MaiMember\\')));
        $file = __DIR__ . '/../vendor/maispace/mai-member/Classes/' . $relativePath . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});
