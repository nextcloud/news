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
class OC_News_Feed {

	private $url;
	private $id;     //id of the feed in the database
	private $spfeed; //encapsulate a SimplePie_Core object
	private $items;  //array that contains all the items of the feed
	private $fetched;

	public function __construct($url, $id = null){
		$this->url = $url;
		$this->spfeed = new SimplePie_Core();
		$this->spfeed->set_feed_url( $url );
		$this->spfeed->enable_cache( false );
		$this->fetched = false;
		if ($id !== null){
			self::setId($id);
		}
	}

	public function fetch(){
		$this->spfeed->init();
		$this->spfeed->handle_content_type();

		$this->fetched = true;
	}

	public function isFetched(){
		return $this->fetched;
	}
 
	public function getId(){
		return $this->id;
	}

	public function setId($id){
		$this->id = $id;
	}

	public function getUrl(){
		return $this->url;
	}

	public function getTitle(){
		if (!$this->isFetched()) {
			$this->fetch();
		}
		return $this->spfeed->get_title();
	}

	public function setItems($items){
		$this->items = $items;
	}

	public function getItems(){
		if (!isset($this->items)){
			if (!$this->isFetched()) {
				$this->fetch();
			}
			$spitems = $this->spfeed->get_items();
			$this->items = array();
			foreach($spitems as $spitem) { //FIXME: maybe we can avoid this loop
				$this->items[] = new OC_News_Item($spitem); 
			}
		}
		return $this->items;
	}
}
