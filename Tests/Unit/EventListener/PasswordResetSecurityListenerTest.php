<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Tests\Unit\EventListener;

use Maispace\MaiAccount\EventListener\PasswordResetSecurityListener;
use Maispace\MaiAccount\Service\PasswordResetLogService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\FrontendLogin\Event\PasswordChangeEvent;
use TYPO3\CMS\FrontendLogin\Event\SendRecoveryEmailEvent;

final class PasswordResetSecurityListenerTest extends TestCase
{
    private PasswordResetLogService&MockObject $logService;
    private PasswordResetSecurityListener $subject;

    protected function setUp(): void
    {
        $this->logService = $this->createMock(PasswordResetLogService::class);
        $this->subject = new PasswordResetSecurityListener($this->logService);
    }

    #[Test]
    public function sendRecoveryEmailEventLogsResetRequest(): void
    {
        $userData = [
            'uid' => 42,
            'email' => 'user@example.com',
            'username' => 'johndoe',
        ];

        $email = $this->createMock(FluidEmail::class);
        $event = new SendRecoveryEmailEvent($email, $userData);

        $_SERVER['REMOTE_ADDR'] = '203.0.113.42';

        $this->logService
            ->expects(self::once())
            ->method('logResetRequest')
            ->with('user@example.com', '203.0.113.42', 42)
            ->willReturn(1);

        ($this->subject)($event);
    }

    #[Test]
    public function sendRecoveryEmailEventSkipsLoggingWhenEmailIsEmpty(): void
    {
        $userData = [
            'uid' => 5,
            'email' => '',
            'username' => 'nodata',
        ];

        $email = $this->createMock(FluidEmail::class);
        $event = new SendRecoveryEmailEvent($email, $userData);

        $this->logService
            ->expects(self::never())
            ->method('logResetRequest');

        ($this->subject)($event);
    }

    #[Test]
    public function passwordChangeEventLogsCompletion(): void
    {
        $userData = [
            'uid' => 42,
            'email' => 'user@example.com',
            'username' => 'johndoe',
        ];

        $request = $this->createMock(ServerRequestInterface::class);
        $event = new PasswordChangeEvent($userData, '$2y$hashed', 'newpassword', $request);

        $this->logService
            ->expects(self::once())
            ->method('logResetCompleted')
            ->with('user@example.com');

        ($this->subject)($event);
    }

    #[Test]
    public function passwordChangeEventSkipsLoggingWhenEmailIsEmpty(): void
    {
        $userData = [
            'uid' => 7,
            'email' => '',
            'username' => 'noemail',
        ];

        $request = $this->createMock(ServerRequestInterface::class);
        $event = new PasswordChangeEvent($userData, '$2y$hashed', 'newpass', $request);

        $this->logService
            ->expects(self::never())
            ->method('logResetCompleted');

        ($this->subject)($event);
    }

    #[Test]
    public function listenerFallsBackToUnknownWhenRemoteAddrNotSet(): void
    {
        $userData = [
            'uid' => 1,
            'email' => 'fallback@example.com',
        ];

        $email = $this->createMock(FluidEmail::class);
        $event = new SendRecoveryEmailEvent($email, $userData);

        unset($_SERVER['REMOTE_ADDR']);

        $this->logService
            ->expects(self::once())
            ->method('logResetRequest')
            ->with('fallback@example.com', 'unknown', 1)
            ->willReturn(3);

        ($this->subject)($event);
    }

    protected function tearDown(): void
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        parent::tearDown();
    }
}
