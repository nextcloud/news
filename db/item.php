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



	

	public function setRead() {
		$this->status &= ~StatusFlag::UNREAD;
	}

	public function setUnread() {
		$this->status |= StatusFlag::UNREAD;
	}

	public function isRead() {
		return !($this->status & StatusFlag::UNREAD);
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