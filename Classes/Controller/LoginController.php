<?php

declare(strict_types=1);

namespace Maispace\Account\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class LoginController extends ActionController
{
    public function indexAction(): ResponseInterface
    {
        $context = GeneralUtility::makeInstance(Context::class);
        $isLoggedIn = (bool)$context->getPropertyFromAspect('frontend.user', 'isLoggedIn', false);

        if ($isLoggedIn && !empty($this->settings['loginRedirectPid'])) {
            return $this->redirect(null, null, null, [], (int)$this->settings['loginRedirectPid']);
        }

        $this->view->assign('isLoggedIn', $isLoggedIn);
        return $this->htmlResponse();
    }

    public function loginAction(): ResponseInterface
    {
        $context = GeneralUtility::makeInstance(Context::class);
        $isLoggedIn = (bool)$context->getPropertyFromAspect('frontend.user', 'isLoggedIn', false);

        if ($isLoggedIn && !empty($this->settings['loginRedirectPid'])) {
            return $this->redirect(null, null, null, [], (int)$this->settings['loginRedirectPid']);
        }

        return $this->htmlResponse();
    }

    public function logoutAction(): ResponseInterface
    {
        if (!empty($this->settings['logoutRedirectPid'])) {
            return $this->redirect(null, null, null, [], (int)$this->settings['logoutRedirectPid']);
        }
        return $this->redirect('index');
    }
}
