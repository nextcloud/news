<?php
/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Alessandro Cosentino <cosenal@gmail.com>
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Alessandro Cosentino 2012
 * @copyright Bernhard Posselt 2012, 2014
 */

namespace OCA\News\Service;

require_once(__DIR__ . "/../../classloader.php");


use \OCA\News\Db\Folder;


class FolderServiceTest extends \PHPUnit_Framework_TestCase {

	private $folderMapper;
	private $folderService;
	private $time;
	private $user;
	private $autoPurgeMinimumInterval;
	private $l10n;

	protected function setUp(){
		$this->l10n = $this->getMockBuilder('\OCP\IL10N')
			->disableOriginalConstructor()
			->getMock();
		$this->time = 222;
		$timeFactory = $this->getMock('TimeFactory', ['getTime']);
		$timeFactory->expects($this->any())
			->method('getTime')
			->will($this->returnValue($this->time));
		$this->folderMapper = $this->getMockBuilder(
			'\OCA\News\Db\FolderMapper')
			->disableOriginalConstructor()
			->getMock();
		$this->autoPurgeMinimumInterval = 10;
		$config = $this->getMockBuilder(
			'\OCA\News\Utility\Config')
			->disableOriginalConstructor()
			->getMock();
		$config->expects($this->any())
			->method('getAutoPurgeMinimumInterval')
			->will($this->returnValue($this->autoPurgeMinimumInterval));
		$this->folderService = new FolderService(
			$this->folderMapper, $this->l10n, $timeFactory, $config);
		$this->user = 'hi';
	}


	function testFindAll(){
		$userId = 'jack';
		$return = 'hi';
		$this->folderMapper->expects($this->once())
			->method('findAllFromUser')
			->with($this->equalTo($userId))
			->will($this->returnValue($return));

		$result = $this->folderService->findAll($userId);

		$this->assertEquals($return, $result);
	}


	public function testCreate(){
		$folder = new Folder();
		$folder->setName('hey');
		$folder->setParentId(5);
		$folder->setUserId('john');
		$folder->setOpened(true);

		$this->folderMapper->expects($this->once())
			->method('insert')
			->with($this->equalTo($folder))
			->will($this->returnValue($folder));

		$result = $this->folderService->create('hey', 'john', 5);

		$this->assertEquals($folder, $result);
	}


	public function testCreateThrowsExWhenFolderNameExists(){
		$folderName = 'hihi';
		$rows = [['id' => 1]];

		$this->l10n->expects($this->once())
			->method('t');
		$this->folderMapper->expects($this->once())
			->method('findByName')
			->with($this->equalTo($folderName))
			->will($this->returnValue($rows));

		$this->setExpectedException('\OCA\News\Service\ServiceConflictException');
		$this->folderService->create($folderName, 'john', 3);
	}


	public function testCreateThrowsExWhenFolderNameEmpty(){
		$folderName = '';

		$this->folderMapper->expects($this->once())
			->method('findByName')
			->with($this->equalTo($folderName))
			->will($this->returnValue([]));

		$this->setExpectedException('\OCA\News\Service\ServiceValidationException');
		$this->folderService->create($folderName, 'john', 3);
	}


	public function testOpen(){
		$folder = new Folder();

		$this->folderMapper->expects($this->once())
			->method('find')
			->with($this->equalTo(3))
			->will($this->returnValue($folder));

		$this->folderMapper->expects($this->once())
			->method('update')
			->with($this->equalTo($folder));

		$this->folderService->open(3, false, '');

		$this->assertFalse($folder->getOpened());

	}


	public function testRename(){
		$folder = new Folder();
		$folder->setName('jooohn');

		$this->folderMapper->expects($this->once())
			->method('find')
			->with($this->equalTo(3))
			->will($this->returnValue($folder));

		$this->folderMapper->expects($this->once())
			->method('update')
			->with($this->equalTo($folder));

		$this->folderService->rename(3, 'bogus', '');

		$this->assertEquals('bogus', $folder->getName());		
	}


	public function testRenameThrowsExWhenFolderNameExists(){
		$folderName = 'hihi';
		$rows = [['id' => 1]];
		
		$this->l10n->expects($this->once())
			->method('t');
		$this->folderMapper->expects($this->once())
			->method('findByName')
			->with($this->equalTo($folderName))
			->will($this->returnValue($rows));

		$this->setExpectedException('\OCA\News\Service\ServiceConflictException');
		$this->folderService->rename(3, $folderName, 'john');
	}


	public function testRenameThrowsExWhenFolderNameEmpty(){
		$folderName = '';
		
		$this->folderMapper->expects($this->once())
			->method('findByName')
			->with($this->equalTo($folderName))
			->will($this->returnValue([]));

		$this->setExpectedException('\OCA\News\Service\ServiceValidationException');
		$this->folderService->rename(3, $folderName, 'john');
	}


	public function testMarkDeleted() {
		$id = 3;
		$folder = new Folder();
		$folder2 = new Folder();
		$folder2->setDeletedAt($this->time);

		$this->folderMapper->expects($this->once())
			->method('find')
			->with($this->equalTo($id), $this->equalTo($this->user))
			->will($this->returnValue($folder));
		$this->folderMapper->expects($this->once())
			->method('update')
			->with($this->equalTo($folder2));

		$this->folderService->markDeleted($id, $this->user);
	}


	public function testUnmarkDeleted() {
		$id = 3;
		$folder = new Folder();
		$folder2 = new Folder();
		$folder2->setDeletedAt(0);

		$this->folderMapper->expects($this->once())
			->method('find')
			->with($this->equalTo($id), $this->equalTo($this->user))
			->will($this->returnValue($folder));
		$this->folderMapper->expects($this->once())
			->method('update')
			->with($this->equalTo($folder2));

		$this->folderService->unmarkDeleted($id, $this->user);
	}

	public function testPurgeDeleted(){
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
		$this->folderMapper->expects($this->at(1))
			->method('delete')
			->with($this->equalTo($folder1));
		$this->folderMapper->expects($this->at(2))
			->method('delete')
			->with($this->equalTo($folder2));

		$this->folderService->purgeDeleted($this->user);
	}


	public function testPurgeDeletedNoInterval(){
		$folder1 = new Folder();
		$folder1->setId(3);
		$folder2 = new Folder();
		$folder2->setId(5);
		$feeds = [$folder1, $folder2];

		$this->folderMapper->expects($this->once())
			->method('getToDelete')
			->with($this->equalTo(null), $this->equalTo($this->user))
			->will($this->returnValue($feeds));
		$this->folderMapper->expects($this->at(1))
			->method('delete')
			->with($this->equalTo($folder1));
		$this->folderMapper->expects($this->at(2))
			->method('delete')
			->with($this->equalTo($folder2));

		$this->folderService->purgeDeleted($this->user, false);
	}


	public function testDeleteUser() {
		$this->folderMapper->expects($this->once())
			->method('deleteUser')
			->will($this->returnValue($this->user));

		$this->folderService->deleteUser($this->user);
	}


}
