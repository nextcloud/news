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

namespace OCA\News\Db;


class Item extends Entity {

	public $url;
	public $title;
	public $guid;
	public $body;
	public $status;
	public $author;
	public $date;
	public $feedTitle;
	public $enclosure;

	public function setRead() {
		$this->markFieldUpdated('status');
		$this->status &= ~StatusFlag::UNREAD;
	}

	public function isRead() {
		return !($this->status & StatusFlag::UNREAD);
	}

}



/*class Item {

	private $url;
	private $title;
	private $guid;
	private $body;
	private $status;  //a bit-field set with status flags
	private $id;      //id of the item in the database table
	private $author;
	private $date; //date is stored in the Unix format
	private $feedTitle;
	private $enclosure; // Enclosure object containing media attachment information
	
	public function __construct($url, $title, $guid, $body, $id = null) {
		$this->title = $title;
		$this->url = $url;
		$this->guid = $guid;
		$this->body = $body;
		$this->enclosure = false;
		if ($id == null) {
			$this->status |= StatusFlag::UNREAD;
		}
		else {
			$this->id = $id;
		}
	}



	



	public function setUnread() {
		$this->status |= StatusFlag::UNREAD;
	}

	

	public function setImportant() {
		$this->status |= StatusFlag::IMPORTANT;
	}

	public function setUnimportant() {
		$this->status &= ~StatusFlag::IMPORTANT;
	}

	public function isImportant() {
		return ($this->status & StatusFlag::IMPORTANT);
	}
	

	

	public function getBody() {
		return $this->body;
	}

	public function setBody($body) {
		$this->body = $body;
	}

	public function getAuthor() {
		return $this->author;
	}

	public function setAuthor($author) {
		$this->author = $author;
	}

	public function getDate() {
		return $this->date;
	}

	//TODO: check if the parameter is in the Unix format
	public function setDate($date) {
		$this->date = $date;
	}
	
	public function getEnclosure() {
		return $this->enclosure;
	}
	
	public function setEnclosure(Enclosure $enclosure) {
		$this->enclosure = $enclosure;
	}
}

*/