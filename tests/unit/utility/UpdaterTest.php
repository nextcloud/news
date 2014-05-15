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

namespace OCA\News\Utility;

use \OCA\News\Service\FolderService;
use \OCA\News\Service\FeedService;
use \OCA\News\Service\ItemService;


require_once(__DIR__ . "/../../classloader.php");


class UpdaterTest extends \PHPUnit_Framework_TestCase {

	private $folderService;
	private $feedService;
	private $itemService;
	private $updater;

	protected function setUp() {
		$this->folderService = $this->getMockBuilder(
			'\OCA\News\Service\FolderService')
			->disableOriginalConstructor()
			->getMock();
		$this->feedService = $this->getMockBuilder(
			'\OCA\News\Service\FeedService')
			->disableOriginalConstructor()
			->getMock();
		$this->itemService = $this->getMockBuilder(
			'\OCA\News\Service\ItemService')
			->disableOriginalConstructor()
			->getMock();
		$this->updater = new Updater($this->folderService,
			$this->feedService,
			$this->itemService);
	}

	public function testBeforeUpdate() {
		$this->folderService->expects($this->once())
			->method('purgeDeleted');
		$this->feedService->expects($this->once())
			->method('purgeDeleted');
		$this->updater->beforeUpdate();
	}


	public function testAfterUpdate() {
		$this->itemService->expects($this->once())
			->method('autoPurgeOld');
		$this->updater->afterUpdate();
	}

	public function testUpdate() {
		$this->feedService->expects($this->once())
			->method('updateAll');
		$this->updater->update();
	}
}