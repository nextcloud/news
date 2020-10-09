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

namespace OCA\News\Tests\Unit\Service;

use OC\L10N\L10N;
use \OCA\News\Db\Folder;
use OCA\News\Db\FolderMapper;
use OCA\News\Service\FolderService;
use OCA\News\Service\Exceptions\ServiceConflictException;
use OCA\News\Service\Exceptions\ServiceValidationException;
use OCA\News\Utility\Time;
use OCP\IConfig;
use OCP\IL10N;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;


class FolderServiceTest extends TestCase
{

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|FolderMapper
     */
    private $folderMapper;

    /**
     * @var FolderService
     */
    private $folderService;

    /**
     * @var int
     */
    private $time;

    /**
     * @var string
     */
    private $user;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|LoggerInterface
     */
    private $logger;

    /**
     * @var int
     */
    private $autoPurgeMinimumInterval;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|L10N
     */
    private $l10n;

    protected function setUp(): void
    {
        $this->l10n = $this->getMockBuilder(IL10N::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->time = 222;
        $timeFactory = $this->getMockBuilder(Time::class)
            ->disableOriginalConstructor()
            ->getMock();
        $timeFactory->expects($this->any())
            ->method('getTime')
            ->will($this->returnValue($this->time));
        $this->folderMapper = $this->getMockBuilder(FolderMapper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->autoPurgeMinimumInterval = 10;
        $config = $this->getMockBuilder(IConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $config->expects($this->any())
            ->method('getAppValue')
            ->with('news', 'autoPurgeMinimumInterval')
            ->will($this->returnValue($this->autoPurgeMinimumInterval));
        $this->folderService = new FolderService(
            $this->folderMapper, $this->l10n, $timeFactory, $config, $this->logger
        );
        $this->user = 'hi';
    }


    public function testFindAll()
    {
        $userId = 'jack';
        $return = [];
        $this->folderMapper->expects($this->once())
            ->method('findAllFromUser')
            ->with($this->equalTo($userId))
            ->will($this->returnValue($return));

        $result = $this->folderService->findAllForUser($userId);

        $this->assertEquals($return, $result);
    }


    public function testCreate()
    {
        $folder = new Folder();
        $folder->setName('hey');
        $folder->setParentId(5);
        $folder->setUserId('john');
        $folder->setOpened(true);

        $this->folderMapper->expects($this->once())
            ->method('insert')
            ->with($this->equalTo($folder))
            ->will($this->returnValue($folder));

        $this->folderMapper->expects($this->once())
            ->method('findByName')
            ->with('hey', 'john')
            ->will($this->returnValue([]));

        $result = $this->folderService->create('hey', 'john', 5);

        $this->assertEquals($folder, $result);
    }


    public function testCreateThrowsExWhenFolderNameExists()
    {
        $folderName = 'hihi';
        $rows = [['id' => 1]];

        $this->l10n->expects($this->once())
            ->method('t');
        $this->folderMapper->expects($this->once())
            ->method('findByName')
            ->with($this->equalTo($folderName))
            ->will($this->returnValue($rows));

        $this->expectException(ServiceConflictException::class);
        $this->folderService->create($folderName, 'john', 3);
    }

    public function testCreateThrowsExWhenFolderNameEmpty()
    {
        $this->expectException('\OCA\News\Service\Exceptions\ServiceValidationException');
        $folderName = '';

        $this->folderMapper->expects($this->once())
            ->method('findByName')
            ->with($this->equalTo($folderName))
            ->will($this->returnValue([]));

        $this->folderService->create($folderName, 'john', 3);
    }


    public function testOpen()
    {
        $folder = new Folder();

        $this->folderMapper->expects($this->once())
            ->method('find')
            ->with('', 3)
            ->will($this->returnValue($folder));

        $this->folderMapper->expects($this->once())
            ->method('update')
            ->with($this->equalTo($folder));

        $this->folderService->open(3, false, '');

        $this->assertFalse($folder->getOpened());

    }


    public function testRename()
    {
        $folder = new Folder();
        $folder->setName('jooohn');

        $this->folderMapper->expects($this->once())
            ->method('find')
            ->with('', 3)
            ->will($this->returnValue($folder));

        $this->folderMapper->expects($this->once())
            ->method('update')
            ->with($this->equalTo($folder));

        $this->folderMapper->expects($this->once())
            ->method('findByName')
            ->with('bogus', '')
            ->will($this->returnValue([]));

        $this->folderService->rename(3, 'bogus', '');

        $this->assertEquals('bogus', $folder->getName());
    }


    public function testRenameThrowsExWhenFolderNameExists()
    {
        $folderName = 'hihi';
        $rows = [['id' => 1]];

        $this->l10n->expects($this->once())
            ->method('t');
        $this->folderMapper->expects($this->once())
            ->method('findByName')
            ->with($this->equalTo($folderName))
            ->will($this->returnValue($rows));

        $this->expectException(ServiceConflictException::class);
        $this->folderService->rename(3, $folderName, 'john');
    }


    public function testRenameThrowsExWhenFolderNameEmpty()
    {
        $folderName = '';

        $this->folderMapper->expects($this->once())
            ->method('findByName')
            ->with($this->equalTo($folderName))
            ->will($this->returnValue([]));

        $this->expectException(ServiceValidationException::class);
        $this->folderService->rename(3, $folderName, 'john');
    }


    public function testMarkDeleted()
    {
        $id = 3;
        $folder = new Folder();
        $folder2 = new Folder();
        $folder2->setDeletedAt($this->time);

        $this->folderMapper->expects($this->once())
            ->method('find')
            ->with($this->equalTo($this->user), $this->equalTo($id))
            ->will($this->returnValue($folder));
        $this->folderMapper->expects($this->once())
            ->method('update')
            ->with($this->equalTo($folder2));

        $this->folderService->markDeleted($id, $this->user);
    }


    public function testUnmarkDeleted()
    {
        $id = 3;
        $folder = new Folder();
        $folder2 = new Folder();
        $folder2->setDeletedAt(0);

        $this->folderMapper->expects($this->once())
            ->method('find')
            ->with($this->equalTo($this->user), $this->equalTo($id))
            ->will($this->returnValue($folder));
        $this->folderMapper->expects($this->once())
            ->method('update')
            ->with($this->equalTo($folder2));

        $this->folderService->unmarkDeleted($id, $this->user);
    }

    public function testPurgeDeleted()
    {
        $folder1 = new Folder();
        $folder1->setId(3);
        $folder2 = new Folder();
        $folder2->setId(5);
        $feeds = [$folder1, $folder2];

        $time = $this->time - $this->autoPurgeMinimumInterval;
        $this->folderMapper->expects($this->once())
            ->method('getToDelete')
            ->with($this->equalTo($time), $this->equalTo($this->user))
            ->will($this->returnValue($feeds));
        $this->folderMapper->expects($this->exactly(2))
            ->method('delete')
            ->withConsecutive([$folder1], [$folder2]);

        $this->folderService->purgeDeleted($this->user);
    }


    public function testPurgeDeletedNoInterval()
    {
        $folder1 = new Folder();
        $folder1->setId(3);
        $folder2 = new Folder();
        $folder2->setId(5);
        $feeds = [$folder1, $folder2];

        $this->folderMapper->expects($this->once())
            ->method('getToDelete')
            ->with($this->equalTo(null), $this->equalTo($this->user))
            ->will($this->returnValue($feeds));
        $this->folderMapper->expects($this->exactly(2))
            ->method('delete')
            ->withConsecutive([$folder1], [$folder2]);

        $this->folderService->purgeDeleted($this->user, false);
    }


    public function testDeleteUser()
    {
        $this->folderMapper->expects($this->once())
            ->method('deleteUser')
            ->will($this->returnValue($this->user));

        $this->folderService->deleteUser($this->user);
    }


}
