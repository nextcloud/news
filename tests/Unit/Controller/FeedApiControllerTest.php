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

use OCA\News\Controller\FeedApiController;
use OCA\News\Service\FeedService;
use OCA\News\Service\FeedServiceV2;
use OCA\News\Service\ItemService;
use OCA\News\Service\ItemServiceV2;
use OCA\News\Utility\PsrLogger;
use \OCP\AppFramework\Http;

use \OCA\News\Service\Exceptions\ServiceNotFoundException;
use \OCA\News\Service\Exceptions\ServiceConflictException;
use \OCA\News\Db\Feed;
use OCP\ILogger;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class FeedApiControllerTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|FeedService
     */
    private $oldFeedService;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|FeedServiceV2
     */
    private $feedService;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ItemService
     */
    private $itemService;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|LoggerInterface
     */
    private $logger;

    private $class;
    private $user;
    private $msg;

    protected function setUp(): void
    {
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $appName = 'news';
        $request = $this->getMockBuilder(IRequest::class)
            ->disableOriginalConstructor()
            ->getMock();
        $userSession = $this->getMockBuilder(IUserSession::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->user = $this->getMockBuilder(IUser::class)
            ->disableOriginalConstructor()
            ->getMock();
        $userSession->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($this->user));
        $this->user->expects($this->any())
            ->method('getUID')
            ->will($this->returnValue('123'));
        $this->oldFeedService = $this->getMockBuilder(FeedService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->feedService = $this->getMockBuilder(FeedServiceV2::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemService = $this->getMockBuilder(ItemService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->class = new FeedApiController(
            $appName,
            $request,
            $userSession,
            $this->oldFeedService,
            $this->feedService,
            $this->itemService,
            $this->logger
        );
        $this->msg = 'hohoho';
    }


    public function testIndex()
    {
        $feeds = [new Feed()];
        $starredCount = 3;
        $newestItemId = 2;

        $this->itemService->expects($this->once())
            ->method('starredCount')
            ->with($this->equalTo($this->user->getUID()))
            ->will($this->returnValue($starredCount));
        $this->itemService->expects($this->once())
            ->method('getNewestItemId')
            ->with($this->equalTo($this->user->getUID()))
            ->will($this->returnValue($newestItemId));
        $this->feedService->expects($this->once())
            ->method('findAllForUser')
            ->with($this->equalTo($this->user->getUID()))
            ->will($this->returnValue($feeds));

        $response = $this->class->index();

        $this->assertEquals(
            [
            'feeds' => [$feeds[0]->toAPI()],
            'starredCount' => $starredCount,
            'newestItemId' => $newestItemId
            ], $response
        );
    }


    public function testIndexNoNewestItemId()
    {
        $feeds = [new Feed()];
        $starredCount = 3;

        $this->itemService->expects($this->once())
            ->method('starredCount')
            ->with($this->equalTo($this->user->getUID()))
            ->will($this->returnValue($starredCount));
        $this->itemService->expects($this->once())
            ->method('getNewestItemId')
            ->with($this->equalTo($this->user->getUID()))
            ->will($this->throwException(new ServiceNotFoundException('')));
        $this->feedService->expects($this->once())
            ->method('findAllForUser')
            ->with($this->equalTo($this->user->getUID()))
            ->will($this->returnValue($feeds));

        $response = $this->class->index();

        $this->assertEquals(
            [
            'feeds' => [$feeds[0]->toAPI()],
            'starredCount' => $starredCount,
            ], $response
        );
    }


    public function testDelete()
    {
        $this->feedService->expects($this->once())
            ->method('delete')
            ->with(
                $this->equalTo($this->user->getUID()),
                $this->equalTo(2)
            );

        $this->class->delete(2);
    }


    public function testDeleteDoesNotExist()
    {
        $this->feedService->expects($this->once())
            ->method('delete')
            ->will(
                $this->throwException(
                    new ServiceNotFoundException($this->msg)
                )
            );

        $response = $this->class->delete(2);

        $data = $response->getData();
        $this->assertEquals($this->msg, $data['message']);
        $this->assertEquals(Http::STATUS_NOT_FOUND, $response->getStatus());
    }


    public function testCreate()
    {
        $feeds = [new Feed()];

        $this->feedService->expects($this->once())
            ->method('purgeDeleted');

        $this->feedService->expects($this->once())
            ->method('create')
            ->with($this->user->getUID(), 'url', 3)
            ->will($this->returnValue($feeds[0]));
        $this->itemService->expects($this->once())
            ->method('getNewestItemId')
            ->will($this->returnValue(3));

        $response = $this->class->create('url', 3);

        $this->assertEquals(
            [
                'feeds' => [$feeds[0]->toAPI()],
                'newestItemId' => 3
            ],
            $response
        );
    }


    public function testCreateNoItems()
    {
        $feeds = [new Feed()];

        $this->feedService->expects($this->once())
            ->method('purgeDeleted');

        $this->feedService->expects($this->once())
            ->method('create')
            ->with($this->user->getUID(), 'ho', 3)
            ->will($this->returnValue($feeds[0]));
        $this->itemService->expects($this->once())
            ->method('getNewestItemId')
            ->will($this->throwException(new ServiceNotFoundException('')));

        $response = $this->class->create('ho', 3);

        $this->assertEquals(
            [
            'feeds' => [$feeds[0]->toAPI()]
            ], $response
        );
    }



    public function testCreateExists()
    {
        $this->feedService->expects($this->once())
            ->method('purgeDeleted');

        $this->feedService->expects($this->once())
            ->method('create')
            ->will(
                $this->throwException(new ServiceConflictException($this->msg))
            );

        $response = $this->class->create('ho', 3);

        $data = $response->getData();
        $this->assertEquals($this->msg, $data['message']);
        $this->assertEquals(Http::STATUS_CONFLICT, $response->getStatus());
    }


    public function testCreateError()
    {
        $this->feedService->expects($this->once())
            ->method('create')
            ->will(
                $this->throwException(new ServiceNotFoundException($this->msg))
            );

        $response = $this->class->create('ho', 3);

        $data = $response->getData();
        $this->assertEquals($this->msg, $data['message']);
        $this->assertEquals(Http::STATUS_NOT_FOUND, $response->getStatus());
    }


    public function testRead()
    {
        $this->itemService->expects($this->once())
            ->method('readFeed')
            ->with(
                $this->equalTo(3),
                $this->equalTo(30),
                $this->equalTo($this->user->getUID())
            );

        $this->class->read(3, 30);
    }


    public function testMove()
    {
        $this->oldFeedService->expects($this->once())
            ->method('patch')
            ->with(
                $this->equalTo(3),
                $this->equalTo($this->user->getUID()),
                $this->equalTo(['folderId' => 30])
            );

        $this->class->move(3, 30);
    }


    public function testMoveDoesNotExist()
    {
        $this->oldFeedService->expects($this->once())
            ->method('patch')
            ->will(
                $this->throwException(new ServiceNotFoundException($this->msg))
            );

        $response = $this->class->move(3, 4);

        $data = $response->getData();
        $this->assertEquals($this->msg, $data['message']);
        $this->assertEquals(Http::STATUS_NOT_FOUND, $response->getStatus());
    }


    public function testRename()
    {
        $feedId = 3;
        $feedTitle = 'test';

        $this->oldFeedService->expects($this->once())
            ->method('patch')
            ->with(
                $this->equalTo($feedId),
                $this->equalTo($this->user->getUID()),
                $this->equalTo(['title' => $feedTitle])
            );

        $this->class->rename($feedId, $feedTitle);
    }


    public function testRenameError()
    {
        $feedId = 3;
        $feedTitle = 'test';

        $this->oldFeedService->expects($this->once())
            ->method('patch')
            ->with(
                $this->equalTo($feedId),
                $this->equalTo($this->user->getUID()),
                $this->equalTo(['title' => $feedTitle])
            )
            ->will($this->throwException(new ServiceNotFoundException('hi')));

        $result = $this->class->rename($feedId, $feedTitle);
        $data = $result->getData();
        $code = $result->getStatus();

        $this->assertSame(Http::STATUS_NOT_FOUND, $code);
        $this->assertSame('hi', $data['message']);
    }


    public function testfromAllUsers()
    {
        $feed = new Feed();
        $feed->setUrl(3);
        $feed->setId(1);
        $feed->setUserId('john');
        $feeds = [$feed];
        $this->feedService->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue($feeds));
        $response = json_encode($this->class->fromAllUsers());
        $this->assertEquals('{"feeds":[{"id":1,"userId":"john"}]}', $response);
    }


    public function testUpdate()
    {
        $feedId = 3;
        $userId = 'hi';
        $feed = new Feed();

        $this->feedService->expects($this->once())
            ->method('find')
            ->with($userId, $feedId)
            ->willReturn($feed);

        $this->feedService->expects($this->once())
            ->method('fetch')
            ->with($feed);

        $this->class->update($userId, $feedId);
    }


    public function testUpdateError()
    {
        $feedId = 3;
        $userId = 'hi';
        $this->feedService->expects($this->once())
            ->method('find')
            ->will($this->throwException(new \Exception($this->msg)));

        $this->logger->expects($this->once())
            ->method('debug')
            ->with('Could not update feed ' . $this->msg);

        $this->class->update($userId, $feedId);
    }

}
