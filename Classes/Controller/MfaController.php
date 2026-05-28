<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Controller;

use Maispace\MaiAccount\Service\MfaService;
use Maispace\MaiBase\Controller\AbstractActionController;
use Maispace\MaiBase\Controller\Traits\FlashMessageTrait;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use Maispace\MaiAccount\Domain\Repository\FrontendUserRepository;

class MfaController extends AbstractActionController
{
    use FlashMessageTrait;

    public function __construct(
        private readonly MfaService $mfaService,
        private readonly FrontendUserRepository $frontendUserRepository,
        private readonly Context $context,
    ) {}

    public function setupAction(string $secret = ''): ResponseInterface
    {
        $feUserUid = (int) $this->context->getPropertyFromAspect('frontend.user', 'id');

        if ($feUserUid === 0) {
            return $this->htmlResponse();
        }

        if ($this->request->getMethod() === 'POST' && $secret !== '') {
            $user = $this->frontendUserRepository->findByUid($feUserUid);
            $user->setTxMaiaccountMfaSecret($secret);
            $user->setTxMaiaccountMfaEnabled(true);
            $this->frontendUserRepository->update($user);

            $feUser = $this->getFrontendUserAuthentication();
            if ($feUser !== null) {
                $feUser->setAndSaveSessionData('mfa_verified', true);
                $feUser->setAndSaveSessionData('pending_mfa', false);
            }

            $this->flashSuccess('MFA has been set up successfully.');
            return $this->redirect('profile', 'Account', 'MaiAccount');
        }

        $user = $this->frontendUserRepository->findByUid($feUserUid);
        $generatedSecret = $this->mfaService->generateSecret();
        $qrCodeUri = $this->mfaService->getQrCodeUri($generatedSecret, (string) $user->getUsername());

        $this->view->assignMultiple([
            'user' => $user,
            'secret' => $generatedSecret,
            'qrCodeUri' => $qrCodeUri,
        ]);

        return $this->htmlResponse();
    }

    public function verifyAction(string $code = ''): ResponseInterface
    {
        $feUserUid = (int) $this->context->getPropertyFromAspect('frontend.user', 'id');

        if ($feUserUid === 0) {
            return $this->htmlResponse();
        }

        $feUser = $this->getFrontendUserAuthentication();

        if ($feUser !== null && $feUser->getSessionData('mfa_verified')) {
            return $this->redirectAfterMfa($feUser);
        }

        if ($this->request->getMethod() === 'POST') {
            $code = trim($code);

            if ($code === '') {
                $this->flashError('Please enter the verification code.');
                return $this->redirect('verify');
            }

            $user = $this->frontendUserRepository->findByUid($feUserUid);
            $secret = $user->getTxMaiaccountMfaSecret();

            if ($secret === '') {
                $this->flashError('MFA is not set up. Please set up MFA first.');
                return $this->redirect('setup');
            }

            if ($this->mfaService->verifyCode($secret, $code)) {
                if ($feUser !== null) {
                    $feUser->setAndSaveSessionData('mfa_verified', true);
                    $feUser->setAndSaveSessionData('pending_mfa', false);
                }

                return $this->redirectAfterMfa($feUser);
            }

            $this->flashError('Invalid verification code. Please try again.');
            return $this->redirect('verify');
        }

        return $this->htmlResponse();
    }

    public function disableAction(): ResponseInterface
    {
        $feUserUid = (int) $this->context->getPropertyFromAspect('frontend.user', 'id');

        if ($feUserUid === 0) {
            return $this->htmlResponse();
        }

        if ($this->request->getMethod() === 'POST') {
            $user = $this->frontendUserRepository->findByUid($feUserUid);
            $user->setTxMaiaccountMfaEnabled(false);
            $user->setTxMaiaccountMfaSecret('');
            $this->frontendUserRepository->update($user);

            $feUser = $this->getFrontendUserAuthentication();
            if ($feUser !== null) {
                $feUser->setAndSaveSessionData('mfa_verified', false);
                $feUser->setAndSaveSessionData('pending_mfa', false);
            }

            $this->flashSuccess('MFA has been disabled.');
            return $this->redirect('profile', 'Account', 'MaiAccount');
        }

        return $this->htmlResponse();
    }

    private function redirectAfterMfa(?FrontendUserAuthentication $feUser): ResponseInterface
    {
        if ($feUser !== null) {
            $returnUrl = (string) $feUser->getSessionData('pending_mfa_return_url');
            if ($returnUrl !== '') {
                $feUser->setAndSaveSessionData('pending_mfa_return_url', '');

                return $this->redirectToUri($returnUrl);
            }
        }

        return $this->redirect('profile', 'Account', 'MaiAccount');
    }

    private function getFrontendUserAuthentication(): ?FrontendUserAuthentication
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
}
