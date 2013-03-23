<?php

/**
* ownCloud - News
*
* @author Alessandro Cosentino
* @author Bernhard Posselt
* @copyright 2012 Alessandro Cosentino cosenal@gmail.com
* @copyright 2012 Bernhard Posselt nukeawhale@gmail.com
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
* License as published by the Free Software Foundation; either
* version 3 of the License, or any later version.
*
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*
* You should have received a copy of the GNU Affero General Public
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
*
*/

namespaces \OCA\News\Search;

class Provider extends \OC_Search_Provider {
	
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
