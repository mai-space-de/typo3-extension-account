<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * Enforces MFA verification for authenticated frontend users.
 *
 * Registered after typo3/cms-frontend/authentication in RequestMiddlewares.php.
 */
final class MfaMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $feUser = $this->getAuthenticatedFrontendUser();

        if ($feUser === null) {
            return $handler->handle($request);
        }

        if ($this->isMfaRequired($feUser) === false) {
            return $handler->handle($request);
        }

        $mfaVerifyPid = $this->resolveMfaVerifyPid();

        if ($mfaVerifyPid <= 0) {
            return $handler->handle($request);
        }

        if ($this->isCurrentPage($request, $mfaVerifyPid) || $this->isExemptPage($request)) {
            return $handler->handle($request);
        }

        $feUser->setAndSaveSessionData('pending_mfa', true);
        $feUser->setAndSaveSessionData('pending_mfa_return_url', (string) $request->getUri());

        return new RedirectResponse($this->buildPageUri($mfaVerifyPid, $request), 302);
    }

    private function getAuthenticatedFrontendUser(): ?FrontendUserAuthentication
    {
        $feUser = $GLOBALS['TSFE']->fe_user ?? null;

        if (!$feUser instanceof FrontendUserAuthentication) {
            return null;
        }

        if (!is_array($feUser->user) || empty($feUser->user['uid'])) {
            return null;
        }

        return $feUser;
    }

    private function isMfaRequired(FrontendUserAuthentication $feUser): bool
    {
        return !empty($feUser->user['tx_maiaccount_mfa_enabled'])
            && !$feUser->getSessionData('mfa_verified');
    }

    private function resolveMfaVerifyPid(): int
    {
        $tsfe = $GLOBALS['TSFE'] ?? null;

        if ($tsfe !== null && isset($tsfe->tmpl->setup['plugin.']['tx_maiaccount_account.']['settings.']['mfaVerifyPid'])) {
            $pid = (int) $tsfe->tmpl->setup['plugin.']['tx_maiaccount_account.']['settings.']['mfaVerifyPid'];
            if ($pid > 0) {
                return $pid;
            }
        }

        try {
            $pid = (int) ($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['mai_account']['mfaVerifyPid'] ?? 0);
            if ($pid > 0) {
                return $pid;
            }
        } catch (\Throwable) {
        }

        return 0;
    }

    private function isCurrentPage(ServerRequestInterface $request, int $mfaVerifyPid): bool
    {
        $routing = $request->getAttribute('routing');

        if (!$routing instanceof PageArguments) {
            return false;
        }

        return (int) $routing->getPageId() === $mfaVerifyPid;
    }

    private function isExemptPage(ServerRequestInterface $request): bool
    {
        $currentPageId = $this->getCurrentPageId($request);

        if ($currentPageId === null) {
            return false;
        }

        foreach ($this->resolveExemptPids() as $exemptPid) {
            if ($exemptPid === $currentPageId) {
                return true;
            }
        }

        return false;
    }

    private function getCurrentPageId(ServerRequestInterface $request): ?int
    {
        $routing = $request->getAttribute('routing');

        if (!$routing instanceof PageArguments) {
            return null;
        }

        return (int) $routing->getPageId();
    }

    private function resolveExemptPids(): array
    {
        $exempt = [];
        $tsfe = $GLOBALS['TSFE'] ?? null;

        if ($tsfe === null || !isset($tsfe->tmpl->setup['plugin.']['tx_maiaccount_account.']['settings.']['mfaExemptPids'])) {
            return $exempt;
        }

        $pidList = (string) $tsfe->tmpl->setup['plugin.']['tx_maiaccount_account.']['settings.']['mfaExemptPids'];

        if ($pidList === '') {
            return $exempt;
        }

        foreach (explode(',', $pidList) as $pid) {
            $pid = (int) trim($pid);
            if ($pid > 0) {
                $exempt[] = $pid;
            }
        }

        return $exempt;
    }

    private function buildPageUri(int $pageUid, ServerRequestInterface $request): string
    {
        $site = $request->getAttribute('site');

        if ($site instanceof Site) {
            try {
                return (string) $site->getRouter()->generateUri($pageUid);
            } catch (\Throwable) {
            }
        }

        $baseUri = rtrim((string) $request->getUri()->withPath('/')->withQuery('')->withFragment(''), '/');

        return $baseUri . '/index.php?id=' . $pageUid;
    }
}
