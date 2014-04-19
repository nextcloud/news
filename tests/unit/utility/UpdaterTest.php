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

use \OCA\News\BusinessLayer\FolderBusinessLayer;
use \OCA\News\BusinessLayer\FeedBusinessLayer;
use \OCA\News\BusinessLayer\ItemBusinessLayer;


require_once(__DIR__ . "/../../classloader.php");


class UpdaterTest extends \PHPUnit_Framework_TestCase {

	private $folderBusinessLayer;
	private $feedBusinessLayer;
	private $itemBusinessLayer;
	private $updater;

	protected function setUp() {
		$this->folderBusinessLayer = $this->getMockBuilder(
			'\OCA\News\BusinessLayer\FolderBusinessLayer')
			->disableOriginalConstructor()
			->getMock();
		$this->feedBusinessLayer = $this->getMockBuilder(
			'\OCA\News\BusinessLayer\FeedBusinessLayer')
			->disableOriginalConstructor()
			->getMock();
		$this->itemBusinessLayer = $this->getMockBuilder(
			'\OCA\News\BusinessLayer\ItemBusinessLayer')
			->disableOriginalConstructor()
			->getMock();
		$this->updater = new Updater($this->folderBusinessLayer,
			$this->feedBusinessLayer,
			$this->itemBusinessLayer);
	}

	public function testBeforeUpdate() {
		$this->folderBusinessLayer->expects($this->once())
			->method('purgeDeleted');
		$this->feedBusinessLayer->expects($this->once())
			->method('purgeDeleted');
		$this->updater->beforeUpdate();
	}


	public function testAfterUpdate() {
		$this->itemBusinessLayer->expects($this->once())
			->method('autoPurgeOld');
		$this->updater->afterUpdate();
	}

	public function testUpdate() {
		$this->feedBusinessLayer->expects($this->once())
			->method('updateAll');
		$this->updater->update();
	}
}