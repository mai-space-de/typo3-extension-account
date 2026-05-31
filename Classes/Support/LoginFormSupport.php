<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Support;

use TYPO3\CMS\Core\Authentication\LoginType;

/**
 * Pure helpers for mai_account login form state (mirrors EXT:felogin semantics).
 */
final class LoginFormSupport
{
    public static function resolveLoginType(array $parsedBody, array $queryParams): string
    {
        return (string) ($parsedBody['logintype'] ?? $queryParams['logintype'] ?? '');
    }

    public static function hasLoginFailed(string $loginType, bool $isLoggedIn): bool
    {
        return LoginType::tryFrom($loginType) === LoginType::LOGIN && !$isLoggedIn;
    }

    public static function isFreshLoginSuccess(string $loginType, bool $isLoggedIn): bool
    {
        return LoginType::tryFrom($loginType) === LoginType::LOGIN && $isLoggedIn;
    }
}
