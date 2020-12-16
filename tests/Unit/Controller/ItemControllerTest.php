<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Alessandro Cosentino <cosenal@gmail.com>
 * @author    Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright 2012 Alessandro Cosentino
 * @copyright 2012-2014 Bernhard Posselt
 */

namespace OCA\News\Tests\Unit\Controller;

use OCA\News\Controller\ItemController;
use OCA\News\Service\FeedService;
use OCA\News\Service\ItemService;
use \OCP\AppFramework\Http;

use \OCA\News\Db\Item;
use \OCA\News\Db\Feed;
use \OCA\News\Db\FeedType;
use \OCA\News\Service\Exceptions\ServiceNotFoundException;
use OCP\IConfig;
use OCP\IRequest;

use OCP\IUser;
use OCP\IUserSession;
use PHPUnit\Framework\TestCase;


class ItemControllerTest extends TestCase
{

    private $appName;
    private $settings;
    private $itemService;
    private $feedService;
    private $request;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|IUser
     */
    private $user;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|IUserSession
     */
    private $userSession;
    private $controller;
    private $newestItemId;


    /**
     * Gets run before each test
     */
    public function setUp(): void
    {
        $this->appName = 'news';
        $this->settings = $this->getMockBuilder(IConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemService =
        $this->getMockBuilder(ItemService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->feedService =
        $this->getMockBuilder(FeedService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockBuilder(IRequest::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->user = $this->getMockBuilder(IUser::class)->getMock();
        $this->user->expects($this->any())
            ->method('getUID')
            ->will($this->returnValue('user'));
        $this->userSession = $this->getMockBuilder(IUserSession::class)
            ->getMock();
        $this->userSession->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($this->user));
        $this->controller = new ItemController(
            $this->request,
            $this->feedService,
            $this->itemService,
            $this->settings,
            $this->userSession
        );
        $this->newestItemId = 12312;
    }


    public function testRead()
    {
        $this->itemService->expects($this->once())
            ->method('read')
            ->with(4, true, 'user');

        $this->controller->read(4, true);
    }


    public function testReadDoesNotExist()
    {
        $msg = 'hi';

        $this->itemService->expects($this->once())
            ->method('read')
            ->will($this->throwException(new ServiceNotFoundException($msg)));

        $response = $this->controller->read(4);
        $params = json_decode($response->render(), true);

        $this->assertEquals(Http::STATUS_NOT_FOUND, $response->getStatus());
        $this->assertEquals($msg, $params['message']);
    }


    public function testReadMultiple()
    {
        $this->itemService->expects($this->exactly(2))
            ->method('read')
            ->withConsecutive(
                [2, true, 'user'],
                [4, true, 'user']
            );

        $this->controller->readMultiple([2, 4]);
    }


    public function testReadMultipleDontStopOnException()
    {

        $this->itemService->expects($this->exactly(2))
            ->method('read')
            ->withConsecutive(
                [2, true, 'user'],
                [4, true, 'user']
            )
            ->willReturnOnConsecutiveCalls($this->throwException(new ServiceNotFoundException('yo')), null);
        $this->controller->readMultiple([2, 4]);
    }


    public function testStar()
    {
        $this->itemService->expects($this->once())
            ->method('star')
            ->with(4, 'test', true, 'user');

        $this->controller->star(4, 'test', true);
    }


    public function testStarDoesNotExist()
    {
        $msg = 'ho';

        $this->itemService->expects($this->once())
            ->method('star')
            ->will($this->throwException(new ServiceNotFoundException($msg)));

        $response = $this->controller->star(4, 'test', false);
        $params = json_decode($response->render(), true);

        $this->assertEquals(Http::STATUS_NOT_FOUND, $response->getStatus());
        $this->assertEquals($msg, $params['message']);
    }


    public function testReadAll()
    {
        $feed = new Feed();

        $expected = ['feeds' => [$feed]];

        $this->itemService->expects($this->once())
            ->method('readAll')
            ->with(5, 'user');
        $this->feedService->expects($this->once())
            ->method('findAllForUser')
            ->with('user')
            ->will($this->returnValue([$feed]));

        $response = $this->controller->readAll(5);
        $this->assertEquals($expected, $response);
    }


    private function itemsApiExpects($id, $type, $oldestFirst = '1')
    {
        $this->settings->expects($this->exactly(2))
            ->method('getUserValue')
            ->withConsecutive(
                ['user', $this->appName, 'showAll'],
                ['user', $this->appName, 'oldestFirst']
            )
            ->willReturnOnConsecutiveCalls('1', $oldestFirst);
        $this->settings->expects($this->exactly(2))
            ->method('setUserValue')
            ->withConsecutive(
                ['user', $this->appName, 'lastViewedFeedId', $id],
                ['user', $this->appName, 'lastViewedFeedType', $type]
            );
    }


    public function testIndex()
    {
        $feeds = [new Feed()];
        $result = [
            'items' => [new Item()],
            'feeds' => $feeds,
            'newestItemId' => $this->newestItemId,
            'starred' => 3111
        ];

        $this->itemsApiExpects(2, FeedType::FEED, '0');

        $this->feedService->expects($this->once())
            ->method('findAllForUser')
            ->with('user')
            ->will($this->returnValue($feeds));

        $this->itemService->expects($this->once())
            ->method('getNewestItemId')
            ->with('user')
            ->will($this->returnValue($this->newestItemId));

        $this->itemService->expects($this->once())
            ->method('starredCount')
            ->with('user')
            ->will($this->returnValue(3111));

        $this->itemService->expects($this->once())
            ->method('findAllItems')
            ->with(2, FeedType::FEED, 3, 0, true, false, 'user', [])
            ->will($this->returnValue($result['items']));

        $response = $this->controller->index(FeedType::FEED, 2, 3);
        $this->assertEquals($result, $response);
    }


    public function testIndexSearch()
    {
        $feeds = [new Feed()];
        $result = [
            'items' => [new Item()],
            'feeds' => $feeds,
            'newestItemId' => $this->newestItemId,
            'starred' => 3111
        ];

        $this->itemsApiExpects(2, FeedType::FEED, '0');

        $this->feedService->expects($this->once())
            ->method('findAllForUser')
            ->with('user')
            ->will($this->returnValue($feeds));

        $this->itemService->expects($this->once())
            ->method('getNewestItemId')
            ->with('user')
            ->will($this->returnValue($this->newestItemId));

        $this->itemService->expects($this->once())
            ->method('starredCount')
            ->with('user')
            ->will($this->returnValue(3111));

        $this->itemService->expects($this->once())
            ->method('findAllItems')
            ->with(2, FeedType::FEED, 3, 0, true, false, 'user', ['test', 'search'])
            ->will($this->returnValue($result['items']));

        $response = $this->controller->index(FeedType::FEED, 2, 3, 0, null, null, 'test%20%20search%20');
        $this->assertEquals($result, $response);
    }


    public function testItemsOffsetNotZero()
    {
        $result = ['items' => [new Item()]];

        $this->itemsApiExpects(2, FeedType::FEED);

        $this->itemService->expects($this->once())
            ->method('findAllItems')
            ->with(2, FeedType::FEED, 3, 10, true, true, 'user')
            ->will($this->returnValue($result['items']));

        $this->feedService->expects($this->never())
            ->method('findAllForUser');

        $response = $this->controller->index(FeedType::FEED, 2, 3, 10);
        $this->assertEquals($result, $response);
    }


    public function testGetItemsNoNewestItemsId()
    {
        $this->itemsApiExpects(2, FeedType::FEED);

        $this->itemService->expects($this->once())
            ->method('getNewestItemId')
            ->with('user')
            ->will($this->throwException(new ServiceNotFoundException('')));

        $response = $this->controller->index(FeedType::FEED, 2, 3);
        $this->assertEquals([], $response);
    }


    public function testNewItems()
    {
        $feeds = [new Feed()];
        $result = [
            'items' => [new Item()],
            'feeds' => $feeds,
            'newestItemId' => $this->newestItemId,
            'starred' => 3111
        ];

        $this->settings->expects($this->once())
            ->method('getUserValue')
            ->with('user', $this->appName, 'showAll')
            ->will($this->returnValue('1'));

        $this->feedService->expects($this->once())
            ->method('findAllForUser')
            ->with('user')
            ->will($this->returnValue($feeds));

        $this->itemService->expects($this->once())
            ->method('getNewestItemId')
            ->with('user')
            ->will($this->returnValue($this->newestItemId));

        $this->itemService->expects($this->once())
            ->method('starredCount')
            ->with('user')
            ->will($this->returnValue(3111));

        $this->itemService->expects($this->once())
            ->method('findAllNew')
            ->with(2, FeedType::FEED, 3, true, 'user')
            ->will($this->returnValue($result['items']));

        $response = $this->controller->newItems(FeedType::FEED, 2, 3);
        $this->assertEquals($result, $response);
    }


    public function testGetNewItemsNoNewestItemsId()
    {
        $this->settings->expects($this->once())
            ->method('getUserValue')
            ->with('user', $this->appName, 'showAll')
            ->will($this->returnValue('1'));

        $this->itemService->expects($this->once())
            ->method('getNewestItemId')
            ->with('user')
            ->will($this->throwException(new ServiceNotFoundException('')));

        $response = $this->controller->newItems(FeedType::FEED, 2, 3);
        $this->assertEquals([], $response);
    }


}