<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Controller;

use Maispace\MaiAccount\Domain\Model\FrontendUser;
use Maispace\MaiAccount\Domain\Model\Reminder;
use Maispace\MaiAccount\Domain\Repository\FrontendUserRepository;
use Maispace\MaiAccount\Domain\Repository\InterestRepository;
use Maispace\MaiAccount\Domain\Repository\ReminderRepository;
use Maispace\MaiAccount\Service\AccountMailer;
use Maispace\MaiAccount\Service\RegistrationService;
use Maispace\MaiAccount\Support\LoginFormSupport;
use Maispace\MaiBase\Controller\AbstractActionController;
use Maispace\MaiBase\Controller\Traits\FlashMessageTrait;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use TYPO3\CMS\Core\Security\RequestToken;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

class AccountController extends AbstractActionController
{
    use FlashMessageTrait;

    public function __construct(
        private readonly FrontendUserRepository $frontendUserRepository,
        private readonly InterestRepository $interestRepository,
        private readonly ReminderRepository $reminderRepository,
        private readonly RegistrationService $registrationService,
        private readonly AccountMailer $accountMailer,
        private readonly PasswordHashFactory $passwordHashFactory,
        private readonly ConnectionPool $connectionPool,
        private readonly PersistenceManagerInterface $persistenceManager,
        private readonly SiteFinder $siteFinder,
        private readonly Context $context,
    ) {}

    public function loginAction(): ResponseInterface
    {
        $parsedBody = $this->request->getParsedBody() ?? [];
        $queryParams = $this->request->getQueryParams();
        $loginType = LoginFormSupport::resolveLoginType($parsedBody, $queryParams);
        $isLoggedIn = $this->context->getAspect('frontend.user')->isLoggedIn();

        if (LoginFormSupport::isFreshLoginSuccess($loginType, $isLoggedIn)) {
            $redirectPid = (int) ($this->settings['loginRedirectPid'] ?? 0);
            if ($redirectPid > 0) {
                return $this->redirect(null, null, null, [], $redirectPid);
            }
        }

        if ($isLoggedIn) {
            $this->view->assign('isLoggedIn', true);

            return $this->htmlResponse();
        }

        $this->view->assignMultiple([
            'loginError' => LoginFormSupport::hasLoginFailed($loginType, $isLoggedIn),
            'requestToken' => $this->createLoginRequestToken(),
        ]);

        return $this->htmlResponse();
    }

    public function logoutAction(): ResponseInterface
    {
        $feUser = $GLOBALS['TSFE']->fe_user ?? null;

        if ($feUser instanceof FrontendUserAuthentication) {
            $feUser->logoff();
        }

        $redirectPid = (int) ($this->settings['logoutRedirectPid'] ?? 0);
        if ($redirectPid > 0) {
            return $this->redirect(null, null, null, [], $redirectPid);
        }

        return $this->htmlResponse();
    }

    public function registerAction(
        string $username = '',
        string $email = '',
        string $password = '',
        string $firstName = '',
        string $lastName = '',
    ): ResponseInterface {
        if ($this->request->getMethod() !== 'POST') {
            return $this->htmlResponse();
        }

        $username = trim($username);
        $email = trim($email);
        $firstName = trim($firstName);
        $lastName = trim($lastName);

        if ($username === '' || $email === '' || $password === '') {
            $this->flashError('Username, email, and password are required.');
            return $this->redirect('register');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flashError('Please enter a valid email address.');
            return $this->redirect('register');
        }

        if (!$this->registrationService->isUsernameAvailable($username)) {
            $this->flashError('Username is already in use.');
            return $this->redirect('register');
        }

        if (!$this->registrationService->isEmailAvailable($email)) {
            $this->flashError('Email is already in use.');
            return $this->redirect('register');
        }

        $storagePid = (int) ($this->settings['registerStoragePid'] ?? ($this->settings['persistence']['storagePid'] ?? 0));
        $result = $this->registrationService->register($username, $email, $password, $firstName, $lastName, $storagePid);

        $confirmPid = (int) ($this->settings['registerConfirmPid'] ?? 0);
        $builder = $this->uriBuilder->reset()->setCreateAbsoluteUri(true);
        if ($confirmPid > 0) {
            $builder->setTargetPageUid($confirmPid);
        }
        $confirmUrl = (string) $builder->uriFor(
            'confirm',
            ['token' => $result['token']],
            'Account',
            'MaiAccount',
            'Account',
        );

        $this->accountMailer->sendRegistrationConfirmation($email, $firstName, $confirmUrl);

        $this->flashSuccess('Registration received. Please check your inbox to confirm your account.');

        return $this->redirect('login');
    }

    public function confirmAction(string $token = ''): ResponseInterface
    {
        $success = $this->registrationService->confirm($token);

        $this->view->assign('success', $success);

        return $this->htmlResponse();
    }

    public function profileAction(): ResponseInterface
    {
        $feUserUid = (int) $this->context->getPropertyFromAspect('frontend.user', 'id');

        if ($feUserUid === 0) {
            return $this->redirectToLogin();
        }

        $user = $this->frontendUserRepository->findByUid($feUserUid);

        $this->view->assignMultiple([
            'user' => $user,
            'memberAvailable' => ExtensionManagementUtility::isLoaded('mai_member'),
        ]);

        return $this->htmlResponse();
    }

    public function changePasswordAction(
        string $currentPassword,
        string $newPassword,
        string $newPasswordConfirm,
    ): ResponseInterface {
        $feUserUid = (int) $this->context->getPropertyFromAspect('frontend.user', 'id');

        if ($feUserUid === 0) {
            return $this->redirectToLogin();
        }

        if ($newPassword === '' || $newPassword !== $newPasswordConfirm) {
            $this->flashError('Passwords do not match.');
            return $this->redirect('profile');
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('fe_users');
        $currentHash = (string) $queryBuilder
            ->select('password')
            ->from('fe_users')
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($feUserUid)))
            ->executeQuery()
            ->fetchOne();

        if ($currentHash === '') {
            $this->flashError('Current password check failed.');
            return $this->redirect('profile');
        }

        $verifyInstance = $this->passwordHashFactory->get($currentHash, 'FE');
        if (!$verifyInstance->checkPassword($currentPassword, $currentHash)) {
            $this->flashError('Current password is incorrect.');
            return $this->redirect('profile');
        }

        $hashInstance = $this->passwordHashFactory->getDefaultHashInstance('FE');
        $newHash = $hashInstance->getHashedPassword($newPassword);

        $updateQb = $this->connectionPool->getQueryBuilderForTable('fe_users');
        $updateQb
            ->update('fe_users')
            ->set('password', $newHash)
            ->set('tstamp', time())
            ->where($updateQb->expr()->eq('uid', $updateQb->createNamedParameter($feUserUid)))
            ->executeStatement();

        $this->flashSuccess('Password updated.');

        return $this->redirect('profile');
    }

    public function interestsAction(): ResponseInterface
    {
        $feUserUid = (int) $this->context->getPropertyFromAspect('frontend.user', 'id');

        if ($feUserUid === 0) {
            return $this->redirectToLogin();
        }

        $request = $this->request;
        if ($request->getMethod() === 'POST' && $request->hasArgument('selectedInterests')) {
            $selected = (array) $request->getArgument('selectedInterests');
            $this->saveInterests($feUserUid, $selected);
            $this->flashSuccess('Interests updated.');

            return $this->redirect('interests');
        }

        $user = $this->frontendUserRepository->findByUid($feUserUid);
        $allInterests = $this->interestRepository->findAll();

        $this->view->assignMultiple([
            'user' => $user,
            'allInterests' => $allInterests,
        ]);

        return $this->htmlResponse();
    }

    public function remindersAction(): ResponseInterface
    {
        $feUserUid = (int) $this->context->getPropertyFromAspect('frontend.user', 'id');

        if ($feUserUid === 0) {
            return $this->redirectToLogin();
        }

        $request = $this->request;
        if (
            $request->getMethod() === 'POST'
            && $request->hasArgument('reminderTitle')
            && $request->hasArgument('reminderAt')
        ) {
            $title = trim((string) $request->getArgument('reminderTitle'));
            $remindAtRaw = (string) $request->getArgument('reminderAt');

            if ($title !== '' && $remindAtRaw !== '') {
                $remindAt = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $remindAtRaw)
                    ?: \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $remindAtRaw);

                if ($remindAt instanceof \DateTimeImmutable) {
                    $reminder = new Reminder();
                    $reminder->setFeUser($feUserUid);
                    $reminder->setTitle($title);
                    $reminder->setRemindAt($remindAt);
                    $this->reminderRepository->add($reminder);
                    $this->persistenceManager->persistAll();
                    $this->flashSuccess('Reminder added.');
                } else {
                    $this->flashError('Invalid reminder date.');
                }
            } else {
                $this->flashError('Title and date are required.');
            }

            return $this->redirect('reminders');
        }

        $reminders = $this->reminderRepository->findByFeUser($feUserUid);

        $this->view->assign('reminders', $reminders);

        return $this->htmlResponse();
    }

    public function newsletterOptInAction(): ResponseInterface
    {
        $feUserUid = (int) $this->context->getPropertyFromAspect('frontend.user', 'id');

        if ($feUserUid === 0) {
            return $this->redirectToLogin();
        }

        $request = $this->request;
        if ($request->getMethod() === 'POST') {
            $optIn = (bool) ($request->hasArgument('optIn') ? (int) $request->getArgument('optIn') : 0);
            $this->handleNewsletterOptInToggle($feUserUid, $optIn);

            return $this->redirect('newsletterOptIn');
        }

        $user = $this->frontendUserRepository->findByUid($feUserUid);
        $this->view->assign('user', $user);
        $this->view->assign('newsletterAvailable', ExtensionManagementUtility::isLoaded('mai_newsletter'));

        return $this->htmlResponse();
    }

    private function handleNewsletterOptInToggle(int $feUserUid, bool $optIn): void
    {
        $qb = $this->connectionPool->getQueryBuilderForTable('fe_users');
        $row = $qb->select('email')
            ->from('fe_users')
            ->where($qb->expr()->eq('uid', $qb->createNamedParameter($feUserUid)))
            ->executeQuery()
            ->fetchAssociative();

        $email = (string) ($row['email'] ?? '');

        $updateQb = $this->connectionPool->getQueryBuilderForTable('fe_users');
        $updateQb
            ->update('fe_users')
            ->set('tx_maiaccount_newsletter_optin', $optIn ? 1 : 0)
            ->set('tstamp', time())
            ->where($updateQb->expr()->eq('uid', $updateQb->createNamedParameter($feUserUid)))
            ->executeStatement();

        if (!$optIn || $email === '' || !ExtensionManagementUtility::isLoaded('mai_newsletter')) {
            $this->flashSuccess('Newsletter preference saved.');
            return;
        }

        $subscriberServiceClass = 'Maispace\\MaiNewsletter\\Service\\SubscriberService';
        $mailerClass = 'Maispace\\MaiNewsletter\\Service\\ConfirmationMailer';

        if (!class_exists($subscriberServiceClass) || !class_exists($mailerClass)) {
            $this->flashSuccess('Newsletter preference saved.');
            return;
        }

        $storagePid = (int) ($this->settings['newsletterSubscriberStoragePid'] ?? 0);
        $siteIdentifier = $this->resolveSiteIdentifier();

        $subscriberService = GeneralUtility::makeInstance($subscriberServiceClass);
        $mailer = GeneralUtility::makeInstance($mailerClass);

        $subscriber = $subscriberService->optIn($email, $siteIdentifier, $storagePid, $feUserUid);

        if (!$subscriber->isPending()) {
            $this->flashInfo('You are already subscribed to the newsletter.');
            return;
        }

        $confirmUrl = $this->buildNewsletterUri('confirm', ['token' => $subscriber->getToken()]);
        $unsubscribeUrl = $this->buildNewsletterUri('unsubscribe', ['token' => $subscriber->getToken()]);

        $mailer->send($subscriber, $confirmUrl, $unsubscribeUrl);

        $this->flashSuccess('Please check your inbox to confirm your newsletter subscription.');
    }

    private function buildNewsletterUri(string $action, array $arguments): string
    {
        $pageUid = (int) ($this->settings['newsletterConfirmPid'] ?? 0);

        $builder = $this->uriBuilder->reset()->setCreateAbsoluteUri(true);
        if ($pageUid > 0) {
            $builder->setTargetPageUid($pageUid);
        }

        return (string) $builder->uriFor(
            $action,
            $arguments,
            'Newsletter',
            'MaiNewsletter',
            'Newsletter',
        );
    }

    private function resolveSiteIdentifier(): string
    {
        $routing = $this->request->getAttribute('routing');
        $pageUid = $routing?->getPageId() ?? 0;

        if ($pageUid > 0) {
            try {
                return $this->siteFinder->getSiteByPageId($pageUid)->getIdentifier();
            } catch (\Throwable) {
                // Fall through.
            }
        }

        return 'default';
    }

    private function saveInterests(int $feUserUid, array $selectedInterestUids): void
    {
        $selectedInterestUids = array_values(array_unique(array_map('intval', $selectedInterestUids)));
        $selectedInterestUids = array_filter($selectedInterestUids, static fn(int $uid): bool => $uid > 0);

        $table = 'tx_maiaccount_feuser_interest_mm';
        $connection = $this->connectionPool->getConnectionForTable($table);

        $deleteQb = $this->connectionPool->getQueryBuilderForTable($table);
        $deleteQb
            ->delete($table)
            ->where($deleteQb->expr()->eq('uid_local', $deleteQb->createNamedParameter($feUserUid)))
            ->executeStatement();

        $sorting = 0;
        foreach ($selectedInterestUids as $interestUid) {
            $connection->insert($table, [
                'uid_local' => $feUserUid,
                'uid_foreign' => $interestUid,
                'sorting' => ++$sorting,
                'sorting_foreign' => 0,
            ]);
        }

        $updateQb = $this->connectionPool->getQueryBuilderForTable('fe_users');
        $updateQb
            ->update('fe_users')
            ->set('tx_maiaccount_interests', count($selectedInterestUids))
            ->set('tstamp', time())
            ->where($updateQb->expr()->eq('uid', $updateQb->createNamedParameter($feUserUid)))
            ->executeStatement();
    }

    private function redirectToLogin(): ResponseInterface
    {
        $loginPid = (int) ($this->settings['loginRedirectPid'] ?? 0);

        if ($loginPid > 0) {
            return $this->redirect(null, null, null, [], $loginPid);
        }

        return $this->htmlResponse();
    }

    private function createLoginRequestToken(): RequestToken
    {
        $storagePid = (int) (
            $this->settings['registerStoragePid']
            ?? $this->settings['persistence']['storagePid']
            ?? 0
        );

        $token = RequestToken::create('core/user-auth/fe');
        if ($storagePid > 0) {
            $token = $token->withMergedParams(['pid' => (string) $storagePid]);
        }

        return $token;
    }
}
