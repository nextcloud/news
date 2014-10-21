<?php
/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Alessandro Cosentino <cosenal@gmail.com>
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Alessandro Cosentino 2012
 * @copyright Bernhard Posselt 2012, 2014
 */

namespace OCA\News\Controller;

use \OCP\AppFramework\Http;

use \OCA\News\Service\ServiceNotFoundException;
use \OCA\News\Db\Item;


class ItemApiControllerTest extends \PHPUnit_Framework_TestCase {

    private $itemService;
    private $itemAPI;
    private $api;
    private $user;
    private $request;
    private $msg;

    protected function setUp() {
        $this->user = 'tom';
        $this->appName = 'news';
        $this->request = $this->getMockBuilder(
            '\OCP\IRequest')
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemService = $this->getMockBuilder(
            '\OCA\News\Service\ItemService')
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemAPI = new ItemApiController(
            $this->appName,
            $this->request,
            $this->itemService,
            $this->user
        );
        $this->msg = 'hi';
    }


    public function testIndex() {
        $items = [new Item()];

        $this->itemService->expects($this->once())
            ->method('findAll')
            ->with(
                $this->equalTo(2),
                $this->equalTo(1),
                $this->equalTo(30),
                $this->equalTo(20),
                $this->equalTo(true),
                $this->equalTo(true),
                $this->equalTo($this->user)
            )
            ->will($this->returnValue($items));

        $response = $this->itemAPI->index(1, 2, true, 30, 20, true);

        $this->assertEquals([
            'items' => [$items[0]->toApi()]
        ], $response);
    }


    public function testIndexDefaultBatchSize() {
        $items = [new Item()];

        $this->itemService->expects($this->once())
            ->method('findAll')
            ->with(
                $this->equalTo(2),
                $this->equalTo(1),
                $this->equalTo(20),
                $this->equalTo(0),
                $this->equalTo(false),
                $this->equalTo(false),
                $this->equalTo($this->user)
            )
            ->will($this->returnValue($items));

        $response = $this->itemAPI->index(1, 2, false);

        $this->assertEquals([
            'items' => [$items[0]->toApi()]
        ], $response);
    }


    public function testUpdated() {
        $items = [new Item()];

        $this->itemService->expects($this->once())
            ->method('findAllNew')
            ->with(
                $this->equalTo(2),
                $this->equalTo(1),
                $this->equalTo(30),
                $this->equalTo(true),
                $this->equalTo($this->user)
            )
            ->will($this->returnValue($items));

        $response = $this->itemAPI->updated(1, 2, 30);

        $this->assertEquals([
            'items' => [$items[0]->toApi()]
        ], $response);
    }


    public function testRead() {
        $this->itemService->expects($this->once())
            ->method('read')
            ->with(
                $this->equalTo(2),
                $this->equalTo(true),
                $this->equalTo($this->user)
            );

        $this->itemAPI->read(2);
    }


    public function testReadDoesNotExist() {
        $this->itemService->expects($this->once())
            ->method('read')
            ->will($this->throwException(
                new ServiceNotFoundException($this->msg))
            );

        $response = $this->itemAPI->read(2);

        $data = $response->getData();
        $this->assertEquals($this->msg, $data['message']);
        $this->assertEquals(Http::STATUS_NOT_FOUND, $response->getStatus());
    }


    public function testUnread() {
        $this->itemService->expects($this->once())
            ->method('read')
            ->with(
                $this->equalTo(2),
                $this->equalTo(false),
                $this->equalTo($this->user)
            );

        $this->itemAPI->unread(2);
    }


    public function testUnreadDoesNotExist() {
        $this->itemService->expects($this->once())
            ->method('read')
            ->will($this->throwException(
                new ServiceNotFoundException($this->msg))
            );

        $response = $this->itemAPI->unread(2);

        $data = $response->getData();
        $this->assertEquals($this->msg, $data['message']);
        $this->assertEquals(Http::STATUS_NOT_FOUND, $response->getStatus());
    }


    public function testStar() {
        $this->itemService->expects($this->once())
            ->method('star')
            ->with(
                $this->equalTo(2),
                $this->equalTo('hash'),
                $this->equalTo(true),
                $this->equalTo($this->user)
            );

        $this->itemAPI->star(2, 'hash');
    }


    public function testStarDoesNotExist() {
        $this->itemService->expects($this->once())
            ->method('star')
            ->will($this->throwException(
                new ServiceNotFoundException($this->msg))
            );

        $response = $this->itemAPI->star(2, 'test');

        $data = $response->getData();
        $this->assertEquals($this->msg, $data['message']);
        $this->assertEquals(Http::STATUS_NOT_FOUND, $response->getStatus());
    }


    public function testUnstar() {
        $this->itemService->expects($this->once())
            ->method('star')
            ->with(
                $this->equalTo(2),
                $this->equalTo('hash'),
                $this->equalTo(false),
                $this->equalTo($this->user)
            );

        $this->itemAPI->unstar(2, 'hash');
    }


    public function testUnstarDoesNotExist() {
        $this->itemService->expects($this->once())
            ->method('star')
            ->will($this->throwException(
                new ServiceNotFoundException($this->msg))
            );

        $response = $this->itemAPI->unstar(2, 'test');

        $data = $response->getData();
        $this->assertEquals($this->msg, $data['message']);
        $this->assertEquals(Http::STATUS_NOT_FOUND, $response->getStatus());
    }


    public function testReadAll() {
        $this->itemService->expects($this->once())
            ->method('readAll')
            ->with(
                $this->equalTo(30),
                $this->equalTo($this->user));

        $this->itemAPI->readAll(30);
    }



    public function testReadMultiple() {
        $this->itemService->expects($this->at(0))
            ->method('read')
            ->with($this->equalTo(2),
                $this->equalTo(true),
                $this->equalTo($this->user));
        $this->itemService->expects($this->at(1))
            ->method('read')
            ->with($this->equalTo(4),
                $this->equalTo(true),
                $this->equalTo($this->user));
        $this->itemAPI->readMultiple([2, 4]);
    }


    public function testReadMultipleDoesntCareAboutException() {
        $this->itemService->expects($this->at(0))
            ->method('read')
            ->will($this->throwException(new ServiceNotFoundException('')));
        $this->itemService->expects($this->at(1))
            ->method('read')
            ->with($this->equalTo(4),
                $this->equalTo(true),
                $this->equalTo($this->user));
        $this->itemAPI->readMultiple([2, 4]);
    }


    public function testUnreadMultiple() {
        $this->itemService->expects($this->at(0))
            ->method('read')
            ->with($this->equalTo(2),
                $this->equalTo(false),
                $this->equalTo($this->user));
        $this->itemService->expects($this->at(1))
            ->method('read')
            ->with($this->equalTo(4),
                $this->equalTo(false),
                $this->equalTo($this->user));
        $this->itemAPI->unreadMultiple([2, 4]);
    }


    public function testStarMultiple() {
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
            ->with($this->equalTo(2),
                $this->equalTo('a'),
                $this->equalTo(true),
                $this->equalTo($this->user));
        $this->itemService->expects($this->at(1))
            ->method('star')
            ->with($this->equalTo(4),
                $this->equalTo('b'),
                $this->equalTo(true),
                $this->equalTo($this->user));
        $this->itemAPI->starMultiple($ids);
    }


    public function testStarMultipleDoesntCareAboutException() {
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
            ->with($this->equalTo(4),
                $this->equalTo('b'),
                $this->equalTo(true),
                $this->equalTo($this->user));
        $this->itemAPI->starMultiple($ids);
    }


    public function testUnstarMultiple() {
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
            ->with($this->equalTo(2),
                $this->equalTo('a'),
                $this->equalTo(false),
                $this->equalTo($this->user));
        $this->itemService->expects($this->at(1))
            ->method('star')
            ->with($this->equalTo(4),
                $this->equalTo('b'),
                $this->equalTo(false),
                $this->equalTo($this->user));
        $this->itemAPI->unstarMultiple($ids);
    }


}
