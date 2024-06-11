<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Alessandro Cosentino <cosenal@gmail.com>
 * @author    Bernhard Posselt <dev@bernhard-posselt.com>
 * @author    David Guillot <david@guillot.me>
 * @copyright 2012 Alessandro Cosentino
 * @copyright 2012-2014 Bernhard Posselt
 * @copyright 2018 David Guillot
 */

namespace OCA\News\Tests\Unit\Controller;

use OCA\News\Controller\ItemApiController;
use OCA\News\Service\ItemServiceV2;
use \OCP\AppFramework\Http;

use \OCA\News\Service\Exceptions\ServiceNotFoundException;
use \OCA\News\Db\Item;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;

use PHPUnit\Framework\TestCase;

class ItemApiControllerTest extends TestCase
{
    /**
     * @var ItemServiceV2|\PHPUnit\Framework\MockObject\MockObject
     */
    private $itemService;
    /**
     * @var IUserSession|\PHPUnit\Framework\MockObject\MockObject
     */
    private $userSession;
    /**
     * @var IUser|\PHPUnit\Framework\MockObject\MockObject
     */
    private $user;
    /**
     * @var IRequest|\PHPUnit\Framework\MockObject\MockObject
     */
    private $request;
    private $msg;
    private $uid = 'tom';
    private $appName;
    private $class;

    protected function setUp(): void
    {
        $this->appName = 'news';
        $this->request = $this->getMockBuilder(IRequest::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->userSession = $this->getMockBuilder(IUserSession::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->user = $this->getMockBuilder(IUser::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->userSession->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($this->user));
        $this->user->expects($this->any())
            ->method('getUID')
            ->will($this->returnValue($this->uid));
        $this->itemService = $this->getMockBuilder(ItemServiceV2::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->class = new ItemApiController(
            $this->request,
            $this->userSession,
            $this->itemService
        );
        $this->msg = 'hi';
    }


    public function testIndexForFeed()
    {
        $item = new Item();
        $item->setId(5);
        $item->setGuid('guid');
        $item->setGuidHash('guidhash');
        $item->setFeedId(123);

        $this->itemService->expects($this->once())
            ->method('findAllInFeedWithFilters')
            ->with($this->uid, 2, 30, 20, false, true)
            ->will($this->returnValue([$item]));

        $response = $this->class->index(0, 2, true, 30, 20, true);

        $this->assertEquals(['items' => [$item->toApi()]], $response);
    }


    public function testIndexForFolder()
    {
        $item = new Item();
        $item->setId(5);
        $item->setGuid('guid');
        $item->setGuidHash('guidhash');
        $item->setFeedId(123);

        $this->itemService->expects($this->once())
            ->method('findAllInFolderWithFilters')
            ->with($this->uid, 2, 30, 20, false, true)
            ->will($this->returnValue([$item]));

        $response = $this->class->index(1, 2, true, 30, 20, true);

        $this->assertEquals(['items' => [$item->toApi()]], $response);
    }


    public function testIndexForItems()
    {
        $item = new Item();
        $item->setId(5);
        $item->setGuid('guid');
        $item->setGuidHash('guidhash');
        $item->setFeedId(123);

        $this->itemService->expects($this->once())
            ->method('findAllWithFilters')
            ->with($this->uid, 3, 30, 20, true)
            ->will($this->returnValue([$item]));

        $response = $this->class->index(3, 2, true, 30, 20, true);

        $this->assertEquals(['items' => [$item->toApi()]], $response);
    }


    public function testIndexDefaultBatchSize()
    {
        $item = new Item();
        $item->setId(5);
        $item->setGuid('guid');
        $item->setGuidHash('guidhash');
        $item->setFeedId(123);

        $this->itemService->expects($this->once())
            ->method('findAllInFolderWithFilters')
            ->with($this->uid, 2, -1, 0, true, false)
            ->will($this->returnValue([$item]));

        $response = $this->class->index(1, 2, false);

        $this->assertEquals(['items' => [$item->toApi()]], $response);
    }


    public function testIndexListensToGetReadOnAllItems()
    {
        $item = new Item();
        $item->setId(5);
        $item->setGuid('guid');
        $item->setGuidHash('guidhash');
        $item->setFeedId(123);

        $this->itemService->expects($this->once())
            ->method('findAllWithFilters')
            ->with($this->uid, 6, -1, 0, false, [])
            ->will($this->returnValue([$item]));

        $response = $this->class->index(3, 0, false);

        $this->assertEquals(['items' => [$item->toApi()]], $response);
    }


    public function testIndexListensToGetReadOnItems()
    {
        $item = new Item();
        $item->setId(5);
        $item->setGuid('guid');
        $item->setGuidHash('guidhash');
        $item->setFeedId(123);

        $this->itemService->expects($this->never())
            ->method('findAllWithFilters');

        $response = $this->class->index(6, 0, false);

        $this->assertEquals(['message' => 'Setting getRead on an already filtered list is not allowed!'], $response);
    }


    public function testUpdatedFeed()
    {
        $item = new Item();
        $item->setId(5);
        $item->setGuid('guid');
        $item->setGuidHash('guidhash');
        $item->setFeedId(123);

        $this->itemService->expects($this->once())
            ->method('findAllInFeedAfter')
            ->with($this->uid, 2, 30000000, false)
            ->will($this->returnValue([$item]));

        $response = $this->class->updated(0, 2, 30);

        $this->assertEquals(['items' => [$item->toApi()]], $response);
    }


    public function testUpdatedFolder()
    {
        $item = new Item();
        $item->setId(5);
        $item->setGuid('guid');
        $item->setGuidHash('guidhash');
        $item->setFeedId(123);

        $this->itemService->expects($this->once())
            ->method('findAllInFolderAfter')
            ->with($this->uid, 2, 30000000, false)
            ->will($this->returnValue([$item]));

        $response = $this->class->updated(1, 2, 30);

        $this->assertEquals(['items' => [$item->toApi()]], $response);
    }


    public function testUpdatedItems()
    {
        $item = new Item();
        $item->setId(5);
        $item->setGuid('guid');
        $item->setGuidHash('guidhash');
        $item->setFeedId(123);

        $this->itemService->expects($this->once())
            ->method('findAllAfter')
            ->with($this->uid, 3, 30000000)
            ->will($this->returnValue([$item]));

        $response = $this->class->updated(3, 2, 30);

        $this->assertEquals(['items' => [$item->toApi()]], $response);
    }

    public function testUpdatedFeedFullTimestamp()
    {
        $item = new Item();
        $item->setId(5);
        $item->setGuid('guid');
        $item->setGuidHash('guidhash');
        $item->setFeedId(123);

        $this->itemService->expects($this->once())
            ->method('findAllInFeedAfter')
            ->with($this->uid, 2, 1609598359000000, false)
            ->will($this->returnValue([$item]));

        $response = $this->class->updated(0, 2, '1609598359000000');

        $this->assertEquals(['items' => [$item->toApi()]], $response);
    }


    public function testRead()
    {
        $this->itemService->expects($this->once())
            ->method('read')
            ->with($this->user->getUID(), 2, true);

        $this->class->read(2);
    }


    public function testReadDoesNotExist()
    {
        $this->itemService->expects($this->once())
            ->method('read')
            ->will(
                $this->throwException(
                    new ServiceNotFoundException($this->msg)
                )
            );

        $response = $this->class->read(2);

        $data = $response->getData();
        $this->assertEquals($this->msg, $data['message']);
        $this->assertEquals(Http::STATUS_NOT_FOUND, $response->getStatus());
    }


    public function testUnread()
    {
        $this->itemService->expects($this->once())
            ->method('read')
            ->with(
                $this->equalTo($this->user->getUID()),
                $this->equalTo(2),
                $this->equalTo(false)
            );

        $this->class->unread(2);
    }


    public function testUnreadDoesNotExist()
    {
        $this->itemService->expects($this->once())
            ->method('read')
            ->will(
                $this->throwException(
                    new ServiceNotFoundException($this->msg)
                )
            );

        $response = $this->class->unread(2);

        $data = $response->getData();
        $this->assertEquals($this->msg, $data['message']);
        $this->assertEquals(Http::STATUS_NOT_FOUND, $response->getStatus());
    }


    public function testStar()
    {
        $this->itemService->expects($this->once())
            ->method('starByGuid')
            ->with('tom', 2, 'hash', true);

        $this->class->star(2, 'hash');
    }


    public function testStarDoesNotExist()
    {
        $this->itemService->expects($this->once())
            ->method('starByGuid')
            ->will($this->throwException(new ServiceNotFoundException($this->msg)));

        $response = $this->class->star(2, 'test');

        $data = $response->getData();
        $this->assertEquals($this->msg, $data['message']);
        $this->assertEquals(Http::STATUS_NOT_FOUND, $response->getStatus());
    }


    public function testUnstar()
    {
        $this->itemService->expects($this->once())
            ->method('starByGuid')
            ->with($this->uid, 2, 'hash', false);

        $this->class->unstar(2, 'hash');
    }


    public function testUnstarDoesNotExist()
    {
        $this->itemService->expects($this->once())
            ->method('starByGuid')
            ->will(
                $this->throwException(
                    new ServiceNotFoundException($this->msg)
                )
            );

        $response = $this->class->unstar(2, 'test');

        $data = $response->getData();
        $this->assertEquals($this->msg, $data['message']);
        $this->assertEquals(Http::STATUS_NOT_FOUND, $response->getStatus());
    }


    public function testReadAll()
    {
        $this->itemService->expects($this->once())
            ->method('readAll')
            ->with($this->user->getUID(), 30);

        $this->class->readAll(30);
    }



    public function testReadMultiple()
    {
        $this->itemService->expects($this->exactly(2))
            ->method('read')
            ->withConsecutive(
                [$this->user->getUID(), 2, true],
                [$this->user->getUID(), 4, true]
            );
        $this->class->readMultipleByIds([2, 4]);
    }


    public function testReadMultipleDoesntCareAboutException()
    {
        $this->itemService->expects($this->exactly(2))
            ->method('read')
            ->withConsecutive(
                [$this->user->getUID(), 2, true],
                [$this->user->getUID(), 4, true]
            )
            ->willReturnOnConsecutiveCalls($this->throwException(new ServiceNotFoundException('')), new Item());
        $this->class->readMultipleByIds([2, 4]);
    }


    public function testUnreadMultiple()
    {
        $this->itemService->expects($this->exactly(2))
            ->method('read')
            ->withConsecutive(
                [$this->user->getUID(), 2, false],
                [$this->user->getUID(), 4, false]
            );
        $this->class->unreadMultipleByIds([2, 4]);
    }


    public function testStarMultiple()
    {
        $ids = [
                    [
                        'feedId' => 2,
                        'guidHash' => 'a'
                    ],
                    [
                        'feedId' => 4,
                        'guidHash' => 'b'
                    ]
                ];

        $this->itemService->expects($this->exactly(2))
            ->method('starByGuid')
            ->withConsecutive(
                [$this->user->getUID(), 2, 'a', true],
                [$this->user->getUID(), 4, 'b', true]
            );
        $this->class->starMultiple($ids);
    }


    public function testStarMultipleDoesntCareAboutException()
    {
        $ids = [
                    [
                        'feedId' => 2,
                        'guidHash' => 'a'
                    ],
                    [
                        'feedId' => 4,
                        'guidHash' => 'b'
                    ]
                ];

        $this->itemService->expects($this->exactly(2))
            ->method('starByGuid')
            ->withConsecutive(
                [$this->user->getUID(), 2, 'a', true],
                [$this->user->getUID(), 4, 'b', true]
            )
            ->willReturnOnConsecutiveCalls($this->throwException(new ServiceNotFoundException('')), new Item());

        $this->class->starMultiple($ids);
    }


    public function testUnstarMultiple()
    {
        $ids = [
                    [
                        'feedId' => 2,
                        'guidHash' => 'a'
                    ],
                    [
                        'feedId' => 4,
                        'guidHash' => 'b'
                    ]
                ];

        $this->itemService->expects($this->exactly(2))
            ->method('starByGuid')
            ->withConsecutive(
                [$this->user->getUID(), 2, 'a', false],
                [$this->user->getUID(), 4, 'b', false]
            );

        $this->class->unstarMultiple($ids);
    }


    public function testStarByItemId()
    {
        $this->itemService->expects($this->once())
            ->method('star')
            ->with($this->uid, 123, true);

        $this->class->starByItemId(123);
    }


    public function testUnstarByItemId()
    {
        $this->itemService->expects($this->once())
            ->method('star')
            ->with($this->uid, 123, false);

        $this->class->unstarByItemId(123);
    }


    public function testStarMultipleByItemIds()
    {
        $ids = [ 345, 678 ];

        $this->itemService->expects($this->exactly(2))
            ->method('star')
            ->withConsecutive(
                [$this->user->getUID(), 345, true],
                [$this->user->getUID(), 678, true]
            );
        $this->class->starMultipleByItemIds($ids);
    }


    public function testUnstarMultipleByItemIds()
    {
        $ids = [ 345, 678 ];

        $this->itemService->expects($this->exactly(2))
            ->method('star')
            ->withConsecutive(
                [$this->user->getUID(), 345, false],
                [$this->user->getUID(), 678, false]
            );

        $this->class->unstarMultipleByItemIds($ids);
    }
}
