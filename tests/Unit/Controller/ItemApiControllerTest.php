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
use OCA\News\Service\ItemService;
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

    private $itemService;
    private $oldItemService;
    private $class;
    private $userSession;
    private $user;
    private $request;
    private $msg;

    protected function setUp(): void
    {
        $this->user = 'tom';
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
            ->will($this->returnValue('123'));
        $this->oldItemService = $this->getMockBuilder(ItemService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemService = $this->getMockBuilder(ItemServiceV2::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->class = new ItemApiController(
            $this->request,
            $this->userSession,
            $this->oldItemService,
            $this->itemService
        );
        $this->msg = 'hi';
    }


    public function testIndex()
    {
        $item = new Item();
        $item->setId(5);
        $item->setGuid('guid');
        $item->setGuidHash('guidhash');
        $item->setFeedId(123);

        $this->oldItemService->expects($this->once())
            ->method('findAllItems')
            ->with(
                $this->equalTo(2),
                $this->equalTo(1),
                $this->equalTo(30),
                $this->equalTo(20),
                $this->equalTo(true),
                $this->equalTo(true),
                $this->equalTo($this->user->getUID())
            )
            ->will($this->returnValue([$item]));

        $response = $this->class->index(1, 2, true, 30, 20, true);

        $this->assertEquals(
            [
            'items' => [$item->toApi()]
            ], $response
        );
    }


    public function testIndexDefaultBatchSize()
    {
        $item = new Item();
        $item->setId(5);
        $item->setGuid('guid');
        $item->setGuidHash('guidhash');
        $item->setFeedId(123);

        $this->oldItemService->expects($this->once())
            ->method('findAllItems')
            ->with(
                $this->equalTo(2),
                $this->equalTo(1),
                $this->equalTo(-1),
                $this->equalTo(0),
                $this->equalTo(false),
                $this->equalTo(false),
                $this->equalTo($this->user->getUID())
            )
            ->will($this->returnValue([$item]));

        $response = $this->class->index(1, 2, false);

        $this->assertEquals(
            [
            'items' => [$item->toApi()]
            ], $response
        );
    }


    public function testUpdated()
    {
        $item = new Item();
        $item->setId(5);
        $item->setGuid('guid');
        $item->setGuidHash('guidhash');
        $item->setFeedId(123);

        $this->oldItemService->expects($this->once())
            ->method('findAllNew')
            ->with(
                $this->equalTo(2),
                $this->equalTo(1),
                $this->equalTo(30000000),
                $this->equalTo(true),
                $this->equalTo($this->user->getUID())
            )
            ->will($this->returnValue([$item]));

        $response = $this->class->updated(1, 2, 30);

        $this->assertEquals(
            [
            'items' => [$item->toApi()]
            ], $response
        );
    }


    public function testRead()
    {
        $this->oldItemService->expects($this->once())
            ->method('read')
            ->with(
                $this->equalTo(2),
                $this->equalTo(true),
                $this->equalTo($this->user->getUID())
            );

        $this->class->read(2);
    }


    public function testReadDoesNotExist()
    {
        $this->oldItemService->expects($this->once())
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
        $this->oldItemService->expects($this->once())
            ->method('read')
            ->with(
                $this->equalTo(2),
                $this->equalTo(false),
                $this->equalTo($this->user->getUID())
            );

        $this->class->unread(2);
    }


    public function testUnreadDoesNotExist()
    {
        $this->oldItemService->expects($this->once())
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
        $this->oldItemService->expects($this->once())
            ->method('star')
            ->with(
                $this->equalTo(2),
                $this->equalTo('hash'),
                $this->equalTo(true),
                $this->equalTo($this->user->getUID())
            );

        $this->class->star(2, 'hash');
    }


    public function testStarDoesNotExist()
    {
        $this->oldItemService->expects($this->once())
            ->method('star')
            ->will(
                $this->throwException(
                    new ServiceNotFoundException($this->msg)
                )
            );

        $response = $this->class->star(2, 'test');

        $data = $response->getData();
        $this->assertEquals($this->msg, $data['message']);
        $this->assertEquals(Http::STATUS_NOT_FOUND, $response->getStatus());
    }


    public function testUnstar()
    {
        $this->oldItemService->expects($this->once())
            ->method('star')
            ->with(
                $this->equalTo(2),
                $this->equalTo('hash'),
                $this->equalTo(false),
                $this->equalTo($this->user->getUID())
            );

        $this->class->unstar(2, 'hash');
    }


    public function testUnstarDoesNotExist()
    {
        $this->oldItemService->expects($this->once())
            ->method('star')
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
        $this->oldItemService->expects($this->once())
            ->method('readAll')
            ->with(
                $this->equalTo(30),
                $this->equalTo($this->user->getUID())
            );

        $this->class->readAll(30);
    }



    public function testReadMultiple()
    {
        $this->oldItemService->expects($this->exactly(2))
            ->method('read')
            ->withConsecutive(
                [2, true, $this->user->getUID()],
                [4, true, $this->user->getUID()]
            );
        $this->class->readMultiple([2, 4]);
    }


    public function testReadMultipleDoesntCareAboutException()
    {
        $this->oldItemService->expects($this->exactly(2))
            ->method('read')
            ->withConsecutive(
                [2, true, $this->user->getUID()],
                [4, true, $this->user->getUID()]
            )
            ->willReturnOnConsecutiveCalls($this->throwException(new ServiceNotFoundException('')), null);
        $this->class->readMultiple([2, 4]);
    }


    public function testUnreadMultiple()
    {
        $this->oldItemService->expects($this->exactly(2))
            ->method('read')
            ->withConsecutive(
                [2, false, $this->user->getUID()],
                [4, false, $this->user->getUID()]
            );
        $this->class->unreadMultiple([2, 4]);
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

        $this->oldItemService->expects($this->exactly(2))
            ->method('star')
            ->withConsecutive(
                [2, 'a', true, $this->user->getUID()],
                [4, 'b', true, $this->user->getUID()]
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

        $this->oldItemService->expects($this->exactly(2))
            ->method('star')
            ->withConsecutive(
                [2, 'a', true, $this->user->getUID()],
                [4, 'b', true, $this->user->getUID()]
            )
            ->willReturnOnConsecutiveCalls($this->throwException(new ServiceNotFoundException('')), null);

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

        $this->oldItemService->expects($this->exactly(2))
            ->method('star')
            ->withConsecutive(
                [2, 'a', false, $this->user->getUID()],
                [4, 'b', false, $this->user->getUID()]
            );

        $this->class->unstarMultiple($ids);
    }


}
