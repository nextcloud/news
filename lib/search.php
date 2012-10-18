<?php

class OC_Search_Provider_News extends OC_Search_Provider{
	
	function search($query) {
		if (!OCP\App::isEnabled('news')) {
			return array();
		}
		
		$feedMapper = new OCA\News\FeedMapper(OCP\USER::getUser());
		$results=array();
		
		if($feedMapper->feedCount() > 0) {
			$allFeeds = $feedMapper->findAll();
			
			$l = new OC_l10n('news');
			
			foreach($allFeeds as $feed) {
				if(substr_count(strtolower($feed['title']), strtolower($query)) > 0) {
					$link = OCP\Util::linkTo('news', 'index.php').'&lastViewedFeedId='.urlencode($feed['id']);
					$results[]=new OC_Search_Result($feed['title'], '', $link, (string)$l->t('News'));
				}
			}
		}
		return $results;
		
	}
}
