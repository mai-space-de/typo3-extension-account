<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\EventListener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Authentication\Event\BeforeRequestTokenProcessedEvent;
use TYPO3\CMS\Core\Security\RequestToken;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * Applies storage-PID constraints from the login form request token (same as EXT:felogin).
 */
final class LoginRequestTokenListener
{
    #[AsEventListener('mai-account-login-request-token')]
    public function __invoke(BeforeRequestTokenProcessedEvent $event): void
    {
        $user = $event->getUser();
        $requestToken = $event->getRequestToken();
        if (!$user instanceof FrontendUserAuthentication || !$requestToken instanceof RequestToken) {
            return;
        }

        $pidParam = (string) ($requestToken->params['pid'] ?? '');
        if ($user->checkPid) {
            $user->checkPid_value = $pidParam;
        }
    }
}
