<?php

declare(strict_types=1);

namespace Maispace\Account\Controller;

use Maispace\Account\Service\RegistrationService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class RegistrationController extends ActionController
{
    public function __construct(
        private readonly RegistrationService $registrationService
    ) {}

    public function indexAction(): ResponseInterface
    {
        return $this->htmlResponse();
    }

    public function registerAction(): ResponseInterface
    {
        if ($this->request->getMethod() !== 'POST') {
            return $this->redirect('index');
        }

        $arguments = $this->request->getArguments();
        $userData = [
            'username' => trim($arguments['username'] ?? ''),
            'email' => trim($arguments['email'] ?? ''),
            'password' => $arguments['password'] ?? '',
            'firstName' => trim($arguments['firstName'] ?? ''),
            'lastName' => trim($arguments['lastName'] ?? ''),
        ];

        if (empty($userData['username']) || empty($userData['email']) || empty($userData['password'])) {
            $this->view->assign('error', 'Bitte alle Pflichtfelder ausfüllen.');
            return $this->htmlResponse();
        }

        $storagePid = (int)($this->settings['storagePid'] ?? 0);
        $confirmationPid = (int)($this->settings['confirmationPid'] ?? 0);
        $baseUrl = $this->uriBuilder->reset()
            ->setTargetPageUid($confirmationPid)
            ->setCreateAbsoluteUri(true)
            ->uriFor('confirm', ['token' => '{token}']);

        try {
            $this->registrationService->register(
                $userData,
                $storagePid,
                $baseUrl,
                $this->settings['emailSenderAddress'] ?? 'noreply@example.com',
                $this->settings['emailSenderName'] ?? 'Account'
            );
            $this->view->assign('success', true);
        } catch (\Exception $e) {
            $this->view->assign('error', $e->getMessage());
        }

        return $this->htmlResponse();
    }

    public function confirmAction(): ResponseInterface
    {
        $token = $this->request->getArgument('token') ?? '';
        $user = $this->registrationService->confirm((string)$token);

        $this->view->assign('success', $user !== null);
        return $this->htmlResponse();
    }

    public function passwordResetFormAction(): ResponseInterface
    {
        return $this->htmlResponse();
    }

    public function passwordResetAction(): ResponseInterface
    {
        if ($this->request->getMethod() !== 'POST') {
            return $this->redirect('passwordResetForm');
        }

        $arguments = $this->request->getArguments();
        $email = trim($arguments['email'] ?? '');
        $token = trim($arguments['token'] ?? '');
        $newPassword = $arguments['newPassword'] ?? '';

        if (!empty($token) && !empty($newPassword)) {
            $user = $this->registrationService->resetPassword($token, $newPassword);
            $this->view->assign('resetSuccess', $user !== null);
        } elseif (!empty($email)) {
            $resetPid = (int)($this->settings['confirmationPid'] ?? 0);
            $baseUrl = $this->uriBuilder->reset()
                ->setTargetPageUid($resetPid)
                ->setCreateAbsoluteUri(true)
                ->uriFor('passwordReset', ['token' => '{token}']);

            $this->registrationService->initiatePasswordReset(
                $email,
                $baseUrl,
                $this->settings['emailSenderAddress'] ?? 'noreply@example.com',
                $this->settings['emailSenderName'] ?? 'Account'
            );
            $this->view->assign('emailSent', true);
        }

        return $this->htmlResponse();
    }
}
