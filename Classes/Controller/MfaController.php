<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Controller;

use Maispace\MaiAccount\Domain\Repository\FrontendUserRepository;
use Maispace\MaiAccount\Service\MfaService;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use Psr\Http\Message\ResponseInterface;
use OTPHP\TOTP;

class MfaController extends ActionController
{
    public function __construct(
        private readonly MfaService $mfaService,
        private readonly FrontendUserRepository $frontendUserRepository,
        private readonly Context $context,
    ) {
    }

    /**
     * Show MFA setup page (QR code + secret).
     */
    public function setupAction(): ResponseInterface
    {
        $user = $this->getCurrentUser();
        if ($user === null) {
            return $this->redirect('index', 'Login');
        }

        if ($user->isMfaEnabled()) {
            $this->view->assign('alreadyEnabled', true);
            return $this->htmlResponse();
        }

        // Generate a new TOTP and store the secret temporarily in session
        $issuer = (string)($this->settings['mfaIssuer'] ?? 'maispace');
        $totp = $this->mfaService->initSetup($user, $issuer);
        $secret = $totp->getSecret();

        $_SESSION['tx_maiaccount_mfa_setup_secret'] = $secret;

        $this->view->assignMultiple([
            'secret' => $secret,
            'qrCodeUri' => $totp->getProvisioningUri(),
            'user' => $user,
        ]);

        return $this->htmlResponse();
    }

    /**
     * Verify the TOTP code entered during setup and enable MFA.
     */
    public function verifyAction(): ResponseInterface
    {
        $user = $this->getCurrentUser();
        if ($user === null) {
            return $this->redirect('index', 'Login');
        }

        $body = $this->request->getParsedBody()['tx_maiaccount_mfa'] ?? [];
        $code = trim((string)($body['code'] ?? ''));

        // Pending MFA after login
        $pendingUid = $_SESSION['tx_maiaccount_mfa_pending_uid'] ?? null;
        if ($pendingUid !== null) {
            $pendingUid = (int)$pendingUid;
            $pendingUser = $this->frontendUserRepository->findByUid($pendingUid);

            if ($pendingUser === null) {
                return $this->redirect('index', 'Login');
            }

            if ($this->mfaService->verifyMfa($pendingUser, $code)
                || $this->mfaService->verifyBackupCode($pendingUser, $code)) {
                unset($_SESSION['tx_maiaccount_mfa_pending_uid']);
                return $this->redirect('dashboard', 'Profile');
            }

            $this->view->assign('verifyError', true);
            return $this->htmlResponse();
        }

        // MFA setup flow
        $secret = (string)($_SESSION['tx_maiaccount_mfa_setup_secret'] ?? '');
        if ($secret === '') {
            return $this->redirect('setup');
        }

        if ($this->mfaService->verifyCode($secret, $code)) {
            $backupCodes = $this->mfaService->enableMfa($user, $secret);
            unset($_SESSION['tx_maiaccount_mfa_setup_secret']);

            $this->view->assignMultiple([
                'mfaEnabled' => true,
                'backupCodes' => $backupCodes,
            ]);
        } else {
            $this->view->assignMultiple([
                'verifyError' => true,
                'secret' => $secret,
                'qrCodeUri' => TOTP::createFromSecret($secret)->getProvisioningUri(),
            ]);
        }

        return $this->htmlResponse();
    }

    /**
     * Show / regenerate backup codes.
     */
    public function backupCodesAction(): ResponseInterface
    {
        $user = $this->getCurrentUser();
        if ($user === null) {
            return $this->redirect('index', 'Login');
        }

        if (!$user->isMfaEnabled()) {
            return $this->redirect('setup');
        }

        if ($this->request->getMethod() === 'POST') {
            $backupCodes = $this->mfaService->regenerateBackupCodes($user);
            $this->view->assign('backupCodes', $backupCodes);
            $this->view->assign('regenerated', true);
        } else {
            $this->view->assign('codeCount', count($user->getMfaBackupCodes()));
        }

        return $this->htmlResponse();
    }

    /**
     * Disable MFA for the current user.
     */
    public function disableAction(): ResponseInterface
    {
        $user = $this->getCurrentUser();
        if ($user === null) {
            return $this->redirect('index', 'Login');
        }

        if ($this->request->getMethod() === 'POST') {
            $this->mfaService->disableMfa($user);
            $this->view->assign('disabled', true);
        }

        return $this->htmlResponse();
    }

    private function getCurrentUser(): ?\Maispace\MaiAccount\Domain\Model\FrontendUser
    {
        $isLoggedIn = $this->context->getPropertyFromAspect('frontend.user', 'isLoggedIn', false);
        if (!$isLoggedIn) {
            return null;
        }

        $userId = (int)$this->context->getPropertyFromAspect('frontend.user', 'id');
        return $this->frontendUserRepository->findByUid($userId);
    }
}
