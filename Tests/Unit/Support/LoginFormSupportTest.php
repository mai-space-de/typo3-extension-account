<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Tests\Unit\Support;

use Maispace\MaiAccount\Support\LoginFormSupport;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class LoginFormSupportTest extends TestCase
{
    #[Test]
    public function resolveLoginTypePrefersPostBody(): void
    {
        self::assertSame(
            'login',
            LoginFormSupport::resolveLoginType(['logintype' => 'login'], ['logintype' => 'logout']),
        );
    }

    #[Test]
    public function resolveLoginTypeFallsBackToQueryParams(): void
    {
        self::assertSame('logout', LoginFormSupport::resolveLoginType([], ['logintype' => 'logout']));
    }

    #[Test]
    public function hasLoginFailedWhenCredentialsWerePostedButUserIsAnonymous(): void
    {
        self::assertTrue(LoginFormSupport::hasLoginFailed('login', false));
    }

    #[Test]
    public function hasLoginFailedIsFalseAfterSuccessfulAuthentication(): void
    {
        self::assertFalse(LoginFormSupport::hasLoginFailed('login', true));
    }

    #[Test]
    public function isFreshLoginSuccessDetectsAuthenticatedPost(): void
    {
        self::assertTrue(LoginFormSupport::isFreshLoginSuccess('login', true));
        self::assertFalse(LoginFormSupport::isFreshLoginSuccess('login', false));
        self::assertFalse(LoginFormSupport::isFreshLoginSuccess('', true));
    }
}
