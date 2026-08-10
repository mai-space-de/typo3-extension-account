<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Event;

use TYPO3\CMS\Extbase\Mvc\RequestInterface;

/**
 * Dispatched by AccountController::loginAction() when
 * LoginFormSupport::hasLoginFailed() detects a failed login attempt.
 *
 * Deliberately carries no opinion about what a listener does with a
 * failure (log it, report it to a firewall/fail2ban rule, ...); mai_account
 * stays unaware of any specific consumer.
 */
final readonly class LoginFailedEvent
{
    public function __construct(
        private RequestInterface $request,
    ) {}

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
