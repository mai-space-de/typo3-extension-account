<?php

declare(strict_types=1);

namespace Maispace\Account\Service;

use Maispace\Account\Domain\Model\FrontendUser;
use Maispace\Account\Domain\Model\Interest;
use Maispace\Account\Domain\Repository\FrontendUserRepository;
use Maispace\Account\Domain\Repository\InterestRepository;
use Maispace\Account\Event\InterestsUpdatedEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

class ProfileService
{
    public function __construct(
        private readonly FrontendUserRepository $frontendUserRepository,
        private readonly InterestRepository $interestRepository,
        private readonly PersistenceManagerInterface $persistenceManager,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    /**
     * Update user profile fields.
     *
     * @param array{firstName?: string, lastName?: string, newsletterOptin?: bool, reminderEnabled?: bool} $data
     */
    public function updateProfile(FrontendUser $user, array $data): void
    {
        if (isset($data['firstName'])) {
            $user->setFirstName($data['firstName']);
        }
        if (isset($data['lastName'])) {
            $user->setLastName($data['lastName']);
        }
        if (isset($data['newsletterOptin'])) {
            $user->setNewsletterOptin((bool)$data['newsletterOptin']);
        }
        if (isset($data['reminderEnabled'])) {
            $user->setReminderEnabled((bool)$data['reminderEnabled']);
        }

        $this->frontendUserRepository->update($user);
        $this->persistenceManager->persistAll();
    }

    /**
     * Update user interests and dispatch InterestsUpdatedEvent.
     *
     * @param int[] $interestUids
     */
    public function updateInterests(FrontendUser $user, array $interestUids): void
    {
        $previousInterestUids = $this->collectInterestUids($user->getInterests());

        $newInterests = new ObjectStorage();
        foreach ($interestUids as $uid) {
            /** @var Interest|null $interest */
            $interest = $this->interestRepository->findByUid((int)$uid);
            if ($interest !== null) {
                $newInterests->attach($interest);
            }
        }

        $user->setInterests($newInterests);
        $this->frontendUserRepository->update($user);
        $this->persistenceManager->persistAll();

        $this->eventDispatcher->dispatch(
            new InterestsUpdatedEvent($user, $previousInterestUids, $interestUids)
        );
    }

    /**
     * @param ObjectStorage<Interest> $interests
     * @return int[]
     */
    private function collectInterestUids(ObjectStorage $interests): array
    {
        $uids = [];
        foreach ($interests as $interest) {
            $uids[] = $interest->getUid();
        }
        return $uids;
    }
}
