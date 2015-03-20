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

use \OCA\News\Db\Item;
use \OCA\News\Db\Feed;
use \OCA\News\Db\FeedType;
use \OCA\News\Service\ServiceNotFoundException;


class ItemControllerTest extends \PHPUnit_Framework_TestCase {

    private $appName;
    private $settings;
    private $itemService;
    private $feedService;
    private $request;
    private $controller;
    private $newestItemId;


    /**
     * Gets run before each test
     */
    public function setUp(){
        $this->appName = 'news';
        $this->user = 'jackob';
        $this->settings = $this->getMockBuilder(
            '\OCP\IConfig')
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemService =
        $this->getMockBuilder('\OCA\News\Service\ItemService')
            ->disableOriginalConstructor()
            ->getMock();
        $this->feedService =
        $this->getMockBuilder('\OCA\News\Service\FeedService')
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockBuilder(
            '\OCP\IRequest')
            ->disableOriginalConstructor()
            ->getMock();
        $this->controller = new ItemController($this->appName, $this->request,
                $this->feedService, $this->itemService, $this->settings,
                $this->user);
        $this->newestItemId = 12312;
    }


    public function testRead(){
        $this->itemService->expects($this->once())
            ->method('read')
            ->with(4, true, $this->user);

        $this->controller->read(4, true);
    }


    public function testReadDoesNotExist(){
        $msg = 'hi';

        $this->itemService->expects($this->once())
            ->method('read')
            ->will($this->throwException(new ServiceNotFoundException($msg)));

        $response = $this->controller->read(4);
        $params = json_decode($response->render(), true);

        $this->assertEquals($response->getStatus(), Http::STATUS_NOT_FOUND);
        $this->assertEquals($msg, $params['message']);
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
        $this->controller->readMultiple([2, 4]);
    }


    public function testReadMultipleDontStopOnException() {
        $this->itemService->expects($this->at(0))
            ->method('read')
            ->with($this->equalTo(2),
                $this->equalTo(true),
                $this->equalTo($this->user))
            ->will($this->throwException(new ServiceNotFoundException('yo')));
        $this->itemService->expects($this->at(1))
            ->method('read')
            ->with($this->equalTo(4),
                $this->equalTo(true),
                $this->equalTo($this->user));
        $this->controller->readMultiple([2, 4]);
    }


    public function testStar(){
        $this->itemService->expects($this->once())
            ->method('star')
            ->with(
                $this->equalTo(4),
                $this->equalTo('test'),
                $this->equalTo(true),
                $this->equalTo($this->user));

        $this->controller->star(4, 'test', true);
    }


    public function testStarDoesNotExist(){
        $msg = 'ho';

        $this->itemService->expects($this->once())
            ->method('star')
            ->will($this->throwException(new ServiceNotFoundException($msg)));

        $response = $this->controller->star(4, 'test', false);
        $params = json_decode($response->render(), true);

        $this->assertEquals($response->getStatus(), Http::STATUS_NOT_FOUND);
        $this->assertEquals($msg, $params['message']);
    }


    public function testReadAll(){
        $feed = new Feed();

        $expected = ['feeds' => [$feed]];

        $this->itemService->expects($this->once())
            ->method('readAll')
            ->with($this->equalTo(5),
                $this->equalTo($this->user));
        $this->feedService->expects($this->once())
            ->method('findAll')
            ->with($this->equalTo($this->user))
            ->will($this->returnValue([$feed]));

        $response = $this->controller->readAll(5);
        $this->assertEquals($expected, $response);
    }


    private function itemsApiExpects($id, $type, $oldestFirst='1'){
        $this->settings->expects($this->at(0))
            ->method('getUserValue')
            ->with($this->equalTo($this->user),
                $this->equalTo($this->appName),
                $this->equalTo('showAll'))
            ->will($this->returnValue('1'));
        $this->settings->expects($this->at(1))
            ->method('getUserValue')
            ->with($this->equalTo($this->user),
                $this->equalTo($this->appName),
                $this->equalTo('oldestFirst'))
            ->will($this->returnValue($oldestFirst));
        $this->settings->expects($this->at(2))
            ->method('setUserValue')
            ->with($this->equalTo($this->user),
                $this->equalTo($this->appName),
                $this->equalTo('lastViewedFeedId'),
                $this->equalTo($id));
        $this->settings->expects($this->at(3))
            ->method('setUserValue')
            ->with($this->equalTo($this->user),
                $this->equalTo($this->appName),
                $this->equalTo('lastViewedFeedType'),
                $this->equalTo($type));
    }


    public function testIndex(){
        $feeds = [new Feed()];
        $result = [
            'items' => [new Item()],
            'feeds' => $feeds,
            'newestItemId' => $this->newestItemId,
            'starred' => 3111
        ];

        $this->itemsApiExpects(2, FeedType::FEED, '0');

        $this->feedService->expects($this->once())
            ->method('findAll')
            ->with($this->equalTo($this->user))
            ->will($this->returnValue($feeds));

        $this->itemService->expects($this->once())
            ->method('getNewestItemId')
            ->with($this->equalTo($this->user))
            ->will($this->returnValue($this->newestItemId));

        $this->itemService->expects($this->once())
            ->method('starredCount')
            ->with($this->equalTo($this->user))
            ->will($this->returnValue(3111));

        $this->itemService->expects($this->once())
            ->method('findAll')
            ->with(
                $this->equalTo(2),
                $this->equalTo(FeedType::FEED),
                $this->equalTo(3),
                $this->equalTo(0),
                $this->equalTo(true),
                $this->equalTo(false),
                $this->equalTo($this->user),
                $this->equalTo([]))
            ->will($this->returnValue($result['items']));

        $response = $this->controller->index(FeedType::FEED, 2, 3);
        $this->assertEquals($result, $response);
    }


    public function testIndexSearch(){
        $feeds = [new Feed()];
        $result = [
            'items' => [new Item()],
            'feeds' => $feeds,
            'newestItemId' => $this->newestItemId,
            'starred' => 3111
        ];

        $this->itemsApiExpects(2, FeedType::FEED, '0');

        $this->feedService->expects($this->once())
            ->method('findAll')
            ->with($this->equalTo($this->user))
            ->will($this->returnValue($feeds));

        $this->itemService->expects($this->once())
            ->method('getNewestItemId')
            ->with($this->equalTo($this->user))
            ->will($this->returnValue($this->newestItemId));

        $this->itemService->expects($this->once())
            ->method('starredCount')
            ->with($this->equalTo($this->user))
            ->will($this->returnValue(3111));

        $this->itemService->expects($this->once())
            ->method('findAll')
            ->with(
                $this->equalTo(2),
                $this->equalTo(FeedType::FEED),
                $this->equalTo(3),
                $this->equalTo(0),
                $this->equalTo(true),
                $this->equalTo(false),
                $this->equalTo($this->user),
                $this->equalTo(['test', 'search']))
            ->will($this->returnValue($result['items']));

        $response = $this->controller->index(FeedType::FEED, 2, 3,
            0, null, null, 'test%20search');
        $this->assertEquals($result, $response);
    }


    public function testItemsOffsetNotZero(){
        $result = ['items' => [new Item()]];

        $this->itemsApiExpects(2, FeedType::FEED);

        $this->itemService->expects($this->once())
            ->method('findAll')
            ->with($this->equalTo(2),
                $this->equalTo(FeedType::FEED),
                $this->equalTo(3),
                $this->equalTo(10),
                $this->equalTo(true),
                $this->equalTo(true),
                $this->equalTo($this->user))
            ->will($this->returnValue($result['items']));

        $this->feedService->expects($this->never())
            ->method('findAll');

        $response = $this->controller->index(FeedType::FEED, 2, 3, 10);
        $this->assertEquals($result, $response);
    }


    public function testGetItemsNoNewestItemsId(){
        $this->itemsApiExpects(2, FeedType::FEED);

        $this->itemService->expects($this->once())
            ->method('getNewestItemId')
            ->with($this->equalTo($this->user))
            ->will($this->throwException(new ServiceNotFoundException('')));

        $response = $this->controller->index(FeedType::FEED, 2, 3);
        $this->assertEquals([], $response);
    }


    public function testNewItems(){
        $feeds = [new Feed()];
        $result = [
            'items' => [new Item()],
            'feeds' => $feeds,
            'newestItemId' => $this->newestItemId,
            'starred' => 3111
        ];

        $this->settings->expects($this->once())
            ->method('getUserValue')
            ->with($this->equalTo($this->user),
                $this->equalTo($this->appName),
                $this->equalTo('showAll'))
            ->will($this->returnValue('1'));

        $this->feedService->expects($this->once())
            ->method('findAll')
            ->with($this->equalTo($this->user))
            ->will($this->returnValue($feeds));

        $this->itemService->expects($this->once())
            ->method('getNewestItemId')
            ->with($this->equalTo($this->user))
            ->will($this->returnValue($this->newestItemId));

        $this->itemService->expects($this->once())
            ->method('starredCount')
            ->with($this->equalTo($this->user))
            ->will($this->returnValue(3111));

        $this->itemService->expects($this->once())
            ->method('findAllNew')
            ->with(
                $this->equalTo(2),
                $this->equalTo(FeedType::FEED),
                $this->equalTo(3),
                $this->equalTo(true),
                $this->equalTo($this->user))
            ->will($this->returnValue($result['items']));

        $response = $this->controller->newItems(FeedType::FEED, 2, 3);
        $this->assertEquals($result, $response);
    }


    public function testGetNewItemsNoNewestItemsId(){
        $this->settings->expects($this->once())
            ->method('getUserValue')
            ->with($this->equalTo($this->user),
                $this->equalTo($this->appName),
                $this->equalTo('showAll'))
            ->will($this->returnValue('1'));

        $this->itemService->expects($this->once())
            ->method('getNewestItemId')
            ->with($this->equalTo($this->user))
            ->will($this->throwException(new ServiceNotFoundException('')));

        $response = $this->controller->newItems(FeedType::FEED, 2, 3);
        $this->assertEquals([], $response);
    }


}