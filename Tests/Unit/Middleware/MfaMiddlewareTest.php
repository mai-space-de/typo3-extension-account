<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Tests\Unit\Middleware;

use Maispace\MaiAccount\Middleware\MfaMiddleware;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

final class MfaMiddlewareTest extends TestCase
{
    private MfaMiddleware $subject;
    private RequestHandlerInterface&MockObject $handler;
    private ServerRequestInterface&MockObject $request;
    private object $tsfe;
    private FrontendUserAuthentication&MockObject $feUser;
    private UriInterface&MockObject $uri;

    protected function setUp(): void
    {
        $this->subject = new MfaMiddleware();
        $this->handler = $this->createMock(RequestHandlerInterface::class);
        $this->request = $this->createMock(ServerRequestInterface::class);
        $this->tsfe = new \stdClass();
        $this->feUser = $this->createMock(FrontendUserAuthentication::class);
        $this->uri = $this->createMock(UriInterface::class);
    }

    #[Test]
    public function passesThroughWhenTsfeIsNotAvailable(): void
    {
        $this->handler->expects(self::once())->method('handle')->with($this->request);
        $this->subject->process($this->request, $this->handler);
    }

    #[Test]
    public function passesThroughWhenFeUserIsNotAvailable(): void
    {
        $GLOBALS['TSFE'] = $this->tsfe;
        $this->handler->expects(self::once())->method('handle')->with($this->request);
        $this->subject->process($this->request, $this->handler);
    }

    #[Test]
    public function passesThroughWhenUserIsNotLoggedIn(): void
    {
        $this->setupBasicTsfe();
        $this->feUser->user = ['uid' => 0];

        $this->handler->expects(self::once())->method('handle')->with($this->request);
        $this->subject->process($this->request, $this->handler);
    }

    #[Test]
    public function passesThroughWhenMfaIsNotEnabled(): void
    {
        $this->setupBasicTsfe();
        $this->feUser->user = ['uid' => 42, 'tx_maiaccount_mfa_enabled' => 0];

        $this->handler->expects(self::once())->method('handle')->with($this->request);
        $this->subject->process($this->request, $this->handler);
    }

    #[Test]
    public function passesThroughWhenMfaAlreadyVerified(): void
    {
        $this->setupBasicTsfe();
        $this->feUser->user = ['uid' => 42, 'tx_maiaccount_mfa_enabled' => 1];
        $this->feUser->method('getSessionData')->willReturnMap([
            ['mfa_verified', true],
        ]);

        $this->handler->expects(self::once())->method('handle')->with($this->request);
        $this->subject->process($this->request, $this->handler);
    }

    #[Test]
    public function passesThroughWhenMfaVerifyPidNotConfigured(): void
    {
        $this->setupBasicTsfe();
        $this->feUser->user = ['uid' => 42, 'tx_maiaccount_mfa_enabled' => 1];
        $this->feUser->method('getSessionData')->willReturnMap([
            ['mfa_verified', false],
        ]);

        $this->handler->expects(self::once())->method('handle')->with($this->request);
        $this->subject->process($this->request, $this->handler);
    }

    #[Test]
    public function passesThroughWhenAlreadyOnMfaVerifyPage(): void
    {
        $this->setupBasicTsfeWithMfaPid(99);
        $this->feUser->user = ['uid' => 42, 'tx_maiaccount_mfa_enabled' => 1];
        $this->feUser->method('getSessionData')->willReturnMap([
            ['mfa_verified', false],
        ]);

        $routing = new PageArguments(99, '0', []);
        $this->request->method('getAttribute')->willReturnMap([
            ['routing', null, $routing],
            ['site', null, null],
        ]);

        $this->handler->expects(self::once())->method('handle')->with($this->request);
        $this->subject->process($this->request, $this->handler);
    }

    #[Test]
    public function redirectsToMfaVerifyPageWhenMfaRequiredAndNotVerified(): void
    {
        $this->setupBasicTsfeWithMfaPid(99);
        $this->feUser->user = ['uid' => 42, 'tx_maiaccount_mfa_enabled' => 1];
        $this->feUser->method('getSessionData')->willReturnMap([
            ['mfa_verified', false],
        ]);

        $this->feUser->expects(self::exactly(2))
            ->method('setAndSaveSessionData')
            ->willReturnCallback(function (string $key, mixed $data): void {
                static $calls = [];
                $calls[] = $key;
                if (count($calls) === 2) {
                    self::assertContains('pending_mfa', $calls);
                    self::assertContains('pending_mfa_return_url', $calls);
                }
            });

        $routing = new PageArguments(1, '0', []);
        $this->request->method('getAttribute')->willReturnMap([
            ['routing', null, $routing],
            ['site', null, null],
        ]);
        $this->request->method('getUri')->willReturn($this->uri);
        $this->uri->method('__toString')->willReturn('https://example.com/protected');
        $this->uri->method('withPath')->willReturn($this->uri);
        $this->uri->method('withQuery')->willReturn($this->uri);
        $this->uri->method('withFragment')->willReturn($this->uri);

        $response = $this->subject->process($this->request, $this->handler);

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame(302, $response->getStatusCode());
    }

    private function setupBasicTsfe(): void
    {
        $GLOBALS['TSFE'] = $this->tsfe;
        $this->tsfe->fe_user = $this->feUser;
    }

    private function setupBasicTsfeWithMfaPid(int $mfaVerifyPid): void
    {
        $this->setupBasicTsfe();
        $this->feUser->user = ['uid' => 42, 'tx_maiaccount_mfa_enabled' => 1];
        $this->tsfe->tmpl = (object) [
            'setup' => [
                'plugin.' => [
                    'tx_maiaccount_account.' => [
                        'settings.' => [
                            'mfaVerifyPid' => (string) $mfaVerifyPid,
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['TSFE']);
    }
}
