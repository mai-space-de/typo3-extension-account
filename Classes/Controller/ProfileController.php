<?php

declare(strict_types=1);

namespace Maispace\Account\Controller;

use Maispace\Account\Domain\Repository\FrontendUserRepository;
use Maispace\Account\Domain\Repository\InterestRepository;
use Maispace\Account\Service\ProfileService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class ProfileController extends ActionController
{
    public function __construct(
        private readonly ProfileService $profileService,
        private readonly FrontendUserRepository $frontendUserRepository,
        private readonly InterestRepository $interestRepository
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

    public function editAction(): ResponseInterface
    {
        $user = $this->getCurrentUser();
        if ($user === null) {
            return $this->redirect('index', 'Login');
        }
        $this->view->assign('user', $user);
        return $this->htmlResponse();
    }

    public function updateAction(): ResponseInterface
    {
        $user = $this->getCurrentUser();
        if ($user === null) {
            return $this->redirect('index', 'Login');
        }

        $arguments = $this->request->getArguments();
        $this->profileService->updateProfile($user, [
            'firstName' => $arguments['firstName'] ?? null,
            'lastName' => $arguments['lastName'] ?? null,
            'newsletterOptin' => isset($arguments['newsletterOptin']) ? (bool)$arguments['newsletterOptin'] : null,
            'reminderEnabled' => isset($arguments['reminderEnabled']) ? (bool)$arguments['reminderEnabled'] : null,
        ]);

        return $this->redirect('index');
    }

    public function interestsAction(): ResponseInterface
    {
        $user = $this->getCurrentUser();
        if ($user === null) {
            return $this->redirect('index', 'Login');
        }

        $allInterests = $this->interestRepository->findAll();
        $this->view->assign('user', $user);
        $this->view->assign('allInterests', $allInterests);
        return $this->htmlResponse();
    }

    public function updateInterestsAction(): ResponseInterface
    {
        $user = $this->getCurrentUser();
        if ($user === null) {
            return $this->redirect('index', 'Login');
        }

        $arguments = $this->request->getArguments();
        $interestUids = array_map('intval', (array)($arguments['interests'] ?? []));

        $this->profileService->updateInterests($user, $interestUids);

        return $this->redirect('interests');
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
