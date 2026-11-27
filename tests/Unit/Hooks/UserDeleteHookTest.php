<?php

namespace OCA\News\Tests\Unit\Hooks;

use OCA\News\Hooks\UserDeleteHook;
use OCA\News\Service\FeedServiceV2;
use OCA\News\Service\FolderServiceV2;
use OCA\News\Service\ItemServiceV2;
use OCP\EventDispatcher\Event;
use OCP\IUser;
use OCP\User\Events\BeforeUserDeletedEvent;
use PHPUnit\Framework\TestCase;

class UserDeleteHookTest extends TestCase
{
    public function testDeletesUserDataInOrder(): void
    {
        $calls = [];
        $items = $this->createMock(ItemServiceV2::class);
        $feeds = $this->createMock(FeedServiceV2::class);
        $folders = $this->createMock(FolderServiceV2::class);
        foreach (['items' => $items, 'feeds' => $feeds, 'folders' => $folders] as $name => $service) {
            $service->expects($this->once())->method('deleteUser')->with('test-user')
                ->willReturnCallback(function () use (&$calls, $name): void {
                    $calls[] = $name;
                });
        }
        $user = $this->createMock(IUser::class);
        $user->method('getUID')->willReturn('test-user');

        (new UserDeleteHook($items, $feeds, $folders))->handle(new BeforeUserDeletedEvent($user));

        $this->assertSame(['items', 'feeds', 'folders'], $calls);
    }

    public function testIgnoresOtherEvents(): void
    {
        $items = $this->createMock(ItemServiceV2::class);
        $feeds = $this->createMock(FeedServiceV2::class);
        $folders = $this->createMock(FolderServiceV2::class);
        foreach ([$items, $feeds, $folders] as $service) {
            $service->expects($this->never())->method('deleteUser');
        }

        (new UserDeleteHook($items, $feeds, $folders))->handle(new Event());
    }
}
