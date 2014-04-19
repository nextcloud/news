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


class Updater {


	private $folderBusinessLayer;
	private $feedBusinessLayer;
	private $itemBusinessLayer;

	public function __construct(FolderBusinessLayer $folderBusinessLayer,
	                            FeedBusinessLayer $feedBusinessLayer,
	                            ItemBusinessLayer $itemBusinessLayer) {
		$this->folderBusinessLayer = $folderBusinessLayer;
		$this->feedBusinessLayer = $feedBusinessLayer;
		$this->itemBusinessLayer = $itemBusinessLayer;
	}


	public function beforeUpdate() {
		$this->folderBusinessLayer->purgeDeleted();
		$this->feedBusinessLayer->purgeDeleted();
	}


	public function update() {
		$this->feedBusinessLayer->updateAll();
	}


	public function afterUpdate() {
		$this->itemBusinessLayer->autoPurgeOld();
	}


}