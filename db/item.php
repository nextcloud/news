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

namespace OCA\News;

/**
 * This class models an item.
 *
 * It encapsulate a SimplePie_Item object and adds a status flag to it
 */
class Item {

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

	public function getFeedId() {
		return $this->feedId;
	}

	public function setFeedId($feedId) {
		$this->feedId = $feedId;
	}

	public function getGuid() {
		return $this->guid;
	}

	public function setGuid($guid) {
		$this->guid = $guid;
	}

	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
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

	/**
	 * NOTE: this is needed to store items in the database, otherwise
	 * the status of an item should be retrieved with methods: isRead(), isImportant(), ...
	 */
	public function getStatus() {
		return $this->status;
	}

	public function setStatus($status) {
		$this->status = $status;
	}

	/* change the following method with set/get magic methods
	 * http://www.php.net/manual/en/language.oop5.overloading.php#object.get
	 */

	public function getTitle() {
		return $this->title;
	}

	public function setTitle($title) {
		$this->title = $title;
	}
	
	public function getFeedTitle() {
		return $this->feedTitle;
	}

	public function setFeedTitle($feedtitle) {
		$this->feedTitle = $feedtitle;
	}

	public function getUrl() {
		return $this->url;
	}

	public function setUrl($url) {
		$this->url = $url;
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

