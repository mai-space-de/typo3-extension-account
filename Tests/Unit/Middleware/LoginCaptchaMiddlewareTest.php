<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Tests\Unit\Middleware;

use Maispace\MaiAccount\Middleware\LoginCaptchaMiddleware;
use Maispace\MaiBase\Captcha\CaptchaVerifier;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\NullLogger;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\ServerRequest;

final class LoginCaptchaMiddlewareTest extends TestCase
{
    /** Not enforced and unconfigured: tokens are accepted without a request to the captcha server. */
    private function acceptingVerifier(): CaptchaVerifier
    {
        return new CaptchaVerifier($this->createMock(RequestFactory::class), new NullLogger(), null, null, false);
    }

    /** Enforced and unconfigured: every token is rejected. */
    private function rejectingVerifier(): CaptchaVerifier
    {
        return new CaptchaVerifier($this->createMock(RequestFactory::class), new NullLogger(), null, null, true);
    }

    private function handle(CaptchaVerifier $verifier, ServerRequestInterface $request): ServerRequestInterface
    {
        $handler = new class implements RequestHandlerInterface {
            public ?ServerRequestInterface $received = null;

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->received = $request;

                return new Response();
            }
        };

        (new LoginCaptchaMiddleware($verifier))->process($request, $handler);
        self::assertNotNull($handler->received);

        return $handler->received;
    }

    private function loginPost(): ServerRequestInterface
    {
        return (new ServerRequest('https://example.test/anmelden', 'POST'))
            ->withParsedBody(['logintype' => 'login', 'user' => 'max', 'pass' => 'secret', 'mai_captcha_token' => 'tok']);
    }

    #[Test]
    public function requestsWithoutLoginAttemptPassUntouchedTest(): void
    {
        $request = (new ServerRequest('https://example.test/', 'GET'))->withQueryParams(['foo' => 'bar']);

        $received = $this->handle($this->rejectingVerifier(), $request);

        self::assertSame($request, $received);
    }

    #[Test]
    public function logoutIsNotBlockedTest(): void
    {
        $request = (new ServerRequest('https://example.test/', 'POST'))->withParsedBody(['logintype' => 'logout']);

        self::assertSame($request, $this->handle($this->rejectingVerifier(), $request));
    }

    #[Test]
    public function validCaptchaKeepsLoginDataTest(): void
    {
        $received = $this->handle($this->acceptingVerifier(), $this->loginPost());

        self::assertSame('max', $received->getParsedBody()['user']);
        self::assertNull($received->getAttribute(LoginCaptchaMiddleware::FAILED_ATTRIBUTE));
    }

    #[Test]
    public function failedCaptchaRemovesLoginDataAndFlagsRequestTest(): void
    {
        $received = $this->handle($this->rejectingVerifier(), $this->loginPost());

        self::assertArrayNotHasKey('logintype', $received->getParsedBody());
        self::assertArrayNotHasKey('user', $received->getParsedBody());
        self::assertArrayNotHasKey('pass', $received->getParsedBody());
        self::assertTrue($received->getAttribute(LoginCaptchaMiddleware::FAILED_ATTRIBUTE));
    }

    #[Test]
    public function loginViaQueryStringIsAlwaysRejectedTest(): void
    {
        $request = (new ServerRequest('https://example.test/anmelden', 'GET'))
            ->withQueryParams(['logintype' => 'login', 'user' => 'max', 'pass' => 'secret']);

        $received = $this->handle($this->acceptingVerifier(), $request);

        self::assertArrayNotHasKey('logintype', $received->getQueryParams());
        self::assertArrayNotHasKey('pass', $received->getQueryParams());
        self::assertTrue($received->getAttribute(LoginCaptchaMiddleware::FAILED_ATTRIBUTE));
    }
}
