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

use OCA\News\Controller\FeedController;
use OCA\News\Service\FeedService;
use OCA\News\Service\FolderServiceV2;
use OCA\News\Service\ItemService;
use OCP\AppFramework\Http;

use OCA\News\Db\Feed;
use OCA\News\Db\FeedType;
use OCA\News\Service\Exceptions\ServiceNotFoundException;
use OCA\News\Service\Exceptions\ServiceConflictException;
use OCP\IConfig;
use OCP\IRequest;

use OCP\IUser;
use OCP\IUserSession;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FeedControllerTest extends TestCase
{
    /**
     * @var string
     */
    private $appName;
    private $exampleResult;
    private $uid;

    /**
     * @var MockObject|FolderServiceV2
     */
    private $folderService;
    /**
     * TODO: Remove
     * @var MockObject|FeedService
     */
    private $feedService;
    /**
     * TODO: Remove
     * @var MockObject|ItemService
     */
    private $itemService;

    /**
     * @var MockObject|IConfig
     */
    private $settings;

    /**
     * @var MockObject|IUser
     */
    private $user;

    /**
     * @var FeedController
     */
    private $class;


    /**
     * Gets run before each test
     */
    public function setUp(): void
    {
        $this->appName = 'news';
        $this->uid = 'jack';
        $this->settings = $this->getMockBuilder(IConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemService = $this
            ->getMockBuilder(ItemService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->feedService = $this
            ->getMockBuilder(FeedService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->folderService = $this
            ->getMockBuilder(FolderServiceV2::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->user = $this->getMockBuilder(IUser::class)->getMock();
        $this->user->expects($this->any())
            ->method('getUID')
            ->will($this->returnValue($this->uid));
        $this->userSession = $this->getMockBuilder(IUserSession::class)
            ->getMock();
        $this->userSession->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($this->user));
        $request = $this->getMockBuilder(IRequest::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->class = new FeedController(
            $request,
            $this->folderService,
            $this->feedService,
            $this->itemService,
            $this->settings,
            $this->userSession
        );
        $this->exampleResult = [
            'activeFeed' => [
                'id' => 0,
                'type' => FeedType::SUBSCRIPTIONS
            ]
        ];
    }


    public function testIndex()
    {
        $result = [
            'feeds' => [
                ['a feed'],
            ],
            'starred' => 13
        ];
        $this->feedService->expects($this->once())
            ->method('findAllForUser')
            ->with($this->uid)
            ->will($this->returnValue($result['feeds']));
        $this->itemService->expects($this->once())
            ->method('getNewestItemId')
            ->with($this->equalTo($this->uid))
            ->will($this->throwException(new ServiceNotFoundException('')));
        $this->itemService->expects($this->once())
            ->method('starredCount')
            ->with($this->equalTo($this->uid))
            ->will($this->returnValue($result['starred']));

        $response = $this->class->index();

        $this->assertEquals($result, $response);
    }


    public function testIndexHighestItemIdExists()
    {
        $result = [
            'feeds' => [
                ['a feed'],
            ],
            'starred' => 13,
            'newestItemId' => 5
        ];
        $this->feedService->expects($this->once())
            ->method('findAllForUser')
            ->with($this->equalTo($this->uid))
            ->will($this->returnValue($result['feeds']));
        $this->itemService->expects($this->once())
            ->method('getNewestItemId')
            ->with($this->equalTo($this->uid))
            ->will($this->returnValue($result['newestItemId']));
        $this->itemService->expects($this->once())
            ->method('starredCount')
            ->with($this->equalTo($this->uid))
            ->will($this->returnValue($result['starred']));

        $response = $this->class->index();

        $this->assertEquals($result, $response);
    }



    private function activeInitMocks($id, $type)
    {
        $this->settings->expects($this->exactly(2))
            ->method('getUserValue')
            ->withConsecutive(
                [$this->uid, $this->appName, 'lastViewedFeedId'],
                [$this->uid, $this->appName, 'lastViewedFeedType']
            )
            ->willReturnOnConsecutiveCalls($id, $type);
    }


    public function testActive()
    {
        $id = 3;
        $type = FeedType::STARRED;
        $result = [
            'activeFeed' => [
                'id' => $id,
                'type' => $type
            ]
        ];

        $this->activeInitMocks($id, $type);

        $response = $this->class->active();

        $this->assertEquals($result, $response);
    }


    public function testActiveFeedDoesNotExist()
    {
        $id = 3;
        $type = FeedType::FEED;
        $ex = new ServiceNotFoundException('hiu');
        $result = $this->exampleResult;

        $this->feedService->expects($this->once())
            ->method('find')
            ->with($this->uid, $id)
            ->will($this->throwException($ex));

        $this->activeInitMocks($id, $type);

        $response = $this->class->active();

        $this->assertEquals($result, $response);
    }


    public function testActiveFolderDoesNotExist()
    {
        $id = 3;
        $type = FeedType::FOLDER;
        $ex = new ServiceNotFoundException('hiu');
        $result = $this->exampleResult;

        $this->folderService->expects($this->once())
            ->method('find')
            ->with($this->uid, $id)
            ->will($this->throwException($ex));

        $this->activeInitMocks($id, $type);

        $response = $this->class->active();

        $this->assertEquals($result, $response);
    }


    public function testActiveActiveIsNull()
    {
        $id = 3;
        $type = null;
        $result = $this->exampleResult;


        $this->activeInitMocks($id, $type);

        $response = $this->class->active();

        $this->assertEquals($result, $response);
    }


    public function testCreate()
    {
        $result = [
            'feeds' => [new Feed()],
            'newestItemId' => 3
        ];

        $this->itemService->expects($this->once())
            ->method('getNewestItemId')
            ->will($this->returnValue($result['newestItemId']));
        $this->feedService->expects($this->once())
            ->method('purgeDeleted')
            ->with($this->equalTo($this->uid), $this->equalTo(false));
        $this->feedService->expects($this->once())
            ->method('create')
            ->with(
                $this->equalTo('hi'),
                $this->equalTo(4),
                $this->equalTo($this->uid),
                $this->equalTo('yo')
            )
            ->will($this->returnValue($result['feeds'][0]));

        $response = $this->class->create('hi', 4, 'yo');

        $this->assertEquals($result, $response);
    }


    public function testCreateNoItems()
    {
        $result = ['feeds' => [new Feed()]];

        $this->feedService->expects($this->once())
            ->method('purgeDeleted')
            ->with($this->equalTo($this->uid), $this->equalTo(false));

        $this->itemService->expects($this->once())
            ->method('getNewestItemId')
            ->will($this->throwException(new ServiceNotFoundException('')));

        $this->feedService->expects($this->once())
            ->method('create')
            ->with(
                $this->equalTo('hi'),
                $this->equalTo(4),
                $this->equalTo($this->uid),
                $this->equalTo('yo')
            )
            ->will($this->returnValue($result['feeds'][0]));

        $response = $this->class->create('hi', 4, 'yo');

        $this->assertEquals($result, $response);
    }


    public function testCreateReturnsErrorForInvalidCreate()
    {
        $msg = 'except';
        $ex = new ServiceNotFoundException($msg);
        $this->feedService->expects($this->once())
            ->method('purgeDeleted')
            ->with($this->equalTo($this->uid), $this->equalTo(false));
        $this->feedService->expects($this->once())
            ->method('create')
            ->will($this->throwException($ex));

        $response = $this->class->create('hi', 4, 'test');
        $params = json_decode($response->render(), true);

        $this->assertEquals($msg, $params['message']);
        $this->assertEquals(
            $response->getStatus(), Http::STATUS_UNPROCESSABLE_ENTITY
        );
    }


    public function testCreateReturnsErrorForDuplicateCreate()
    {
        $msg = 'except';
        $ex = new ServiceConflictException($msg);
        $this->feedService->expects($this->once())
            ->method('purgeDeleted')
            ->with($this->equalTo($this->uid), $this->equalTo(false));
        $this->feedService->expects($this->once())
            ->method('create')
            ->will($this->throwException($ex));

        $response = $this->class->create('hi', 4, 'test');
        $params = json_decode($response->render(), true);

        $this->assertEquals($msg, $params['message']);
        $this->assertEquals($response->getStatus(), Http::STATUS_CONFLICT);
    }


    public function testDelete()
    {
        $this->feedService->expects($this->once())
            ->method('markDeleted')
            ->with($this->equalTo(4));

        $this->class->delete(4);
    }


    public function testDeleteDoesNotExist()
    {
        $msg = 'hehe';

        $this->feedService->expects($this->once())
            ->method('markDeleted')
            ->will($this->throwException(new ServiceNotFoundException($msg)));

        $response = $this->class->delete(4);
        $params = json_decode($response->render(), true);

        $this->assertEquals($msg, $params['message']);
        $this->assertEquals($response->getStatus(), Http::STATUS_NOT_FOUND);
    }


    public function testUpdate()
    {
        $feed = new Feed();
        $feed->setId(3);
        $feed->setUnreadCount(44);
        $result = [
            'feeds' => [
                [
                    'id' => $feed->getId(),
                    'unreadCount' => $feed->getUnreadCount()
                ]
            ]
        ];

        $this->feedService->expects($this->once())
            ->method('update')
            ->with($this->equalTo($this->uid), $this->equalTo(4))
            ->will($this->returnValue($feed));

        $response = $this->class->update(4);

        $this->assertEquals($result, $response);
    }


    public function testUpdateReturnsJSONError()
    {
        $this->feedService->expects($this->once())
            ->method('update')
            ->with($this->equalTo($this->uid), $this->equalTo(4))
            ->will($this->throwException(new ServiceNotFoundException('NO!')));

        $response = $this->class->update(4);
        $render = $response->render();

        $this->assertEquals('{"message":"NO!"}', $render);
        $this->assertEquals($response->getStatus(), Http::STATUS_NOT_FOUND);
    }


    public function testImport()
    {
        $feed = new Feed();

        $expected = [
            'starred' => 3,
            'feeds' => [$feed]
        ];

        $this->feedService->expects($this->once())
            ->method('importArticles')
            ->with(
                $this->equalTo(['json']),
                $this->equalTo($this->uid)
            )
            ->will($this->returnValue($feed));

        $this->itemService->expects($this->once())
            ->method('starredCount')
            ->with($this->equalTo($this->uid))
            ->will($this->returnValue(3));

        $response = $this->class->import(['json']);

        $this->assertEquals($expected, $response);
    }


    public function testImportCreatesNoAdditionalFeed()
    {
        $this->feedService->expects($this->once())
            ->method('importArticles')
            ->with(
                $this->equalTo(['json']),
                $this->equalTo($this->uid)
            )
            ->will($this->returnValue(null));

        $this->itemService->expects($this->once())
            ->method('starredCount')
            ->with($this->equalTo($this->uid))
            ->will($this->returnValue(3));

        $response = $this->class->import(['json']);

        $this->assertEquals(['starred' => 3], $response);
    }


    public function testReadFeed()
    {
        $expected = [
            'feeds' => [
                [
                    'id' => 4,
                    'unreadCount' => 0
                ]
            ]
        ];

        $this->itemService->expects($this->once())
            ->method('readFeed')
            ->with($this->equalTo(4), $this->equalTo(5), $this->uid);

        $response = $this->class->read(4, 5);
        $this->assertEquals($expected, $response);
    }


    public function testRestore()
    {
        $this->feedService->expects($this->once())
            ->method('unmarkDeleted')
            ->with($this->equalTo(4));

        $this->class->restore(4);
    }


    public function testRestoreDoesNotExist()
    {
        $msg = 'hehe';

        $this->feedService->expects($this->once())
            ->method('unmarkDeleted')
            ->will($this->throwException(new ServiceNotFoundException($msg)));

        $response = $this->class->restore(4);
        $params = json_decode($response->render(), true);

        $this->assertEquals($msg, $params['message']);
        $this->assertEquals($response->getStatus(), Http::STATUS_NOT_FOUND);
    }

    public function testPatch()
    {
        $expected = [
            'pinned' => true,
            'fullTextEnabled' => true,
            'updateMode' => 1
        ];
        $this->feedService->expects($this->once())
            ->method('patch')
            ->with(4, $this->uid, $expected)
            ->will($this->returnValue(1));

        $this->class->patch(4, true, true, 1);
    }

    public function testPatchDoesNotExist()
    {
        $msg = 'hehe';

        $this->feedService->expects($this->once())
            ->method('patch')
            ->will($this->throwException(new ServiceNotFoundException($msg)));

        $response = $this->class->patch(4, 2);
        $params = json_decode($response->render(), true);

        $this->assertEquals($msg, $params['message']);
        $this->assertEquals($response->getStatus(), Http::STATUS_NOT_FOUND);
    }


}
