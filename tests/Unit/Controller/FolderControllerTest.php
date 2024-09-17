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
use OCA\News\Service\FeedServiceV2;
use OCA\News\Service\FolderServiceV2;
use OCA\News\Service\ItemService;
use \OCP\AppFramework\Http;

use \OCA\News\Db\Folder;
use \OCA\News\Db\Feed;
use \OCA\News\Service\Exceptions\ServiceNotFoundException;
use \OCA\News\Service\Exceptions\ServiceConflictException;
use \OCA\News\Service\Exceptions\ServiceValidationException;
use OCP\IRequest;

use OCP\IUser;
use OCP\IUserSession;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FolderControllerTest extends TestCase
{

    /**
     * @var MockObject|FolderServiceV2
     */
    private $folderService;
    private $itemService;
    /**
     * @var MockObject|FeedServiceV2
     */
    private $feedService;
    /**
     * @var MockObject|IUser
     */
    private $user;
    private $userSession;
    private $class;
    private $msg;


    /**
     * Gets run before each test
     */
    public function setUp(): void
    {
        $appName = 'news';
        $this->user = $this->getMockBuilder(IUser::class)
                           ->getMock();
        $this->folderService = $this->getMockBuilder(FolderServiceV2::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->feedService = $this->getMockBuilder(FeedServiceV2::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemService = $this->getMockBuilder(ItemService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $request = $this->getMockBuilder(IRequest::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->user = $this->getMockBuilder(IUser::class)->getMock();
        $this->user->expects($this->any())
            ->method('getUID')
            ->will($this->returnValue('jack'));
        $this->userSession = $this->getMockBuilder(IUserSession::class)
            ->getMock();
        $this->userSession->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($this->user));
        $this->class = new FolderController(
            $request,
            $this->folderService,
            $this->userSession
        );
        $this->msg = 'ron';
    }

    public function testIndex()
    {
        $return = [new Folder(), new Folder()];
        $this->folderService->expects($this->once())
            ->method('findAllForUser')
            ->will($this->returnValue($return));

        $response = $this->class->index();
        $expected = ['folders' => [$return[0]->toAPI(), $return[1]->toAPI()]];
        $this->assertEquals($expected, $response);
    }

    public function testOpen()
    {
        $this->folderService->expects($this->once())
            ->method('open')
            ->with('jack', 3, true);

        $this->class->open(3, true);
    }

    public function testOpenDoesNotExist()
    {
        $this->folderService->expects($this->once())
            ->method('open')
            ->will($this->throwException(new ServiceNotFoundException($this->msg)));

        $response = $this->class->open(5, true);

        $params = json_decode($response->render(), true);

        $this->assertEquals($this->msg, $params['message']);
        $this->assertEquals($response->getStatus(), Http::STATUS_NOT_FOUND);
    }


    public function testCollapse()
    {
        $this->folderService->expects($this->once())
            ->method('open')
            ->with('jack', 5, false);

        $this->class->open(5, false);
    }

    public function testCreate()
    {
        $folder = new Folder();
        $result = ['folders' => [$folder->toAPI()]];

        $this->folderService->expects($this->once())
            ->method('purgeDeleted');
        $this->folderService->expects($this->once())
            ->method('create')
            ->with('jack', 'tech')
            ->will($this->returnValue($folder));

        $response = $this->class->create('tech');

        $this->assertEquals($result, $response);
    }

    public function testDelete()
    {
        $this->folderService->expects($this->once())
            ->method('markDelete')
            ->with('jack', 5, true);

        $this->class->delete(5);
    }

    public function testDeleteRoot()
    {
        $this->folderService->expects($this->never())
            ->method('markDelete')
            ->with('jack', 5, true);

        $response = $this->class->delete(null);
        $this->assertEquals(400, $response->getStatus());
    }

    public function testDeleteDoesNotExist()
    {
        $this->folderService->expects($this->once())
            ->method('markDelete')
            ->will($this->throwException(new ServiceNotFoundException($this->msg)));

        $response = $this->class->delete(5);

        $params = json_decode($response->render(), true);

        $this->assertEquals($this->msg, $params['message']);
        $this->assertEquals($response->getStatus(), Http::STATUS_NOT_FOUND);
    }

    public function testDeleteConflict()
    {
        $this->folderService->expects($this->once())
            ->method('markDelete')
            ->will($this->throwException(new ServiceConflictException($this->msg)));

        $response = $this->class->delete(5);

        $params = json_decode($response->render(), true);

        $this->assertEquals($this->msg, $params['message']);
        $this->assertEquals($response->getStatus(), Http::STATUS_CONFLICT);
    }

    public function testRename()
    {
        $folder = new Folder();
        $result = ['folders' => [$folder->toAPI()]];

        $this->folderService->expects($this->once())
            ->method('rename')
            ->with('jack', 4, 'tech')
            ->will($this->returnValue($folder));

        $response = $this->class->rename(4, 'tech');

        $this->assertEquals($result, $response);
    }

    public function testRenameRoot()
    {
        $this->folderService->expects($this->never())
            ->method('rename');

        $response = $this->class->rename(null, 'tech');

        $this->assertEquals(400, $response->getStatus());
    }

    public function testRenameDoesNotExist()
    {
        $msg = 'except';
        $ex = new ServiceNotFoundException($msg);
        $this->folderService->expects($this->once())
            ->method('rename')
            ->will($this->throwException($ex));

        $response = $this->class->rename(5, 'tech');
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

        $response = $this->class->rename(1, 'tech');
        $params = json_decode($response->render(), true);

        $this->assertEquals($response->getStatus(), Http::STATUS_CONFLICT);
        $this->assertEquals($msg, $params['message']);
    }



    public function testRead()
    {
        $this->folderService->expects($this->once())
            ->method('read')
            ->with('jack', 4, 5);

        $this->class->read(4, 5);
    }


    public function testRestore()
    {
        $this->folderService->expects($this->once())
            ->method('markDelete')
            ->with('jack', 5, false);

        $this->class->restore(5);
    }


    public function testRestoreDoesNotExist()
    {
        $this->folderService->expects($this->once())
            ->method('markDelete')
            ->with('jack', 5, false)
            ->will($this->throwException(new ServiceNotFoundException($this->msg)));

        $response = $this->class->restore(5);

        $params = json_decode($response->render(), true);

        $this->assertEquals(Http::STATUS_NOT_FOUND, $response->getStatus());
        $this->assertEquals($this->msg, $params['message']);
    }


    public function testRestoreConflict()
    {
        $this->folderService->expects($this->once())
            ->method('markDelete')
            ->with('jack', 5, false)
            ->will($this->throwException(new ServiceConflictException($this->msg)));

        $response = $this->class->restore(5);

        $params = json_decode($response->render(), true);

        $this->assertEquals(Http::STATUS_CONFLICT, $response->getStatus());
        $this->assertEquals($this->msg, $params['message']);
    }
}
