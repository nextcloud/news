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
use OCA\News\Service\FeedServiceV2;
use OCA\News\Service\ItemServiceV2;
use OCA\News\Service\ShareService;
use \OCP\AppFramework\Http;

use \OCA\News\Db\Item;
use \OCA\News\Db\Feed;
use \OCA\News\Db\ListType;
use \OCA\News\Service\Exceptions\ServiceNotFoundException;
use OCP\IConfig;
use OCP\IRequest;

use OCP\IUser;
use OCP\IUserSession;
use PHPUnit\Framework\TestCase;

class ItemControllerTest extends TestCase
{

    private $appName;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|IConfig
     */
    private $settings;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ItemServiceV2
     */
    private $itemService;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|FeedServiceV2
     */
    private $feedService;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ShareService
     */
    private $shareService;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|IRequest
     */
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
        $this->getMockBuilder(ItemServiceV2::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->feedService =
        $this->getMockBuilder(FeedServiceV2::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->shareService =
        $this->getMockBuilder(ShareService::class)
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
            $this->shareService,
            $this->settings,
            $this->userSession
        );
        $this->newestItemId = 12312;
    }


    public function testRead()
    {
        $this->itemService->expects($this->once())
            ->method('read')
            ->with('user', 4, true);

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


    public function testShare()
    {
        $this->shareService->expects($this->once())
            ->method('shareItemWithUser')
            ->with('user', 4, 'test');

        $this->controller->share(4, 'test');
    }


    public function testShareDoesNotExist()
    {
        $msg = 'hi';

        $this->shareService->expects($this->once())
            ->method('shareItemWithUser')
            ->with('user', 4, 'test')
            ->will($this->throwException(new ServiceNotFoundException($msg)));

        $response = $this->controller->share(4, 'test');
        $params = json_decode($response->render(), true);

        $this->assertEquals($response->getStatus(), Http::STATUS_NOT_FOUND);
        $this->assertEquals($msg, $params['message']);
    }


    public function testReadMultiple()
    {
        $this->itemService->expects($this->exactly(2))
            ->method('read')
            ->withConsecutive(
                ['user', 2, true],
                ['user', 4, true]
            );

        $this->controller->readMultiple([2, 4]);
    }


    public function testReadMultipleDontStopOnException()
    {

        $this->itemService->expects($this->exactly(2))
            ->method('read')
            ->withConsecutive(
                ['user', 2, true],
                ['user', 4, true]
            )
            ->willReturnOnConsecutiveCalls($this->throwException(new ServiceNotFoundException('yo')), new Item());
        $this->controller->readMultiple([2, 4]);
    }


    public function testStar()
    {
        $this->itemService->expects($this->once())
            ->method('starByGuid')
            ->with('user', 4, 'test', true);

        $this->controller->star(4, 'test', true);
    }


    public function testStarDoesNotExist()
    {
        $msg = 'ho';

        $this->itemService->expects($this->once())
            ->method('starByGuid')
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
            ->with('user', 5);
        $this->feedService->expects($this->once())
            ->method('findAllForUser')
            ->with('user')
            ->will($this->returnValue([$feed]));

        $response = $this->controller->readAll(5);
        $this->assertEquals($expected, $response);
    }

    /**
     * Setup expectations for settings
     *
     * @param        $id
     * @param        $type
     * @param string $oldestFirst
     */
    private function itemsApiExpects($id, $type, $oldestFirst = '1'): void
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


    public function testIndexForFeed()
    {
        $feeds = [new Feed()];
        $result = [
            'items' => [new Item()],
            'feeds' => $feeds,
            'newestItemId' => $this->newestItemId,
            'starred' => 3
        ];

        $this->itemsApiExpects(2, ListType::FEED, '0');

        $this->feedService->expects($this->once())
            ->method('findAllForUser')
            ->with('user')
            ->will($this->returnValue($feeds));

        $this->itemService->expects($this->once())
            ->method('newest')
            ->with('user')
            ->will($this->returnValue(Item::fromParams(['id' => $this->newestItemId])));

        $this->itemService->expects($this->once())
            ->method('starred')
            ->with('user')
            ->will($this->returnValue([1, 2, 3]));

        $this->itemService->expects($this->once())
            ->method('findAllInFeedWithFilters')
            ->with('user', 2, 3, 0, false, false, [])
            ->will($this->returnValue($result['items']));

        $this->shareService->expects($this->once())
            ->method('mapSharedByDisplayNames')
            ->with($result['items'])
            ->will($this->returnValue($result['items']));

        $response = $this->controller->index(ListType::FEED, 2, 3);
        $this->assertEquals($result, $response);
    }


    public function testIndexForFolder()
    {
        $feeds = [new Feed()];
        $result = [
            'items' => [new Item()],
            'feeds' => $feeds,
            'newestItemId' => $this->newestItemId,
            'starred' => 3
        ];

        $this->itemsApiExpects(2, ListType::FOLDER, '0');

        $this->feedService->expects($this->once())
            ->method('findAllForUser')
            ->with('user')
            ->will($this->returnValue($feeds));

        $this->itemService->expects($this->once())
            ->method('newest')
            ->with('user')
            ->will($this->returnValue(Item::fromParams(['id' => $this->newestItemId])));

        $this->itemService->expects($this->once())
            ->method('starred')
            ->with('user')
            ->will($this->returnValue([1, 2, 3]));

        $this->itemService->expects($this->once())
            ->method('findAllInFolderWithFilters')
            ->with('user', 2, 3, 0, false, false, [])
            ->will($this->returnValue($result['items']));

        $this->shareService->expects($this->once())
            ->method('mapSharedByDisplayNames')
            ->with($result['items'])
            ->will($this->returnValue($result['items']));

        $response = $this->controller->index(ListType::FOLDER, 2, 3);
        $this->assertEquals($result, $response);
    }


    public function testIndexForOther()
    {
        $feeds = [new Feed()];
        $result = [
            'items' => [new Item()],
            'feeds' => $feeds,
            'newestItemId' => $this->newestItemId,
            'starred' => 3
        ];

        $this->itemsApiExpects(2, ListType::STARRED, '0');

        $this->feedService->expects($this->once())
            ->method('findAllForUser')
            ->with('user')
            ->will($this->returnValue($feeds));

        $this->itemService->expects($this->once())
            ->method('newest')
            ->with('user')
            ->will($this->returnValue(Item::fromParams(['id' => $this->newestItemId])));

        $this->itemService->expects($this->once())
            ->method('starred')
            ->with('user')
            ->will($this->returnValue([1, 2, 3]));

        $this->itemService->expects($this->once())
            ->method('findAllWithFilters')
            ->with('user', 2, 3, 0, false, [])
            ->will($this->returnValue($result['items']));

        $this->shareService->expects($this->once())
            ->method('mapSharedByDisplayNames')
            ->with($result['items'])
            ->will($this->returnValue($result['items']));

        $response = $this->controller->index(ListType::STARRED, 2, 3);
        $this->assertEquals($result, $response);
    }


    public function testIndexSearchFeed()
    {
        $feeds = [new Feed()];
        $result = [
            'items' => [new Item()],
            'feeds' => $feeds,
            'newestItemId' => $this->newestItemId,
            'starred' => 3
        ];

        $this->itemsApiExpects(2, ListType::FEED, '0');

        $this->feedService->expects($this->once())
            ->method('findAllForUser')
            ->with('user')
            ->will($this->returnValue($feeds));

        $this->itemService->expects($this->once())
            ->method('newest')
            ->with('user')
            ->will($this->returnValue(Item::fromParams(['id' => $this->newestItemId])));

        $this->itemService->expects($this->once())
            ->method('starred')
            ->with('user')
            ->will($this->returnValue([1, 2, 3]));

        $this->itemService->expects($this->once())
            ->method('findAllInFeedWithFilters')
            ->with('user', 2, 3, 0, false, false, ['test', 'search'])
            ->will($this->returnValue($result['items']));

        $this->shareService->expects($this->once())
            ->method('mapSharedByDisplayNames')
            ->with($result['items'])
            ->will($this->returnValue($result['items']));

        $response = $this->controller->index(ListType::FEED, 2, 3, 0, null, null, 'test%20%20search%20');
        $this->assertEquals($result, $response);
    }


    public function testItemsOffsetNotZero()
    {
        $result = ['items' => [new Item()]];

        $this->itemsApiExpects(2, ListType::FEED);

        $this->itemService->expects($this->once())
            ->method('findAllInFeedWithFilters')
            ->with('user', 2, 3, 10, false, true)
            ->will($this->returnValue($result['items']));

        $this->shareService->expects($this->once())
            ->method('mapSharedByDisplayNames')
            ->with($result['items'])
            ->will($this->returnValue($result['items']));

        $this->feedService->expects($this->never())
            ->method('findAllForUser');

        $response = $this->controller->index(ListType::FEED, 2, 3, 10);
        $this->assertEquals($result, $response);
    }


    public function testGetItemsNoNewestItemsId()
    {
        $result = [
            'items' => [],
            'feeds' => [],
            'newestItemId' => null,
            'starred' => 0
        ];

        $this->itemsApiExpects(2, ListType::FEED);

        $this->itemService->expects($this->once())
            ->method('newest')
            ->with('user')
            ->will($this->throwException(new ServiceNotFoundException('')));

        $response = $this->controller->index(ListType::FEED, 2, 3);
        $this->assertEquals($result, $response);
    }


    public function testNewItemsFeed()
    {
        $feeds = [new Feed()];
        $result = [
            'items' => [new Item()],
            'feeds' => $feeds,
            'newestItemId' => $this->newestItemId,
            'starred' => 3
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
            ->method('newest')
            ->with('user')
            ->will($this->returnValue(Item::fromParams(['id' => $this->newestItemId])));

        $this->itemService->expects($this->once())
            ->method('starred')
            ->with('user')
            ->will($this->returnValue([1, 2, 3]));

        $this->itemService->expects($this->once())
            ->method('findAllInFeedAfter')
            ->with('user', 2, 3, false)
            ->will($this->returnValue($result['items']));

        $this->shareService->expects($this->once())
            ->method('mapSharedByDisplayNames')
            ->with($result['items'])
            ->will($this->returnValue($result['items']));

        $response = $this->controller->newItems(ListType::FEED, 2, 3);
        $this->assertEquals($result, $response);
    }


    public function testNewItemsFolder()
    {
        $feeds = [new Feed()];
        $result = [
            'items' => [new Item()],
            'feeds' => $feeds,
            'newestItemId' => $this->newestItemId,
            'starred' => 3
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
            ->method('newest')
            ->with('user')
            ->will($this->returnValue(Item::fromParams(['id' => $this->newestItemId])));

        $this->itemService->expects($this->once())
            ->method('starred')
            ->with('user')
            ->will($this->returnValue([1, 2, 3]));

        $this->itemService->expects($this->once())
            ->method('findAllInFolderAfter')
            ->with('user', 2, 3, false)
            ->will($this->returnValue($result['items']));

        $this->shareService->expects($this->once())
            ->method('mapSharedByDisplayNames')
            ->with($result['items'])
            ->will($this->returnValue($result['items']));

        $response = $this->controller->newItems(ListType::FOLDER, 2, 3);
        $this->assertEquals($result, $response);
    }


    public function testNewItemsOther()
    {
        $feeds = [new Feed()];
        $result = [
            'items' => [new Item()],
            'feeds' => $feeds,
            'newestItemId' => $this->newestItemId,
            'starred' => 3
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
            ->method('newest')
            ->with('user')
            ->will($this->returnValue(Item::fromParams(['id' => $this->newestItemId])));

        $this->itemService->expects($this->once())
            ->method('starred')
            ->with('user')
            ->will($this->returnValue([1, 2, 3]));

        $this->itemService->expects($this->once())
            ->method('findAllAfter')
            ->with('user', 6, 3)
            ->will($this->returnValue($result['items']));

        $this->shareService->expects($this->once())
            ->method('mapSharedByDisplayNames')
            ->with($result['items'])
            ->will($this->returnValue($result['items']));

        $response = $this->controller->newItems(ListType::UNREAD, 2, 3);
        $this->assertEquals($result, $response);
    }


    public function testGetNewItemsNoNewestItemsId()
    {
        $this->settings->expects($this->once())
            ->method('getUserValue')
            ->with('user', $this->appName, 'showAll')
            ->will($this->returnValue('1'));

        $this->itemService->expects($this->once())
            ->method('newest')
            ->with('user')
            ->will($this->throwException(new ServiceNotFoundException('')));

        $response = $this->controller->newItems(ListType::FEED, 2, 3);
        $this->assertEquals([], $response);
    }
}
