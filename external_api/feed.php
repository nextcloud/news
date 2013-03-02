<?php

namespace OCA\News;

use \OCA\News\Controller\FeedController;

class API_Feed {

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
}
