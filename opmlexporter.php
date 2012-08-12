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

class OPMLExporter {


	public static function feedToXML(OCA\News\Feed $feed){
	
	}

	/**
	 * @brief 
	 */
	public static function export(OCA\News\Collection $data){
		foreach($children as $child) {
			if ($child instanceOf OCA\News\Folder){
			}
			elseif ($child instanceOf OCA\News\Feed) { //onhover $(element).attr('id', 'newID');
			}
			else {
			//TODO:handle error in this case
			}
		}
	}
}