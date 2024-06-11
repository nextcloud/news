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
use OCA\News\Db\Folder;
use OCA\News\Service\FeedServiceV2;
use OCA\News\Service\FolderServiceV2;
use OCA\News\Service\ImportService;
use OCA\News\Service\ItemServiceV2;
use OCP\AppFramework\Http;

use OCA\News\Db\Feed;
use OCA\News\Db\ListType;
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
     * @var MockObject|FeedServiceV2
     */
    private $feedService;
    /**
     * @var MockObject|ImportService
     */
    private $importService;
    /**
     * @var MockObject|ItemServiceV2
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
     * @var MockObject|IUserSession
     */
    private $userSession;

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
            ->getMockBuilder(ItemServiceV2::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->feedService = $this
            ->getMockBuilder(FeedServiceV2::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->importService = $this
            ->getMockBuilder(ImportService::class)
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
            $this->importService,
            $this->settings,
            $this->userSession
        );
        $this->exampleResult = [
            'activeFeed' => [
                'id' => 0,
                'type' => ListType::ALL_ITEMS
            ]
        ];
    }


    public function testIndex()
    {
        $result = [
            'feeds' => [
                ['a feed'],
            ],
            'starred' => 4
        ];
        $this->feedService->expects($this->once())
            ->method('findAllForUser')
            ->with($this->uid)
            ->will($this->returnValue($result['feeds']));
        $this->itemService->expects($this->once())
            ->method('newest')
            ->with($this->uid)
            ->will($this->throwException(new ServiceNotFoundException('')));
        $this->itemService->expects($this->once())
            ->method('starred')
            ->with($this->uid)
            ->will($this->returnValue([1, 2, 3, 4]));

        $response = $this->class->index();

        $this->assertEquals($result, $response);
    }


    public function testIndexHighestItemIdExists()
    {
        $result = [
            'feeds' => [
                ['a feed'],
            ],
            'starred' => 2,
            'newestItemId' => 5
        ];
        $this->feedService->expects($this->once())
            ->method('findAllForUser')
            ->with($this->uid)
            ->will($this->returnValue($result['feeds']));
        $this->itemService->expects($this->once())
            ->method('newest')
            ->with($this->uid)
            ->will($this->returnValue(Feed::fromParams(['id' => 5])));
        $this->itemService->expects($this->once())
            ->method('starred')
            ->with($this->uid)
            ->will($this->returnValue([1, 2]));

        $response = $this->class->index();

        $this->assertEquals($result, $response);
    }


    /**
     * Configure settings with active mocks
     *
     * @param $id
     * @param $type
     */
    private function activeInitMocks($id, $type): void
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
        $type = ListType::STARRED;
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


    public function testActiveFeed()
    {
        $id = 3;
        $type = ListType::FEED;
        $result = [
            'activeFeed' => [
                'id' => $id,
                'type' => $type
            ]
        ];

        $this->feedService->expects($this->once())
            ->method('find')
            ->with($this->uid, $id)
            ->will($this->returnValue(new Feed()));

        $this->activeInitMocks($id, $type);

        $response = $this->class->active();

        $this->assertEquals($result, $response);
    }


    public function testActiveFeedDoesNotExist()
    {
        $id = 3;
        $type = ListType::FEED;
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


    public function testActiveFolder()
    {
        $type = ListType::FOLDER;
        $folder = new Folder();
        $folder->setId(3);

        $result = [
            'activeFeed' => [
                'id' => 3,
                'type' => 1
            ]
        ];

        $this->folderService->expects($this->once())
            ->method('find')
            ->with($this->uid, 3)
            ->will($this->returnValue($folder));

        $this->activeInitMocks(3, $type);

        $response = $this->class->active();

        $this->assertEquals($result, $response);
    }


    public function testActiveFolderDoesNotExist()
    {
        $id = 3;
        $type = ListType::FOLDER;
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
            ->method('newest')
            ->will($this->returnValue(Feed::fromParams(['id' => 3])));
        $this->feedService->expects($this->once())
            ->method('purgeDeleted')
            ->with($this->uid, false);

        $this->feedService->expects($this->once())
            ->method('fetch')
            ->with($result['feeds'][0]);
        $this->feedService->expects($this->once())
            ->method('create')
            ->with($this->uid, 'hi', 4, false, 'yo')
            ->will($this->returnValue($result['feeds'][0]));

        $response = $this->class->create('hi', 4, 'yo');

        $this->assertEquals($result, $response);
    }


    public function testCreateOldFolderId()
    {
        $result = [
            'feeds' => [new Feed()],
            'newestItemId' => 3
        ];

        $this->itemService->expects($this->once())
            ->method('newest')
            ->will($this->returnValue(Feed::fromParams(['id' => 3])));
        $this->feedService->expects($this->once())
            ->method('purgeDeleted')
            ->with($this->uid, false);

        $this->feedService->expects($this->once())
            ->method('fetch')
            ->with($result['feeds'][0]);
        $this->feedService->expects($this->once())
            ->method('create')
            ->with($this->uid, 'hi', null, false, 'yo')
            ->will($this->returnValue($result['feeds'][0]));

        $response = $this->class->create('hi', 0, 'yo');

        $this->assertEquals($result, $response);
    }


    public function testCreateNoItems()
    {
        $result = ['feeds' => [new Feed()]];

        $this->feedService->expects($this->once())
            ->method('purgeDeleted')
            ->with($this->uid, false);

        $this->itemService->expects($this->once())
            ->method('newest')
            ->will($this->throwException(new ServiceNotFoundException('')));

        $this->feedService->expects($this->once())
            ->method('create')
            ->with($this->uid, 'hi', 4, false, 'yo')
            ->will($this->returnValue($result['feeds'][0]));

        $this->feedService->expects($this->once())
            ->method('fetch')
            ->with($result['feeds'][0]);

        $response = $this->class->create('hi', 4, 'yo');

        $this->assertEquals($result, $response);
    }


    public function testCreateReturnsErrorForInvalidCreate()
    {
        $msg = 'except';
        $ex = new ServiceNotFoundException($msg);
        $this->feedService->expects($this->once())
            ->method('purgeDeleted')
            ->with($this->uid, false);
        $this->feedService->expects($this->once())
            ->method('create')
            ->will($this->throwException($ex));

        $response = $this->class->create('hi', 4, 'test');
        $params = json_decode($response->render(), true);

        $this->assertEquals($msg, $params['message']);
        $this->assertEquals(
            $response->getStatus(),
            Http::STATUS_UNPROCESSABLE_ENTITY
        );
    }


    public function testCreateReturnsErrorForDuplicateCreate()
    {
        $msg = 'except';
        $ex = new ServiceConflictException($msg);
        $this->feedService->expects($this->once())
            ->method('purgeDeleted')
            ->with($this->uid, false);
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
        $feed = $this->getMockBuilder(Feed::class)
                     ->getMock();
        $feed->expects($this->once())
             ->method('setDeletedAt');
        $this->feedService->expects($this->once())
            ->method('find')
            ->with('jack', 4)
            ->willReturn($feed);
        $this->feedService->expects($this->once())
            ->method('update')
            ->with('jack', $feed);

        $this->class->delete(4);
    }


    public function testDeleteDoesNotExist()
    {
        $msg = 'hehe';

        $this->feedService->expects($this->once())
            ->method('find')
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
            ->method('find')
            ->with($this->uid, 4)
            ->will($this->returnValue($feed));

        $this->feedService->expects($this->once())
            ->method('fetch')
            ->with($feed)
            ->will($this->returnValue($feed));

        $response = $this->class->update(4);

        $this->assertEquals($result, $response);
    }


    public function testUpdateReturnsJSONError()
    {
        $this->feedService->expects($this->once())
            ->method('find')
            ->with($this->uid, 4)
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

        $this->importService->expects($this->once())
            ->method('importArticles')
            ->with($this->uid, ['json'],)
            ->will($this->returnValue($feed));

        $this->itemService->expects($this->once())
            ->method('starred')
            ->with($this->uid)
            ->will($this->returnValue([1, 2, 3]));

        $response = $this->class->import(['json']);

        $this->assertEquals($expected, $response);
    }


    public function testImportCreatesNoAdditionalFeed()
    {
        $this->importService->expects($this->once())
            ->method('importArticles')
            ->with($this->uid, ['json'])
            ->will($this->returnValue(null));

        $this->itemService->expects($this->once())
            ->method('starred')
            ->with($this->uid)
            ->will($this->returnValue([1, 2, 3]));

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

        $this->feedService->expects($this->once())
            ->method('read')
            ->with($this->uid, 4, 5);

        $response = $this->class->read(4, 5);
        $this->assertEquals($expected, $response);
    }


    public function testRestore()
    {
        $feed = $this->getMockBuilder(Feed::class)
                     ->getMock();

        $feed->expects($this->once())
             ->method('setDeletedAt')
             ->with(null);

        $this->feedService->expects($this->once())
            ->method('find')
            ->with($this->uid, 4)
            ->willReturn($feed);

        $this->feedService->expects($this->once())
                          ->method('update')
                          ->with($this->uid, $feed);

        $this->class->restore(4);
    }


    public function testRestoreDoesNotExist()
    {
        $msg = 'hehe';

        $this->feedService->expects($this->once())
            ->method('find')
            ->with($this->uid, 4)
            ->will($this->throwException(new ServiceNotFoundException($msg)));

        $response = $this->class->restore(4);
        $params = json_decode($response->render(), true);

        $this->assertEquals($msg, $params['message']);
        $this->assertEquals($response->getStatus(), Http::STATUS_NOT_FOUND);
    }

    public function testPatch()
    {
        $feed = $this->getMockBuilder(Feed::class)
                     ->getMock();

        $feed->expects($this->never())
             ->method('setFolderId');

        $feed->expects($this->once())
             ->method('setPinned')
             ->with(true);

        $feed->expects($this->once())
             ->method('setFullTextEnabled')
             ->with(false);


        $feed->expects($this->once())
             ->method('setUpdateMode')
             ->with(1);


        $feed->expects($this->never())
             ->method('setOrdering')
             ->with(true);


        $feed->expects($this->never())
             ->method('setTitle')
             ->with(true);

        $this->feedService->expects($this->once())
            ->method('find')
            ->with($this->uid, 4)
            ->will($this->returnValue($feed));

        $this->feedService->expects($this->once())
            ->method('update')
            ->with($this->uid, $feed);

        $this->class->patch(4, true, false, 1);
    }

    public function testPatchFolder()
    {
        $feed = $this->getMockBuilder(Feed::class)
                     ->getMock();

        $feed->expects($this->once())
             ->method('setFolderId')
             ->with(5);

        $feed->expects($this->once())
             ->method('setPinned')
             ->with(true);

        $feed->expects($this->once())
             ->method('setFullTextEnabled')
             ->with(false);


        $feed->expects($this->once())
             ->method('setUpdateMode')
             ->with(1);


        $feed->expects($this->never())
             ->method('setOrdering')
             ->with(true);


        $feed->expects($this->never())
             ->method('setTitle')
             ->with(true);

        $this->feedService->expects($this->once())
            ->method('find')
            ->with($this->uid, 4)
            ->will($this->returnValue($feed));

        $this->feedService->expects($this->once())
            ->method('update')
            ->with($this->uid, $feed);

        $this->class->patch(4, true, false, 1, null, 5);
    }

    public function testPatchDoesNotExist()
    {
        $msg = 'hehe';

        $this->feedService->expects($this->once())
            ->method('find')
            ->with($this->uid, 4)
            ->will($this->throwException(new ServiceNotFoundException($msg)));

        $response = $this->class->patch(4, 2);
        $params = json_decode($response->render(), true);

        $this->assertEquals($msg, $params['message']);
        $this->assertEquals($response->getStatus(), Http::STATUS_NOT_FOUND);
    }

    public function testPatchDoesNotExistOnUpdate()
    {
        $feed = $this->getMockBuilder(Feed::class)
            ->getMock();

        $feed->expects($this->once())
            ->method('setFolderId')
            ->with(1);

        $feed->expects($this->once())
            ->method('setPinned')
            ->with(true);

        $feed->expects($this->once())
            ->method('setFullTextEnabled')
            ->with(false);


        $feed->expects($this->once())
            ->method('setUpdateMode')
            ->with(1);


        $feed->expects($this->once())
            ->method('setOrdering')
            ->with(1);


        $feed->expects($this->once())
            ->method('setTitle')
            ->with('title');

        $this->feedService->expects($this->once())
            ->method('find')
            ->with($this->uid, 4)
            ->will($this->returnValue($feed));

        $this->feedService->expects($this->once())
            ->method('update')
            ->with($this->uid, $feed)
            ->will($this->throwException(new ServiceNotFoundException('test')));

        $response = $this->class->patch(4, 2, false, 1, 1, 1, 'title');
        $params = json_decode($response->render(), true);

        $this->assertEquals('test', $params['message']);
        $this->assertEquals($response->getStatus(), Http::STATUS_NOT_FOUND);
    }
}
