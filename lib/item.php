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


class StatusFlag{
	const Unread    = 0x02;
	const Important = 0x04;
	const Deleted   = 0x08;
	const Updated   = 0x16;
}

/**
 * This class models an item.
 *
 * It encapsulate a SimplePie_Item object and adds a status flag to it
 */
class OC_News_Item {

	private $url;
	private $title;
	private $guid;
	private $body;
	private $status;  //a bit-field set with status flags
	private $id;      //id of the item in the database table

	public function __construct($url, $title, $guid, $id = null){
		$this->title = $title;
		$this->url = $url;
		$this->guid = $guid;
		$this->status |= StatusFlag::Unread;
	}

	public function getGuid(){
		return $this->guid;
	}

	public function setGuid($guid){
		$this->guid = $guid;
	}

	public function getId(){
		return $this->id;
	}

	public function setId($id){
		$this->id = $id;
	}

	public function setRead(){
		$this->status &= ~StatusFlag::Unread;
	}

	public function setUnread(){
		$this->status |= StatusFlag::Unread; 
	}

	public function isRead(){
		return !($this->status & StatusFlag::Unread);
	}
	
	public function setImportant(){
		$this->status |= StatusFlag::Important; 
	}
	
	public function setUnimportant(){
		$this->status &= ~StatusFlag::Important;
	}
	
	public function isImportant(){
		return ($this->status & StatusFlag::Important);
	}
		
	/**
	 * NOTE: this is needed to store items in the database, otherwise 
	 * the status of an item should be retrieved with methods: isRead(), isImportant(), ...
	 */
	public function getStatus(){
		return $this->status;
	}
	
	public function setStatus($status){
		$this->status = $status;
	}

	public function getTitle(){
		return $this->title;
	}

	public function setTitle($title){
		$this->title = $title;
	}

	public function getUrl(){
		return $this->url;
	}

	public function setUrl($url){
		$this->url = $url;
	}
}
