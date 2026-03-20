<?php

declare(strict_types=1);

namespace Maispace\Account\Controller;

use Maispace\Account\Domain\Repository\FrontendUserRepository;
use Maispace\Account\Service\MfaService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class MfaController extends ActionController
{
    public function __construct(
        private readonly MfaService $mfaService,
        private readonly FrontendUserRepository $frontendUserRepository
    ) {}

    public function indexAction(): ResponseInterface
    {
        $user = $this->getCurrentUser();
        if ($user === null) {
            return $this->redirect('index', 'Login');
        }
        $this->view->assign('user', $user);
        return $this->htmlResponse();
    }

    public function setupAction(): ResponseInterface
    {
        $user = $this->getCurrentUser();
        if ($user === null) {
            return $this->redirect('index', 'Login');
        }

        if ($this->request->getMethod() === 'POST') {
            $secret = $this->request->getArgument('secret') ?? '';
            $code = $this->request->getArgument('code') ?? '';

            try {
                $backupCodes = $this->mfaService->enable($user, (string)$secret, (string)$code);
                $this->view->assign('backupCodes', $backupCodes);
                $this->view->assign('mfaEnabled', true);
            } catch (\InvalidArgumentException $e) {
                $setupData = $this->mfaService->generateSetupData($user);
                $this->view->assign('error', 'Ungültiger Code. Bitte erneut versuchen.');
                $this->view->assign('setupData', $setupData);
            }
        } else {
            $setupData = $this->mfaService->generateSetupData($user);
            $this->view->assign('setupData', $setupData);
        }

        $this->view->assign('user', $user);
        return $this->htmlResponse();
    }

    public function verifyAction(): ResponseInterface
    {
        $user = $this->getCurrentUser();
        if ($user === null) {
            return $this->redirect('index', 'Login');
        }

        if ($this->request->getMethod() === 'POST') {
            $code = $this->request->getArgument('code') ?? '';
            $valid = $this->mfaService->verify($user, (string)$code);
            $this->view->assign('valid', $valid);

            if ($valid && !empty($this->settings['loginRedirectPid'])) {
                return $this->redirect(null, null, null, [], (int)$this->settings['loginRedirectPid']);
            }
        }

        $this->view->assign('user', $user);
        return $this->htmlResponse();
    }

    public function disableAction(): ResponseInterface
    {
        $user = $this->getCurrentUser();
        if ($user === null) {
            return $this->redirect('index', 'Login');
        }

        if ($this->request->getMethod() === 'POST') {
            $this->mfaService->disable($user);
            return $this->redirect('index');
        }

        $this->view->assign('user', $user);
        return $this->htmlResponse();
    }

    private function getCurrentUser(): ?\Maispace\Account\Domain\Model\FrontendUser
    {
        $context = GeneralUtility::makeInstance(Context::class);
        $isLoggedIn = (bool)$context->getPropertyFromAspect('frontend.user', 'isLoggedIn', false);
        if (!$isLoggedIn) {
            return null;
        }
        $uid = (int)$context->getPropertyFromAspect('frontend.user', 'id', 0);
        return $this->frontendUserRepository->findByUid($uid);
    }
}
