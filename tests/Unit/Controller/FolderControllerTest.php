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

use OCA\News\Controller\FolderController;
use OCA\News\Service\FeedService;
use OCA\News\Service\FolderService;
use OCA\News\Service\ItemService;
use \OCP\AppFramework\Http;

use \OCA\News\Db\Folder;
use \OCA\News\Db\Feed;
use \OCA\News\Service\Exceptions\ServiceNotFoundException;
use \OCA\News\Service\Exceptions\ServiceConflictException;
use \OCA\News\Service\Exceptions\ServiceValidationException;
use OCP\IRequest;

use PHPUnit\Framework\TestCase;


class FolderControllerTest extends TestCase
{

    private $folderService;
    private $itemService;
    private $feedService;
    private $controller;
    private $msg;


    /**
     * Gets run before each test
     */
    public function setUp(): void
    {
        $appName = 'news';
        $this->user = 'jack';
        $this->folderService = $this->getMockBuilder(FolderService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->feedService = $this->getMockBuilder(FeedService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemService = $this->getMockBuilder(ItemService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $request = $this->getMockBuilder(IRequest::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->controller = new FolderController(
            $appName, $request,
            $this->folderService,
            $this->feedService,
            $this->itemService,
            $this->user
        );
        $this->msg = 'ron';
    }

    public function testIndex()
    {
        $return = [new Folder(), new Folder()];
        $this->folderService->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue($return));

        $response = $this->controller->index();
        $expected = ['folders' => $return];
        $this->assertEquals($expected, $response);
    }

    public function testOpen()
    {
        $this->folderService->expects($this->once())
            ->method('open')
            ->with(
                $this->equalTo(3),
                $this->equalTo(true), $this->equalTo($this->user)
            );

        $this->controller->open(3, true);

    }


    public function testOpenDoesNotExist()
    {
        $this->folderService->expects($this->once())
            ->method('open')
            ->will(
                $this->throwException(
                    new ServiceNotFoundException($this->msg)
                )
            );

        $response = $this->controller->open(5, true);

        $params = json_decode($response->render(), true);

        $this->assertEquals($this->msg, $params['message']);
        $this->assertEquals($response->getStatus(), Http::STATUS_NOT_FOUND);
    }


    public function testCollapse()
    {
        $this->folderService->expects($this->once())
            ->method('open')
            ->with(
                $this->equalTo(5),
                $this->equalTo(false), $this->equalTo($this->user)
            );

        $this->controller->open(5, false);

    }


    public function testCreate()
    {
        $result = ['folders' => [new Folder()]];

        $this->folderService->expects($this->once())
            ->method('purgeDeleted')
            ->with($this->equalTo($this->user), $this->equalTo(false));
        $this->folderService->expects($this->once())
            ->method('create')
            ->with(
                $this->equalTo('tech'),
                $this->equalTo($this->user)
            )
            ->will($this->returnValue($result['folders'][0]));

        $response = $this->controller->create('tech');

        $this->assertEquals($result, $response);
    }


    public function testCreateReturnsErrorForInvalidCreate()
    {
        $msg = 'except';
        $ex = new ServiceValidationException($msg);
        $this->folderService->expects($this->once())
            ->method('purgeDeleted')
            ->with($this->equalTo($this->user), $this->equalTo(false));
        $this->folderService->expects($this->once())
            ->method('create')
            ->will($this->throwException($ex));

        $response = $this->controller->create('tech');
        $params = json_decode($response->render(), true);

        $this->assertEquals(
            $response->getStatus(),
            Http::STATUS_UNPROCESSABLE_ENTITY
        );
        $this->assertEquals($msg, $params['message']);
    }


    public function testCreateReturnsErrorForDuplicateCreate()
    {
        $msg = 'except';
        $ex = new ServiceConflictException($msg);
        $this->folderService->expects($this->once())
            ->method('purgeDeleted')
            ->with($this->equalTo($this->user), $this->equalTo(false));
        $this->folderService->expects($this->once())
            ->method('create')
            ->will($this->throwException($ex));

        $response = $this->controller->create('tech');
        $params = json_decode($response->render(), true);

        $this->assertEquals($response->getStatus(), Http::STATUS_CONFLICT);
        $this->assertEquals($msg, $params['message']);
    }


    public function testDelete()
    {
        $this->folderService->expects($this->once())
            ->method('markDeleted')
            ->with(
                $this->equalTo(5),
                $this->equalTo($this->user)
            );

        $this->controller->delete(5);
    }


    public function testDeleteDoesNotExist()
    {
        $this->folderService->expects($this->once())
            ->method('markDeleted')
            ->will(
                $this->throwException(new ServiceNotFoundException($this->msg))
            );

        $response = $this->controller->delete(5);

        $params = json_decode($response->render(), true);

        $this->assertEquals($this->msg, $params['message']);
        $this->assertEquals($response->getStatus(), Http::STATUS_NOT_FOUND);
    }


    public function testRename()
    {
        $result = ['folders' => [new Folder()]];

        $this->folderService->expects($this->once())
            ->method('rename')
            ->with(
                $this->equalTo(4),
                $this->equalTo('tech'),
                $this->equalTo($this->user)
            )
            ->will($this->returnValue($result['folders'][0]));

        $response = $this->controller->rename('tech', 4);

        $this->assertEquals($result, $response);
    }


    public function testRenameReturnsErrorForInvalidCreate()
    {
        $msg = 'except';
        $ex = new ServiceValidationException($msg);
        $this->folderService->expects($this->once())
            ->method('rename')
            ->will($this->throwException($ex));

        $response = $this->controller->rename('tech', 4);
        $params = json_decode($response->render(), true);

        $this->assertEquals(
            $response->getStatus(),
            Http::STATUS_UNPROCESSABLE_ENTITY
        );
        $this->assertEquals($msg, $params['message']);
    }


    public function testRenameDoesNotExist()
    {
        $msg = 'except';
        $ex = new ServiceNotFoundException($msg);
        $this->folderService->expects($this->once())
            ->method('rename')
            ->will($this->throwException($ex));

        $response = $this->controller->rename('tech', 5);
        $params = json_decode($response->render(), true);

        $this->assertEquals($response->getStatus(), Http::STATUS_NOT_FOUND);
        $this->assertEquals($msg, $params['message']);
    }


    public function testRenameReturnsErrorForDuplicateCreate()
    {
        $msg = 'except';
        $ex = new ServiceConflictException($msg);
        $this->folderService->expects($this->once())
            ->method('rename')
            ->will($this->throwException($ex));

        $response = $this->controller->rename('tech', 1);
        $params = json_decode($response->render(), true);

        $this->assertEquals($response->getStatus(), Http::STATUS_CONFLICT);
        $this->assertEquals($msg, $params['message']);
    }



    public function testRead()
    {
        $feed = new Feed();
        $expected = ['feeds' => [$feed]];

        $this->itemService->expects($this->once())
            ->method('readFolder')
            ->with(
                $this->equalTo(4),
                $this->equalTo(5),
                $this->equalTo($this->user)
            );
        $this->feedService->expects($this->once())
            ->method('findAll')
            ->with($this->equalTo($this->user))
            ->will($this->returnValue([$feed]));

        $response = $this->controller->read(4, 5);
        $this->assertEquals($expected, $response);
    }


    public function testRestore()
    {
        $this->folderService->expects($this->once())
            ->method('unmarkDeleted')
            ->with(
                $this->equalTo(5),
                $this->equalTo($this->user)
            );

        $this->controller->restore(5);
    }


    public function testRestoreDoesNotExist()
    {
        $this->folderService->expects($this->once())
            ->method('unmarkDeleted')
            ->will(
                $this->throwException(new ServiceNotFoundException($this->msg))
            );

        $response = $this->controller->restore(5);

        $params = json_decode($response->render(), true);

        $this->assertEquals($response->getStatus(), Http::STATUS_NOT_FOUND);
        $this->assertEquals($this->msg, $params['message']);
    }

}