<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Service;

use Maispace\MaiAccount\Domain\Model\FrontendUser;
use Maispace\MaiAccount\Domain\Repository\FrontendUserRepository;
use Maispace\MaiAccount\Event\InterestsUpdatedEvent;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class ProfileService
{
    public function __construct(
        private readonly FrontendUserRepository $frontendUserRepository,
        private readonly PersistenceManagerInterface $persistenceManager,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * Update the user's interest list.
     * Dispatches InterestsUpdatedEvent so maispace/newsletter can sync subscriptions.
     *
     * @param string[] $newInterests
     */
    public function updateInterests(FrontendUser $user, array $newInterests): void
    {
        $previousInterests = $user->getInterests();

        $normalizedNew = array_values(array_unique(array_filter(array_map('trim', $newInterests))));
        sort($normalizedNew);

        $normalizedPrev = $previousInterests;
        sort($normalizedPrev);

        $user->setInterests($normalizedNew);
        $this->frontendUserRepository->update($user);
        $this->persistenceManager->persistAll();

        // Dispatch PSR-14 event if interests actually changed
        if ($normalizedNew !== $normalizedPrev) {
            $this->eventDispatcher->dispatch(
                new InterestsUpdatedEvent(
                    (int)$user->getUid(),
                    $user->getEmail(),
                    $normalizedNew,
                    $normalizedPrev,
                )
            );
        }
    }

    /**
     * Update newsletter opt-in status.
     */
    public function updateNewsletterOptin(FrontendUser $user, bool $optIn): void
    {
        $user->setNewsletterOptin($optIn);

        if ($optIn && $user->getNewsletterOptinDate() === 0) {
            $user->setNewsletterOptinDate(time());
        }

        $this->frontendUserRepository->update($user);
        $this->persistenceManager->persistAll();
    }

    /**
     * Update reminder opt-in status.
     */
    public function updateRemindersOptin(FrontendUser $user, bool $optIn): void
    {
        $user->setRemindersOptin($optIn);
        $this->frontendUserRepository->update($user);
        $this->persistenceManager->persistAll();
    }

    /**
     * Update basic profile fields (name, etc.).
     *
     * @param array<string, string> $data
     */
    public function updateProfile(FrontendUser $user, array $data): void
    {
        if (isset($data['firstName'])) {
            $user->setFirstName($data['firstName']);
        }
        if (isset($data['lastName'])) {
            $user->setLastName($data['lastName']);
        }
        if (isset($data['telephone'])) {
            $user->setTelephone($data['telephone']);
        }
        if (isset($data['address'])) {
            $user->setAddress($data['address']);
        }
        if (isset($data['zip'])) {
            $user->setZip($data['zip']);
        }
        if (isset($data['city'])) {
            $user->setCity($data['city']);
        }

        $this->frontendUserRepository->update($user);
        $this->persistenceManager->persistAll();
    }
}
