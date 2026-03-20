<?php

declare(strict_types=1);

namespace Maispace\Account\Controller;

use Maispace\Account\Service\MfaService;
use Maispace\Account\Service\RegistrationService;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use Psr\Http\Message\ResponseInterface;

class LoginController extends ActionController
{
    public function __construct(
        private readonly RegistrationService $registrationService,
        private readonly MfaService $mfaService,
        private readonly Context $context,
    ) {
    }

    /**
     * Show login form.
     */
    public function indexAction(): ResponseInterface
    {
        $isLoggedIn = $this->context->getPropertyFromAspect('frontend.user', 'isLoggedIn', false);

        if ($isLoggedIn) {
            return $this->redirect('dashboard', 'Profile');
        }

        return $this->htmlResponse();
    }

    /**
     * Process login form submission.
     * TYPO3 handles FE login natively via fe_login; this action handles MFA interception.
     */
    public function loginAction(): ResponseInterface
    {
        // TYPO3's FE login middleware handles credential check.
        // After successful credential check, if MFA is enabled, redirect to MFA verification.
        $isLoggedIn = $this->context->getPropertyFromAspect('frontend.user', 'isLoggedIn', false);

        if ($isLoggedIn) {
            $userId = (int)$this->context->getPropertyFromAspect('frontend.user', 'id');

            // Check if MFA verification is still pending (set by MFA interception hook)
            if (isset($_SESSION['tx_account_mfa_pending_uid']) && $_SESSION['tx_account_mfa_pending_uid'] === $userId) {
                return $this->redirect('verify', 'Mfa');
            }

            return $this->redirect('dashboard', 'Profile');
        }

        $this->view->assign('loginError', true);
        return $this->htmlResponse();
    }

    /**
     * Logout action – delegates to TYPO3 FE logout.
     */
    public function logoutAction(): ResponseInterface
    {
        return $this->redirect('index');
    }

    /**
     * Show password reset request form.
     */
    public function passwordResetAction(): ResponseInterface
    {
        if ($this->request->getMethod() === 'POST') {
            $email = trim((string)($this->request->getParsedBody()['tx_account_login']['email'] ?? ''));

            if ($email !== '') {
                $resetPageUrl = $this->uriBuilder
                    ->reset()
                    ->setCreateAbsoluteUri(true)
                    ->uriFor('passwordResetConfirm', [], 'Login', 'account', 'Login');

                $this->registrationService->initiatePasswordReset($email, $resetPageUrl);
            }

            $this->view->assign('resetRequested', true);
        }

        return $this->htmlResponse();
    }

    /**
     * Show and process the password reset confirmation form (token from email).
     */
    public function passwordResetConfirmAction(): ResponseInterface
    {
        $token = trim((string)($this->request->getQueryParams()['tx_account_login']['token'] ?? ''));

        if ($token === '') {
            $this->view->assign('invalidToken', true);
            return $this->htmlResponse();
        }

        if ($this->request->getMethod() === 'POST') {
            $body = $this->request->getParsedBody()['tx_account_login'] ?? [];
            $newPassword = (string)($body['password'] ?? '');
            $newPasswordRepeat = (string)($body['passwordRepeat'] ?? '');

            if ($newPassword === '' || $newPassword !== $newPasswordRepeat) {
                $this->view->assign('passwordMismatch', true);
                $this->view->assign('token', $token);
                return $this->htmlResponse();
            }

            $success = $this->registrationService->resetPassword($token, $newPassword);
            $this->view->assign('resetSuccess', $success);
            $this->view->assign('resetFailed', !$success);
        } else {
            $this->view->assign('token', $token);
        }

        return $this->htmlResponse();
    }
}
