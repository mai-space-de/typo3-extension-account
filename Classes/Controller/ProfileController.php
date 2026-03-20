<?php

declare(strict_types=1);

namespace Maispace\Account\Controller;

use Maispace\Account\Domain\Model\FrontendUser;
use Maispace\Account\Domain\Repository\FrontendUserRepository;
use Maispace\Account\Service\ProfileService;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use Psr\Http\Message\ResponseInterface;

class ProfileController extends ActionController
{
    public function __construct(
        private readonly ProfileService $profileService,
        private readonly FrontendUserRepository $frontendUserRepository,
        private readonly Context $context,
    ) {
    }

    /**
     * Profile dashboard overview.
     */
    public function dashboardAction(): ResponseInterface
    {
        $user = $this->requireLoggedInUser();
        if ($user === null) {
            return $this->redirect('index', 'Login');
        }

        $this->view->assign('user', $user);

        return $this->htmlResponse();
    }

    /**
     * Show and update interests.
     */
    public function updateInterestsAction(): ResponseInterface
    {
        $user = $this->requireLoggedInUser();
        if ($user === null) {
            return $this->redirect('index', 'Login');
        }

        $availableInterests = $this->getAvailableInterests();

        if ($this->request->getMethod() === 'POST') {
            $body = $this->request->getParsedBody()['tx_account_profile'] ?? [];
            $selectedInterests = (array)($body['interests'] ?? []);

            $this->profileService->updateInterests($user, $selectedInterests);
            $this->view->assign('updated', true);
        }

        $this->view->assignMultiple([
            'user' => $user,
            'availableInterests' => $availableInterests,
            'userInterests' => $user->getInterests(),
        ]);

        return $this->htmlResponse();
    }

    /**
     * Update newsletter opt-in and reminder opt-in status.
     */
    public function updateNewsletterAction(): ResponseInterface
    {
        $user = $this->requireLoggedInUser();
        if ($user === null) {
            return $this->redirect('index', 'Login');
        }

        if ($this->request->getMethod() === 'POST') {
            $body = $this->request->getParsedBody()['tx_account_profile'] ?? [];

            $newsletterOptin = !empty($body['newsletterOptin']);
            $remindersOptin = !empty($body['remindersOptin']);

            $this->profileService->updateNewsletterOptin($user, $newsletterOptin);
            $this->profileService->updateRemindersOptin($user, $remindersOptin);

            $this->view->assign('updated', true);
        }

        $this->view->assign('user', $user);

        return $this->htmlResponse();
    }

    /**
     * Update basic profile data (name, address, etc.).
     */
    public function updateProfileAction(): ResponseInterface
    {
        $user = $this->requireLoggedInUser();
        if ($user === null) {
            return $this->redirect('index', 'Login');
        }

        if ($this->request->getMethod() === 'POST') {
            $body = $this->request->getParsedBody()['tx_account_profile'] ?? [];

            $allowedFields = ['firstName', 'lastName', 'telephone', 'address', 'zip', 'city'];
            $data = array_intersect_key($body, array_flip($allowedFields));

            $this->profileService->updateProfile($user, array_map('trim', $data));
            $this->view->assign('updated', true);
        }

        $this->view->assign('user', $user);

        return $this->htmlResponse();
    }

    private function requireLoggedInUser(): ?FrontendUser
    {
        $isLoggedIn = $this->context->getPropertyFromAspect('frontend.user', 'isLoggedIn', false);
        if (!$isLoggedIn) {
            return null;
        }

        $userId = (int)$this->context->getPropertyFromAspect('frontend.user', 'id');
        return $this->frontendUserRepository->findByUid($userId);
    }

    /**
     * Returns the configured list of available interests from TypoScript settings.
     *
     * Expected TypoScript format:
     *   plugin.tx_account.settings.interests {
     *     culture = Kultur & Kunst
     *     sports = Sport & Bewegung
     *     ...
     *   }
     *
     * @return array<string, string>
     */
    private function getAvailableInterests(): array
    {
        return (array)($this->settings['interests'] ?? []);
    }
}
