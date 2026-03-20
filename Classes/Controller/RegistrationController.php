<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Controller;

use Maispace\MaiAccount\Service\RegistrationService;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use Psr\Http\Message\ResponseInterface;

class RegistrationController extends ActionController
{
    public function __construct(
        private readonly RegistrationService $registrationService,
        private readonly Context $context,
    ) {
    }

    /**
     * Show registration form.
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
     * Process registration form submission.
     */
    public function registerAction(): ResponseInterface
    {
        $data = $this->request->getParsedBody()['tx_maiaccount_registration'] ?? [];

        $errors = $this->validateRegistrationData($data);

        if (!empty($errors)) {
            $this->view->assign('errors', $errors);
            $this->view->assign('formData', $data);
            return $this->htmlResponse();
        }

        // Storage PID and user group from TypoScript settings
        $storagePid = (int)($this->settings['storagePid'] ?? 0);
        $userGroupUid = (int)($this->settings['defaultUserGroup'] ?? 0);

        $confirmationPageUrl = $this->uriBuilder
            ->reset()
            ->setCreateAbsoluteUri(true)
            ->uriFor('confirm', [], 'Registration', 'mai_account', 'Registration');

        $data['confirmationPageUrl'] = $confirmationPageUrl;

        try {
            $this->registrationService->register($data, $storagePid, $userGroupUid);
            $this->view->assign('registrationSuccess', true);
        } catch (\Exception $e) {
            $this->view->assign('registrationError', $e->getMessage());
        }

        return $this->htmlResponse();
    }

    /**
     * Confirm email address via token from confirmation email.
     */
    public function confirmAction(): ResponseInterface
    {
        $token = trim((string)($this->request->getQueryParams()['tx_maiaccount_registration']['token'] ?? ''));

        if ($token === '') {
            $this->view->assign('invalidToken', true);
            return $this->htmlResponse();
        }

        $user = $this->registrationService->confirmEmail($token);

        if ($user === null) {
            $this->view->assign('confirmationFailed', true);
        } else {
            $this->view->assign('confirmationSuccess', true);
            $this->view->assign('user', $user);
        }

        return $this->htmlResponse();
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, string>
     */
    private function validateRegistrationData(array $data): array
    {
        $errors = [];

        $email = trim((string)($data['email'] ?? ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Bitte geben Sie eine gültige E-Mail-Adresse ein.';
        }

        $password = (string)($data['password'] ?? '');
        $passwordRepeat = (string)($data['passwordRepeat'] ?? '');

        if (strlen($password) < 8) {
            $errors['password'] = 'Das Passwort muss mindestens 8 Zeichen lang sein.';
        } elseif ($password !== $passwordRepeat) {
            $errors['passwordRepeat'] = 'Die Passwörter stimmen nicht überein.';
        }

        if (trim((string)($data['firstName'] ?? '')) === '') {
            $errors['firstName'] = 'Bitte geben Sie Ihren Vornamen ein.';
        }

        if (trim((string)($data['lastName'] ?? '')) === '') {
            $errors['lastName'] = 'Bitte geben Sie Ihren Nachnamen ein.';
        }

        if (empty($data['terms'])) {
            $errors['terms'] = 'Bitte akzeptieren Sie die Nutzungsbedingungen.';
        }

        return $errors;
    }
}
