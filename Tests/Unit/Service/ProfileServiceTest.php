<?php

declare(strict_types=1);

namespace Maispace\Account\Tests\Unit\Service;

use Maispace\Account\Domain\Model\FrontendUser;
use Maispace\Account\Domain\Repository\FrontendUserRepository;
use Maispace\Account\Event\InterestsUpdatedEvent;
use Maispace\Account\Service\ProfileService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

class ProfileServiceTest extends TestCase
{
    private FrontendUserRepository&MockObject $userRepository;
    private PersistenceManagerInterface&MockObject $persistenceManager;
    private EventDispatcherInterface&MockObject $eventDispatcher;
    private ProfileService $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = $this->createMock(FrontendUserRepository::class);
        $this->persistenceManager = $this->createMock(PersistenceManagerInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->subject = new ProfileService(
            $this->userRepository,
            $this->persistenceManager,
            $this->eventDispatcher,
        );
    }

    public function testUpdateInterestsDispatchesEventWhenInterestsChange(): void
    {
        $user = $this->createMock(FrontendUser::class);
        $user->method('getInterests')->willReturn(['culture']);
        $user->method('getEmail')->willReturn('test@example.com');
        $user->method('getUid')->willReturn(42);

        $user->expects(self::once())->method('setInterests')->with(['sports']);

        $this->userRepository->expects(self::once())->method('update');
        $this->persistenceManager->expects(self::once())->method('persistAll');

        $this->eventDispatcher
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(InterestsUpdatedEvent::class));

        $this->subject->updateInterests($user, ['sports']);
    }

    public function testUpdateInterestsDoesNotDispatchEventWhenNothingChanges(): void
    {
        $user = $this->createMock(FrontendUser::class);
        $user->method('getInterests')->willReturn(['culture', 'sports']);
        $user->method('getEmail')->willReturn('test@example.com');
        $user->method('getUid')->willReturn(42);
        $user->method('setInterests')->with(['culture', 'sports']);

        $this->eventDispatcher
            ->expects(self::never())
            ->method('dispatch');

        $this->subject->updateInterests($user, ['sports', 'culture']);
    }

    public function testUpdateNewsletterOptinSetsOptinTrue(): void
    {
        $user = $this->createMock(FrontendUser::class);
        $user->expects(self::once())->method('setNewsletterOptin')->with(true);
        $user->method('getNewsletterOptinDate')->willReturn(0);
        $user->expects(self::once())->method('setNewsletterOptinDate')->with(self::greaterThan(0));

        $this->userRepository->expects(self::once())->method('update');
        $this->persistenceManager->expects(self::once())->method('persistAll');

        $this->subject->updateNewsletterOptin($user, true);
    }

    public function testUpdateNewsletterOptinDoesNotOverwriteExistingDate(): void
    {
        $existingTimestamp = 1700000000;

        $user = $this->createMock(FrontendUser::class);
        $user->expects(self::once())->method('setNewsletterOptin')->with(true);
        $user->method('getNewsletterOptinDate')->willReturn($existingTimestamp);
        $user->expects(self::never())->method('setNewsletterOptinDate');

        $this->userRepository->expects(self::once())->method('update');
        $this->persistenceManager->expects(self::once())->method('persistAll');

        $this->subject->updateNewsletterOptin($user, true);
    }

    public function testUpdateProfileUpdatesAllowedFields(): void
    {
        $user = $this->createMock(FrontendUser::class);
        $user->expects(self::once())->method('setFirstName')->with('Max');
        $user->expects(self::once())->method('setLastName')->with('Mustermann');
        $user->expects(self::once())->method('setCity')->with('Berlin');

        $this->userRepository->expects(self::once())->method('update');
        $this->persistenceManager->expects(self::once())->method('persistAll');

        $this->subject->updateProfile($user, [
            'firstName' => 'Max',
            'lastName' => 'Mustermann',
            'city' => 'Berlin',
            'illegalField' => 'should be ignored',
        ]);
    }

    public function testInterestsUpdatedEventHasCorrectDiff(): void
    {
        $event = new InterestsUpdatedEvent(
            feUserUid: 1,
            feUserEmail: 'test@example.com',
            newInterests: ['sports', 'culture', 'nature'],
            previousInterests: ['culture', 'technology'],
        );

        self::assertSame(['sports', 'nature'], $event->getAddedInterests());
        self::assertSame(['technology'], $event->getRemovedInterests());
    }
}
