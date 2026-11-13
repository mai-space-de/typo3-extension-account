<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Middleware;

use Maispace\MaiBase\Captcha\CaptchaVerifier;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Authentication\LoginType;

/**
 * Requires a valid invisible captcha for frontend login attempts.
 *
 * TYPO3 authenticates the visitor in a middleware, before any controller runs, so the
 * check cannot live in an action. A failed check removes the login data from the request:
 * no authentication is attempted and the login form shows the captcha error instead.
 *
 * Registered before typo3/cms-frontend/authentication in RequestMiddlewares.php.
 */
final readonly class LoginCaptchaMiddleware implements MiddlewareInterface
{
    public const string FAILED_ATTRIBUTE = 'mai_account.captchaFailed';

    private const array LOGIN_FIELDS = ['logintype', 'user', 'pass', 'pid', 'permalogin', 'redirect_url'];

    public function __construct(private CaptchaVerifier $captchaVerifier) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->isLoginAttempt($request)) {
            return $handler->handle($request);
        }

        // Only the POST body counts: a login attempt via query string never carries a token.
        if ($request->getMethod() === 'POST' && $this->captchaVerifier->verifyRequest($request)) {
            return $handler->handle($request);
        }

        return $handler->handle($this->withoutLoginData($request)->withAttribute(self::FAILED_ATTRIBUTE, true));
    }

    private function isLoginAttempt(ServerRequestInterface $request): bool
    {
        $body = $request->getParsedBody();
        $loginType = is_array($body) && isset($body['logintype'])
            ? $body['logintype']
            : ($request->getQueryParams()['logintype'] ?? '');

        return is_string($loginType) && LoginType::tryFrom($loginType) === LoginType::LOGIN;
    }

    private function withoutLoginData(ServerRequestInterface $request): ServerRequestInterface
    {
        $body = $request->getParsedBody();
        if (is_array($body)) {
            $request = $request->withParsedBody(array_diff_key($body, array_flip(self::LOGIN_FIELDS)));
        }

        return $request->withQueryParams(array_diff_key($request->getQueryParams(), array_flip(self::LOGIN_FIELDS)));
    }
}
