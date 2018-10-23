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
use \OCP\AppFramework\Http;

use \OCA\News\Service\ServiceNotFoundException;
use \OCA\News\Db\Item;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;

use PHPUnit\Framework\TestCase;


class ItemApiControllerTest extends TestCase
{

    private $itemService;
    private $itemAPI;
    private $api;
    private $userSession;
    private $user;
    private $request;
    private $msg;

    protected function setUp() 
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
        $this->itemService = $this->getMockBuilder(ItemService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemAPI = new ItemApiController(
            $this->appName,
            $this->request,
            $this->userSession,
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

        $this->itemService->expects($this->once())
            ->method('findAll')
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

        $response = $this->itemAPI->index(1, 2, true, 30, 20, true);

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

        $this->itemService->expects($this->once())
            ->method('findAll')
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

        $response = $this->itemAPI->index(1, 2, false);

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

        $this->itemService->expects($this->once())
            ->method('findAllNew')
            ->with(
                $this->equalTo(2),
                $this->equalTo(1),
                $this->equalTo('30000000'),
                $this->equalTo(true),
                $this->equalTo($this->user->getUID())
            )
            ->will($this->returnValue([$item]));

        $response = $this->itemAPI->updated(1, 2, 30);

        $this->assertEquals(
            [
            'items' => [$item->toApi()]
            ], $response
        );
    }


    public function testRead() 
    {
        $this->itemService->expects($this->once())
            ->method('read')
            ->with(
                $this->equalTo(2),
                $this->equalTo(true),
                $this->equalTo($this->user->getUID())
            );

        $this->itemAPI->read(2);
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

        $response = $this->itemAPI->read(2);

        $data = $response->getData();
        $this->assertEquals($this->msg, $data['message']);
        $this->assertEquals(Http::STATUS_NOT_FOUND, $response->getStatus());
    }


    public function testUnread() 
    {
        $this->itemService->expects($this->once())
            ->method('read')
            ->with(
                $this->equalTo(2),
                $this->equalTo(false),
                $this->equalTo($this->user->getUID())
            );

        $this->itemAPI->unread(2);
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

        $response = $this->itemAPI->unread(2);

        $data = $response->getData();
        $this->assertEquals($this->msg, $data['message']);
        $this->assertEquals(Http::STATUS_NOT_FOUND, $response->getStatus());
    }


    public function testStar() 
    {
        $this->itemService->expects($this->once())
            ->method('star')
            ->with(
                $this->equalTo(2),
                $this->equalTo('hash'),
                $this->equalTo(true),
                $this->equalTo($this->user->getUID())
            );

        $this->itemAPI->star(2, 'hash');
    }


    public function testStarDoesNotExist() 
    {
        $this->itemService->expects($this->once())
            ->method('star')
            ->will(
                $this->throwException(
                    new ServiceNotFoundException($this->msg)
                )
            );

        $response = $this->itemAPI->star(2, 'test');

        $data = $response->getData();
        $this->assertEquals($this->msg, $data['message']);
        $this->assertEquals(Http::STATUS_NOT_FOUND, $response->getStatus());
    }


    public function testUnstar() 
    {
        $this->itemService->expects($this->once())
            ->method('star')
            ->with(
                $this->equalTo(2),
                $this->equalTo('hash'),
                $this->equalTo(false),
                $this->equalTo($this->user->getUID())
            );

        $this->itemAPI->unstar(2, 'hash');
    }


    public function testUnstarDoesNotExist() 
    {
        $this->itemService->expects($this->once())
            ->method('star')
            ->will(
                $this->throwException(
                    new ServiceNotFoundException($this->msg)
                )
            );

        $response = $this->itemAPI->unstar(2, 'test');

        $data = $response->getData();
        $this->assertEquals($this->msg, $data['message']);
        $this->assertEquals(Http::STATUS_NOT_FOUND, $response->getStatus());
    }


    public function testReadAll() 
    {
        $this->itemService->expects($this->once())
            ->method('readAll')
            ->with(
                $this->equalTo(30),
                $this->equalTo($this->user->getUID())
            );

        $this->itemAPI->readAll(30);
    }



    public function testReadMultiple() 
    {
        $this->itemService->expects($this->at(0))
            ->method('read')
            ->with(
                $this->equalTo(2),
                $this->equalTo(true),
                $this->equalTo($this->user->getUID())
            );
        $this->itemService->expects($this->at(1))
            ->method('read')
            ->with(
                $this->equalTo(4),
                $this->equalTo(true),
                $this->equalTo($this->user->getUID())
            );
        $this->itemAPI->readMultiple([2, 4]);
    }


    public function testReadMultipleDoesntCareAboutException() 
    {
        $this->itemService->expects($this->at(0))
            ->method('read')
            ->will($this->throwException(new ServiceNotFoundException('')));
        $this->itemService->expects($this->at(1))
            ->method('read')
            ->with(
                $this->equalTo(4),
                $this->equalTo(true),
                $this->equalTo($this->user->getUID())
            );
        $this->itemAPI->readMultiple([2, 4]);
    }


    public function testUnreadMultiple() 
    {
        $this->itemService->expects($this->at(0))
            ->method('read')
            ->with(
                $this->equalTo(2),
                $this->equalTo(false),
                $this->equalTo($this->user->getUID())
            );
        $this->itemService->expects($this->at(1))
            ->method('read')
            ->with(
                $this->equalTo(4),
                $this->equalTo(false),
                $this->equalTo($this->user->getUID())
            );
        $this->itemAPI->unreadMultiple([2, 4]);
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

        $this->itemService->expects($this->at(0))
            ->method('star')
            ->with(
                $this->equalTo(2),
                $this->equalTo('a'),
                $this->equalTo(true),
                $this->equalTo($this->user->getUID())
            );
        $this->itemService->expects($this->at(1))
            ->method('star')
            ->with(
                $this->equalTo(4),
                $this->equalTo('b'),
                $this->equalTo(true),
                $this->equalTo($this->user->getUID())
            );
        $this->itemAPI->starMultiple($ids);
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

        $this->itemService->expects($this->at(0))
            ->method('star')
            ->will($this->throwException(new ServiceNotFoundException('')));
        $this->itemService->expects($this->at(1))
            ->method('star')
            ->with(
                $this->equalTo(4),
                $this->equalTo('b'),
                $this->equalTo(true),
                $this->equalTo($this->user->getUID())
            );
        $this->itemAPI->starMultiple($ids);
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

        $this->itemService->expects($this->at(0))
            ->method('star')
            ->with(
                $this->equalTo(2),
                $this->equalTo('a'),
                $this->equalTo(false),
                $this->equalTo($this->user->getUID())
            );
        $this->itemService->expects($this->at(1))
            ->method('star')
            ->with(
                $this->equalTo(4),
                $this->equalTo('b'),
                $this->equalTo(false),
                $this->equalTo($this->user->getUID())
            );
        $this->itemAPI->unstarMultiple($ids);
    }


}
