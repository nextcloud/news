<?php

namespace OCA\News;

use \OCA\News\Controller\FeedController;

class FeedApi {

	public function __construct($bl){
		$this->bl = $bl;
	}

	public function getAll() {
		$feeds = $this->bl->getAll();
		$serializedFeeds = array();
		foreach ($feeds as $feed) {
			$serializedFeeds[] = $feed->jsonSerialize();
		}
		return new \OC_OCS_Result($serializedFeeds);
	}
	
	public function getById($params) {
		$feed = $this->bl->getById($feedid);
		$serializedFeed = array($feed->jsonSerialize());
		return new \OC_OCS_Result($serializedFeed);
	}
	
	public function delete($params) {
		//TODO: check parameters here

		$success = $this->bl->delete($params["feedid"]);

		if ($success) {
			return new \OC_OCS_Result();
		}
		else {
			return new \OC_OCS_Result(null, 101);
		}
	}

	public function create() {
		$url = $_POST['url'];
		$folderId = $_POST['folderid'];
		//TODO: check parameters here
	
		$success = $this->bl->create($url, $folderId);
		
		if ($success) {
			return new \OC_OCS_Result();
		}
		else {
			return new \OC_OCS_Result(null, 101);
		}
	}
}
