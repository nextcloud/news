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
use OCA\News\Service\ItemService;
use \OCP\AppFramework\Http;

use \OCA\News\Service\ServiceNotFoundException;
use \OCA\News\Service\ServiceConflictException;
use \OCA\News\Db\Feed;
use OCP\ILogger;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;

use PHPUnit\Framework\TestCase;

class FeedApiControllerTest extends TestCase
{

    private $feedService;
    private $itemService;
    private $feedAPI;
    private $appName;
    private $userSession;
    private $user;
    private $request;
    private $msg;
    private $logger;
    private $loggerParams;

    protected function setUp() 
    {
        $this->loggerParams = ['hi'];
        $this->logger = $this->getMockBuilder(ILogger::class)
            ->disableOriginalConstructor()
            ->getMock();
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
        $this->feedService = $this->getMockBuilder(FeedService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemService = $this->getMockBuilder(ItemService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->feedAPI = new FeedApiController(
            $this->appName,
            $this->request,
            $this->userSession,
            $this->feedService,
            $this->itemService,
            $this->logger,
            $this->loggerParams
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
            ->method('findAll')
            ->with($this->equalTo($this->user->getUID()))
            ->will($this->returnValue($feeds));

        $response = $this->feedAPI->index();

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
            ->method('findAll')
            ->with($this->equalTo($this->user->getUID()))
            ->will($this->returnValue($feeds));

        $response = $this->feedAPI->index();

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
                $this->equalTo(2),
                $this->equalTo($this->user->getUID())
            );

        $this->feedAPI->delete(2);
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

        $response = $this->feedAPI->delete(2);

        $data = $response->getData();
        $this->assertEquals($this->msg, $data['message']);
        $this->assertEquals(Http::STATUS_NOT_FOUND, $response->getStatus());
    }


    public function testCreate() 
    {
        $feeds = [new Feed()];

        $this->feedService->expects($this->once())
            ->method('purgeDeleted')
            ->with($this->equalTo($this->user->getUID()), $this->equalTo(false));
        $this->feedService->expects($this->once())
            ->method('create')
            ->with(
                $this->equalTo('url'),
                $this->equalTo(3),
                $this->equalTo($this->user->getUID())
            )
            ->will($this->returnValue($feeds[0]));
        $this->itemService->expects($this->once())
            ->method('getNewestItemId')
            ->will($this->returnValue(3));

        $response = $this->feedAPI->create('url', 3);

        $this->assertEquals(
            [
            'feeds' => [$feeds[0]->toAPI()],
            'newestItemId' => 3
            ], $response
        );
    }


    public function testCreateNoItems() 
    {
        $feeds = [new Feed()];

        $this->feedService->expects($this->once())
            ->method('purgeDeleted')
            ->with($this->equalTo($this->user->getUID()), $this->equalTo(false));
        $this->feedService->expects($this->once())
            ->method('create')
            ->with(
                $this->equalTo('ho'),
                $this->equalTo(3),
                $this->equalTo($this->user->getUID())
            )
            ->will($this->returnValue($feeds[0]));
        $this->itemService->expects($this->once())
            ->method('getNewestItemId')
            ->will($this->throwException(new ServiceNotFoundException('')));

        $response = $this->feedAPI->create('ho', 3);

        $this->assertEquals(
            [
            'feeds' => [$feeds[0]->toAPI()]
            ], $response
        );
    }



    public function testCreateExists() 
    {
        $this->feedService->expects($this->once())
            ->method('purgeDeleted')
            ->with($this->equalTo($this->user->getUID()), $this->equalTo(false));
        $this->feedService->expects($this->once())
            ->method('create')
            ->will(
                $this->throwException(new ServiceConflictException($this->msg))
            );

        $response = $this->feedAPI->create('ho', 3);

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

        $response = $this->feedAPI->create('ho', 3);

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

        $this->feedAPI->read(3, 30);
    }


    public function testMove() 
    {
        $this->feedService->expects($this->once())
            ->method('patch')
            ->with(
                $this->equalTo(3),
                $this->equalTo($this->user->getUID()),
                $this->equalTo(['folderId' => 30])
            );

        $this->feedAPI->move(3, 30);
    }


    public function testMoveDoesNotExist() 
    {
        $this->feedService->expects($this->once())
            ->method('patch')
            ->will(
                $this->throwException(new ServiceNotFoundException($this->msg))
            );

        $response = $this->feedAPI->move(3, 4);

        $data = $response->getData();
        $this->assertEquals($this->msg, $data['message']);
        $this->assertEquals(Http::STATUS_NOT_FOUND, $response->getStatus());
    }


    public function testRename() 
    {
        $feedId = 3;
        $feedTitle = 'test';

        $this->feedService->expects($this->once())
            ->method('patch')
            ->with(
                $this->equalTo($feedId),
                $this->equalTo($this->user->getUID()),
                $this->equalTo(['title' => $feedTitle])
            );

        $this->feedAPI->rename($feedId, $feedTitle);
    }


    public function testRenameError() 
    {
        $feedId = 3;
        $feedTitle = 'test';

        $this->feedService->expects($this->once())
            ->method('patch')
            ->with(
                $this->equalTo($feedId),
                $this->equalTo($this->user->getUID()),
                $this->equalTo(['title' => $feedTitle])
            )
            ->will($this->throwException(new ServiceNotFoundException('hi')));

        $result = $this->feedAPI->rename($feedId, $feedTitle);
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
            ->method('findAllFromAllUsers')
            ->will($this->returnValue($feeds));
        $response = json_encode($this->feedAPI->fromAllUsers());
        $this->assertEquals('{"feeds":[{"id":1,"userId":"john"}]}', $response);
    }


    public function testUpdate() 
    {
        $feedId = 3;
        $userId = 'hi';

        $this->feedService->expects($this->once())
            ->method('update')
            ->with($this->equalTo($feedId), $this->equalTo($userId));

        $this->feedAPI->update($userId, $feedId);
    }


    public function testUpdateError() 
    {
        $feedId = 3;
        $userId = 'hi';
        $this->feedService->expects($this->once())
            ->method('update')
            ->will($this->throwException(new \Exception($this->msg)));
        $this->logger->expects($this->once())
            ->method('debug')
            ->with(
                $this->equalTo('Could not update feed ' . $this->msg),
                $this->equalTo($this->loggerParams)
            );

        $this->feedAPI->update($userId, $feedId);


    }


}
