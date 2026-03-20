<?php

declare(strict_types=1);

namespace Maispace\Account\Tests\Unit\Service;

use Maispace\Account\Domain\Model\FrontendUser;
use Maispace\Account\Domain\Model\Interest;
use Maispace\Account\Domain\Repository\FrontendUserRepository;
use Maispace\Account\Domain\Repository\InterestRepository;
use Maispace\Account\Event\InterestsUpdatedEvent;
use Maispace\Account\Service\ProfileService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

class ProfileServiceTest extends TestCase
{
    private FrontendUserRepository&MockObject $userRepository;
    private InterestRepository&MockObject $interestRepository;
    private PersistenceManagerInterface&MockObject $persistenceManager;
    private EventDispatcherInterface&MockObject $eventDispatcher;
    private ProfileService $subject;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(FrontendUserRepository::class);
        $this->interestRepository = $this->createMock(InterestRepository::class);
        $this->persistenceManager = $this->createMock(PersistenceManagerInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->subject = new ProfileService(
            $this->userRepository,
            $this->interestRepository,
            $this->persistenceManager,
            $this->eventDispatcher
        );
    }

    public function testUpdateProfileUpdatesUserFields(): void
    {
        $user = new FrontendUser();
        $this->userRepository->expects(self::once())->method('update')->with($user);

        $this->subject->updateProfile($user, [
            'firstName' => 'Jane',
            'lastName' => 'Doe',
            'newsletterOptin' => true,
            'reminderEnabled' => false,
        ]);

        self::assertSame('Jane', $user->getFirstName());
        self::assertSame('Doe', $user->getLastName());
        self::assertTrue($user->isNewsletterOptin());
        self::assertFalse($user->isReminderEnabled());
    }

    public function testUpdateInterestsDispatchesEvent(): void
    {
        $user = new FrontendUser();
        $interest = new Interest();

        $this->interestRepository
            ->method('findByUid')
            ->with(42)
            ->willReturn($interest);

        $this->eventDispatcher
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(InterestsUpdatedEvent::class));

        $this->subject->updateInterests($user, [42]);
    }

    public function testUpdateInterestsWithUnknownUidSkipsInterest(): void
    {
        $user = new FrontendUser();

        $this->interestRepository
            ->method('findByUid')
            ->willReturn(null);

        $this->subject->updateInterests($user, [999]);

        self::assertCount(0, $user->getInterests());
    }
}
