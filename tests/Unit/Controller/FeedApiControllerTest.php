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

use Exception;
use OCA\News\Controller\FeedApiController;
use OCA\News\Service\FeedServiceV2;
use OCA\News\Service\ItemServiceV2;
use \OCP\AppFramework\Http;

use \OCA\News\Service\Exceptions\ServiceNotFoundException;
use \OCA\News\Service\Exceptions\ServiceConflictException;
use \OCA\News\Db\Feed;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class FeedApiControllerTest extends TestCase
{

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|FeedServiceV2
     */
    private $feedService;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ItemServiceV2
     */
    private $itemService;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|LoggerInterface
     */
    private $logger;

    private $class;
    /**
     * @var string
     */
    private $userID = '123';
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
        $user = $this->getMockBuilder(IUser::class)
            ->disableOriginalConstructor()
            ->getMock();
        $userSession->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($user));
        $user->expects($this->any())
            ->method('getUID')
            ->will($this->returnValue($this->userID));
        $this->feedService = $this->getMockBuilder(FeedServiceV2::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemService = $this->getMockBuilder(ItemServiceV2::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->class = new FeedApiController(
            $request,
            $userSession,
            $this->feedService,
            $this->itemService,
            $this->logger
        );
        $this->msg = 'hohoho';
    }


    public function testIndex()
    {
        $feed = Feed::fromParams(['id' => 5]);
        $feeds = [$feed];

        $this->itemService->expects($this->once())
            ->method('starred')
            ->with($this->equalTo($this->userID))
            ->will($this->returnValue([1, 2, 3]));
        $this->itemService->expects($this->once())
            ->method('newest')
            ->with($this->equalTo($this->userID))
            ->will($this->returnValue($feeds[0]));
        $this->feedService->expects($this->once())
            ->method('findAllForUser')
            ->with($this->equalTo($this->userID))
            ->will($this->returnValue($feeds));

        $response = $this->class->index();

        $this->assertEquals(
            [
                'feeds' => [$feeds[0]->toAPI()],
                'starredCount' => 3,
                'newestItemId' => 5
            ],
            $response
        );
    }


    public function testIndexNoNewestItemId()
    {
        $feeds = [new Feed()];

        $this->itemService->expects($this->once())
            ->method('starred')
            ->with($this->equalTo($this->userID))
            ->will($this->returnValue([1, 2, 3]));
        $this->itemService->expects($this->once())
            ->method('newest')
            ->with($this->equalTo($this->userID))
            ->will($this->throwException(new ServiceNotFoundException('')));
        $this->feedService->expects($this->once())
            ->method('findAllForUser')
            ->with($this->equalTo($this->userID))
            ->will($this->returnValue($feeds));

        $response = $this->class->index();

        $this->assertEquals(
            [
                'feeds' => [$feeds[0]->toAPI()],
                'starredCount' => 3,
            ],
            $response
        );
    }


    public function testDelete()
    {
        $this->feedService->expects($this->once())
            ->method('delete')
            ->with(
                $this->equalTo($this->userID),
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
            ->with($this->userID, 'url', 3)
            ->will($this->returnValue($feeds[0]));

        $this->feedService->expects($this->once())
            ->method('fetch')
            ->with($feeds[0]);
        $this->itemService->expects($this->once())
            ->method('newest')
            ->will($this->returnValue(Feed::fromParams(['id' => 3])));

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
            ->with($this->userID, 'ho', 3)
            ->will($this->returnValue($feeds[0]));

        $this->feedService->expects($this->once())
            ->method('fetch')
            ->with($feeds[0]);
        $this->itemService->expects($this->once())
            ->method('newest')
            ->will($this->throwException(new ServiceNotFoundException('')));

        $response = $this->class->create('ho', 3);

        $this->assertEquals(
            [
                'feeds' => [$feeds[0]->toAPI()]
            ],
            $response
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
        $this->feedService->expects($this->once())
            ->method('read')
            ->with($this->userID, 3, 30);

        $this->class->read(3, 30);
    }


    public function testMove()
    {
        $feed = $this->getMockBuilder(Feed::class)->getMock();
        $feed->expects($this->once())
             ->method('setFolderId')
             ->with(30)
             ->will($this->returnSelf());
        $this->feedService->expects($this->once())
            ->method('find')
            ->with($this->userID, 3)
            ->will($this->returnValue($feed));
        $this->feedService->expects($this->once())
            ->method('update')
            ->with($this->userID, $feed);

        $this->class->move(3, 30);
    }


    public function testMoveDoesNotExist()
    {
        $this->feedService->expects($this->once())
            ->method('update')
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
        $feed = $this->getMockBuilder(Feed::class)->getMock();
        $feed->expects($this->once())
            ->method('setTitle')
            ->with('test')
            ->will($this->returnSelf());
        $this->feedService->expects($this->once())
            ->method('find')
            ->with($this->userID, 3)
            ->will($this->returnValue($feed));
        $this->feedService->expects($this->once())
            ->method('update')
            ->with($this->userID, $feed);

        $this->class->rename(3, 'test');
    }


    public function testRenameError()
    {
        $feedId = 3;
        $feedTitle = 'test';

        $this->feedService->expects($this->once())
            ->method('find')
            ->with($this->userID, 3)
            ->will($this->throwException(new ServiceNotFoundException('hi')));

        $result = $this->class->rename($feedId, $feedTitle);
        $data = $result->getData();
        $code = $result->getStatus();

        $this->assertSame(Http::STATUS_NOT_FOUND, $code);
        $this->assertSame('hi', $data['message']);
    }


    public function testFromAllUsers()
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
            ->will($this->throwException(new Exception($this->msg)));

        $this->logger->expects($this->once())
            ->method('debug')
            ->with('Could not update feed ' . $this->msg);

        $this->class->update($userId, $feedId);
    }
}
