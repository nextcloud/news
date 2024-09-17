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

use OCA\News\Controller\FolderApiController;
use OCA\News\Service\FolderServiceV2;
use OCA\News\Service\ItemService;
use \OCP\AppFramework\Http;

use \OCA\News\Service\Exceptions\ServiceNotFoundException;
use \OCA\News\Service\Exceptions\ServiceConflictException;
use \OCA\News\Service\Exceptions\ServiceValidationException;

use \OCA\News\Db\Folder;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;

use PHPUnit\Framework\TestCase;

class FolderApiControllerTest extends TestCase
{

    private $folderService;
    private $itemService;
    private $class;
    private $user;
    private $msg;

    protected function setUp(): void
    {
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
        $this->folderService = $this->getMockBuilder(FolderServiceV2::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemService = $this->getMockBuilder(ItemService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->class = new FolderApiController(
            $request,
            $userSession,
            $this->folderService,
            $this->itemService
        );
        $this->msg = 'test';
    }


    public function testIndex()
    {
        $folders = [new Folder()];

        $this->folderService->expects($this->once())
            ->method('findAllForUser')
            ->with($this->equalTo($this->user->getUID()))
            ->will($this->returnValue($folders));

        $response = $this->class->index();

        $this->assertEquals(
            [
            'folders' => [$folders[0]->toAPI()]
            ],
            $response
        );
    }


    public function testCreate()
    {
        $folderName = 'test';
        $folder = new Folder();
        $folder->setName($folderName);

        $this->folderService->expects($this->once())
            ->method('purgeDeleted');

        $this->folderService->expects($this->once())
            ->method('create')
            ->with($this->user->getUID(), $folderName)
            ->will($this->returnValue($folder));

        $response = $this->class->create($folderName);

        $this->assertEquals(
            [
            'folders' => [$folder->toAPI()]
            ],
            $response
        );
    }


    public function testCreateAlreadyExists()
    {
        $msg = 'exists';

        $this->folderService->expects($this->once())
            ->method('purgeDeleted');

        $this->folderService->expects($this->once())
            ->method('create')
            ->will($this->throwException(new ServiceConflictException($msg)));

        $response = $this->class->create('hi');

        $data = $response->getData();
        $this->assertEquals($msg, $data['message']);
        $this->assertEquals(Http::STATUS_CONFLICT, $response->getStatus());
    }


    public function testCreateInvalidFolderName()
    {
        $msg = 'exists';

        $this->folderService->expects($this->once())
            ->method('purgeDeleted');

        $this->folderService->expects($this->once())
            ->method('create')
            ->will($this->throwException(new ServiceValidationException($msg)));

        $response = $this->class->create('hi');

        $data = $response->getData();
        $this->assertEquals($msg, $data['message']);
        $this->assertEquals(
            Http::STATUS_UNPROCESSABLE_ENTITY,
            $response->getStatus()
        );
    }


    public function testDelete()
    {
        $this->folderService->expects($this->once())
            ->method('delete')
            ->with($this->user->getUID(), 23);

        $this->class->delete(23);
    }


    public function testDeleteDoesNotExist()
    {
        $folderId = 23;

        $this->folderService->expects($this->once())
            ->method('delete')
            ->will(
                $this->throwException(
                    new ServiceNotFoundException($this->msg)
                )
            );

        $response = $this->class->delete($folderId);

        $data = $response->getData();
        $this->assertEquals($this->msg, $data['message']);
        $this->assertEquals(Http::STATUS_NOT_FOUND, $response->getStatus());
    }


    public function testUpdate()
    {
        $folderId = 23;
        $folderName = 'test';

        $this->folderService->expects($this->once())
            ->method('rename')
            ->with($this->user->getUID(), $folderId, $folderName);

        $this->class->update($folderId, $folderName);
    }

    public function testUpdateDoesNotExist()
    {
        $folderId = 23;
        $folderName = 'test';

        $this->folderService->expects($this->once())
            ->method('rename')
            ->will(
                $this->throwException(
                    new ServiceNotFoundException($this->msg)
                )
            );

        $response = $this->class->update($folderId, $folderName);

        $data = $response->getData();
        $this->assertEquals($this->msg, $data['message']);
        $this->assertEquals(Http::STATUS_NOT_FOUND, $response->getStatus());
    }


    public function testUpdateExists()
    {
        $folderId = 23;
        $folderName = 'test';

        $this->folderService->expects($this->once())
            ->method('rename')
            ->will(
                $this->throwException(
                    new ServiceConflictException($this->msg)
                )
            );

        $response = $this->class->update($folderId, $folderName);

        $data = $response->getData();
        $this->assertEquals($this->msg, $data['message']);
        $this->assertEquals(Http::STATUS_CONFLICT, $response->getStatus());
    }


    public function testUpdateInvalidFolderName()
    {
        $folderId = 23;
        $folderName = '';

        $this->folderService->expects($this->once())
            ->method('rename')
            ->will(
                $this->throwException(
                    new ServiceValidationException($this->msg)
                )
            );

        $response = $this->class->update($folderId, $folderName);

        $data = $response->getData();
        $this->assertEquals($this->msg, $data['message']);
        $this->assertEquals(
            Http::STATUS_UNPROCESSABLE_ENTITY,
            $response->getStatus()
        );
    }


    public function testRead()
    {
        $this->folderService->expects($this->once())
            ->method('read')
            ->with($this->user->getUID(), 3, 30);

        $this->class->read(3, 30);
    }

    public function testUpdateRoot()
    {
        $response = $this->class->update(null, '');
        $this->assertSame(400, $response->getStatus());
    }

    public function testDeleteRoot()
    {
        $response = $this->class->delete(null);
        $this->assertSame(400, $response->getStatus());
    }
}
