<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Paul Tirk <paultirk@paultirk.com>
 * @copyright 2020 Paul Tirk
 */

namespace OCA\News\Tests\Unit\Controller;

use OCA\News\Controller\FolderApiV2Controller;
use OCA\News\Service\FolderServiceV2;
use OCA\News\Service\ItemServiceV2;
use \OCP\AppFramework\Http;

use \OCA\News\Service\Exceptions\ServiceNotFoundException;
use \OCA\News\Service\Exceptions\ServiceConflictException;
use \OCA\News\Service\Exceptions\ServiceValidationException;

use \OCA\News\Db\Folder;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;

use PHPUnit\Framework\TestCase;

class FolderApiV2ControllerTest extends TestCase
{

    private $folderService;
    private $itemService;
    private $folderAPI;
    private $userSession;
    private $user;
    private $request;
    private $msg;

    protected function setUp(): void
    {
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
        $this->folderService = $this->getMockBuilder(FolderServiceV2::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemService = $this->getMockBuilder(ItemServiceV2::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->folderAPI = new FolderApiV2Controller(
            $this->request,
            $this->userSession,
            $this->folderService,
            $this->itemService
        );
        $this->msg = 'test';
    }

    public function testCreate()
    {
        $folderName = 'test';
        $folder = new Folder();
        $folder->setName($folderName);

        $this->folderService->expects($this->once())
            ->method('purgeDeleted')
            ->with($this->equalTo($this->user->getUID()), $this->equalTo(false));
        $this->folderService->expects($this->once())
            ->method('create')
            ->with($this->equalTo($this->user->getUID()), $this->equalTo($folderName))
            ->will($this->returnValue($folder));

        $response = $this->folderAPI->createFolder($folderName);

        $data = $response->getData();
        $this->assertEquals(
            [
                'folder' => $folder->toAPI2()
            ],
            $data
        );
    }


    public function testCreateAlreadyExists()
    {
        $this->folderService->expects($this->once())
            ->method('purgeDeleted')
            ->with($this->equalTo($this->user->getUID()), $this->equalTo(false));
        $this->folderService->expects($this->once())
            ->method('create')
            ->will($this->throwException(new ServiceConflictException('exists')));

        $response = $this->folderAPI->createFolder('hi');

        $this->assertEquals(Http::STATUS_CONFLICT, $response->getStatus());
    }


    public function testCreateInvalidFolderName()
    {
        $msg = 'exists';

        $this->folderService->expects($this->once())
            ->method('purgeDeleted')
            ->with($this->equalTo($this->user->getUID()), $this->equalTo(false));
        $this->folderService->expects($this->once())
            ->method('create')
            ->will($this->throwException(new ServiceValidationException($msg)));

        $response = $this->folderAPI->createFolder('hi');

        $data = $response->getData();
        $this->assertEquals($msg, $data['error']['message']);
        $this->assertEquals(
            Http::STATUS_BAD_REQUEST, $response->getStatus()
        );
    }


    public function testDelete()
    {
        $folderId = 23;
        $folder = new Folder();

        $this->folderService->expects($this->once())
            ->method('delete')
            ->with($this->equalTo($this->user->getUID()), $this->equalTo($folderId))
            ->will($this->returnValue($folder));

        $response = $this->folderAPI->deleteFolder(23);

        $data = $response->getData();
        $this->assertEquals(
            [
                'folder' => $folder->toAPI2()
            ],
            $data
        );
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

        $response = $this->folderAPI->deleteFolder($folderId);

        $data = $response->getData();
        $this->assertEquals($this->msg, $data['error']['message']);
        $this->assertEquals(Http::STATUS_NOT_FOUND, $response->getStatus());
    }


    public function testUpdate()
    {
        $folderId = 23;
        $folderName = 'test';

        $this->folderService->expects($this->once())
            ->method('rename')
            ->with(
                $this->equalTo($this->user->getUID()),
                $this->equalTo($folderId),
                $this->equalTo($folderName)
            );

        $this->folderAPI->updateFolder($folderId, $folderName);
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

        $response = $this->folderAPI->updateFolder($folderId, $folderName);

        $data = $response->getData();
        $this->assertEquals($this->msg, $data['error']['message']);
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

        $response = $this->folderAPI->updateFolder($folderId, $folderName);

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

        $response = $this->folderAPI->updateFolder($folderId, $folderName);

        $data = $response->getData();
        $this->assertEquals($this->msg, $data['error']['message']);
        $this->assertEquals(
            Http::STATUS_UNPROCESSABLE_ENTITY, $response->getStatus()
        );
    }

}
