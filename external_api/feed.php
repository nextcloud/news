<?php

namespace OCA\News;

use \OCA\News\Controller\FeedController;

class API_Feed {

// 	public __construct($feedbl) {
// 		$this->bl = $feedbl;
// 	}

	public static function getAll() {
		$container = createDIContainer();
		$bl = $container['FeedBL'];
		$feeds = $bl->getAll();
		$serializedFeeds = array();
		foreach ($feeds as $feed) {
			$serializedFeeds[] = $feed->jsonSerialize();
		}
		return new \OC_OCS_Result($serializedFeeds);
	}
	
	public function getById($parameters) {
		$feedid = $parameters['feedid'];
		$container = createDIContainer();
		$bl = $container['FeedBL'];
		$feed = $bl->getById($feedid);
		$serializedFeed = array($feed->jsonSerialize());
		return new \OC_OCS_Result($serializedFeed);
	}
	
	public static function create() {
		
		$url = $_POST['url'];
		$folderId = $_POST['folderid'];
	
		$container = createDIContainer();
		$bl = $container['FeedBL'];
		$success = $bl->create($url, $folderId);
		
		if ($success) {
			return new \OC_OCS_Result();
		}
		else {
			return new \OC_OCS_Result(null, 101);
		}
	}
}
