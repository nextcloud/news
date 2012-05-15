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
	private $spfeed; //we encapsulate a SimplePie_Core object

	public function __construct($url){
		$this->url = $url;
		$this->spfeed = new SimplePie_Core();
		$this->spfeed->set_item_class('OC_News_Item');
		$this->spfeed->set_feed_url( $url );
		$this->spfeed->enable_cache( false );

		//FIXME: figure out if constructor is the right place for these
		$this->spfeed->init();
		$this->spfeed->handle_content_type();
	}

	public function getUrl(){
		return $this->url;
	}

	public function getTitle(){
		return $this->spfeed->get_title();
	}
}
