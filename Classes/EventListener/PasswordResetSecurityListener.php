<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\EventListener;

use Maispace\MaiAccount\Service\PasswordResetLogService;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\FrontendLogin\Event\PasswordChangeEvent;
use TYPO3\CMS\FrontendLogin\Event\SendRecoveryEmailEvent;

/**
 * Security listener that records every felogin password-reset sequence.
 *
 * Reset requests are logged on SendRecoveryEmailEvent; completions are
 * recorded on PasswordChangeEvent. Incomplete entries past the timeout
 * window (see PasswordResetLogService) are flagged as failed sequences
 * for security audit purposes.
 */
#[AsEventListener(identifier: 'mai-account/password-reset-security')]
final class PasswordResetSecurityListener
{
    public function __construct(
        private readonly PasswordResetLogService $logService,
    ) {}

    public function __invoke(SendRecoveryEmailEvent|PasswordChangeEvent $event): void
    {
        if ($event instanceof SendRecoveryEmailEvent) {
            $this->handleRecoveryEmail($event);
        } elseif ($event instanceof PasswordChangeEvent) {
            $this->handlePasswordChange($event);
        }
    }

    private function handleRecoveryEmail(SendRecoveryEmailEvent $event): void
    {
        $userData = $event->getUserInformation();
        $email = (string) ($userData['email'] ?? '');
        $feUserUid = (int) ($userData['uid'] ?? 0);

        if ($email === '') {
            return;
        }

        $this->logService->logResetRequest(
            $email,
            $this->resolveClientIp(),
            $feUserUid,
        );
    }

    private function handlePasswordChange(PasswordChangeEvent $event): void
    {
        $userData = $event->getUser();
        $email = (string) ($userData['email'] ?? '');

        if ($email === '') {
            return;
        }

        $this->logService->logResetCompleted($email);
    }

    private function resolveClientIp(): string
    {
        $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';

        if (is_string($remoteAddr) && $remoteAddr !== '') {
            return $remoteAddr;
        }

        return 'unknown';
    }
}
