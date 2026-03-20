<?php

declare(strict_types=1);

namespace Maispace\MaiAccount\Tests\Unit\Service;

use Maispace\MaiAccount\Domain\Model\FrontendUser;
use Maispace\MaiAccount\Domain\Repository\FrontendUserRepository;
use Maispace\MaiAccount\Event\EventRegisteredEvent;
use Maispace\MaiAccount\Service\ReminderService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;

class ReminderServiceTest extends TestCase
{
    private FrontendUserRepository&MockObject $userRepository;
    private ConnectionPool&MockObject $connectionPool;
    private ReminderService $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = $this->createMock(FrontendUserRepository::class);
        $this->connectionPool = $this->createMock(ConnectionPool::class);

        $this->subject = new ReminderService(
            $this->userRepository,
            $this->connectionPool,
        );
    }

    public function testOnEventRegisteredSkipsUserWithoutRemindersOptin(): void
    {
        $user = $this->createMock(FrontendUser::class);
        $user->method('isRemindersOptin')->willReturn(false);

        $this->userRepository->method('findByUid')->willReturn($user);

        $this->connectionPool->expects(self::never())->method('getConnectionForTable');

        $event = new EventRegisteredEvent(
            feUserUid: 1,
            feUserEmail: 'test@example.com',
            eventUid: 10,
            eventTitle: 'Test Event',
            eventDate: time() + 86400 * 7,
            eventLocation: 'Berlin',
        );

        $this->subject->onEventRegistered($event);
    }

    public function testOnEventRegisteredSkipsWhenUserNotFound(): void
    {
        $this->userRepository->method('findByUid')->willReturn(null);
        $this->connectionPool->expects(self::never())->method('getConnectionForTable');

        $event = new EventRegisteredEvent(
            feUserUid: 999,
            feUserEmail: 'ghost@example.com',
            eventUid: 10,
            eventTitle: 'Test Event',
            eventDate: time() + 86400 * 7,
        );

        $this->subject->onEventRegistered($event);
    }

    public function testOnEventRegisteredSkipsEventInThePast(): void
    {
        $user = $this->createMock(FrontendUser::class);
        $user->method('isRemindersOptin')->willReturn(true);

        $this->userRepository->method('findByUid')->willReturn($user);
        $this->connectionPool->expects(self::never())->method('getConnectionForTable');

        // Event date in the past – reminder time would already have passed
        $event = new EventRegisteredEvent(
            feUserUid: 1,
            feUserEmail: 'test@example.com',
            eventUid: 10,
            eventTitle: 'Past Event',
            eventDate: time() - 3600,
        );

        $this->subject->onEventRegistered($event);
    }

    public function testOnEventRegisteredInsertsReminderForOptInUser(): void
    {
        $user = $this->createMock(FrontendUser::class);
        $user->method('isRemindersOptin')->willReturn(true);

        $this->userRepository->method('findByUid')->willReturn($user);

        $connection = $this->createMock(Connection::class);
        $connection->expects(self::once())->method('insert')->with(
            'tx_maiaccount_reminder_queue',
            self::arrayHasKey('event_uid')
        );

        $this->connectionPool->method('getConnectionForTable')->willReturn($connection);

        $event = new EventRegisteredEvent(
            feUserUid: 1,
            feUserEmail: 'test@example.com',
            eventUid: 42,
            eventTitle: 'Future Event',
            eventDate: time() + 86400 * 7, // 7 days from now
            eventLocation: 'Hamburg',
        );

        $this->subject->onEventRegistered($event);
    }
}
