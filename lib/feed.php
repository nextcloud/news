<?php
/**
* ownCloud - News app
*
* @author Alessandro Cosentino
* @copyright 2012 Alessandro Cosentino cosenal@gmail.com
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
* You should have received a copy of the GNU Lesser General Public 
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
* 
*/

/**
 * This class models a feed.
 */
class OC_News_Feed extends OC_News_Collection {

	private $url;
	private $spfeed; //encapsulate a SimplePie_Core object
	private $items;  //array that contains all the items of the feed

	public function __construct($url, $title, $items, $id = null){
		$this->url = $url;
		$this->title = $title;
		$this->items = $items;
		if ($id !== null){
			$this->id = $id;
		}
	}
 
	public function getUrl(){
		return $this->url;
	}

	public function getTitle(){
		return $this->title;
	}

	public function setItems($items){
		$this->items = $items;
	}

	public function getItems(){
		return $this->items;
	}
}
