<?php
/**
* ownCloud - News app
*
* @author Alessandro Cosentino
* Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
*
* This file is licensed under the Affero General Public License version 3 or later.
* See the COPYING-README file
*
*/

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
				if(substr_count(strtolower($feed->getTitle()), strtolower($query)) > 0) {
					$link = \OC_Helper::linkToRoute('news_index_feed', array('feedid' => $feed->getId()));
					$results[]=new OC_Search_Result($feed->getTitle(), '', $link, (string)$l->t('News'));
				}
			}
		}
		return $results;
		
	}
}
