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
 * This class models a feed.
 */
class Feed extends Collection {

	private $title;
	private $url;
	private $items;  //array that contains all the items of the feed
	private $favicon;

	// if $items = null, it means that feed has not been fetched yet
	// if $id = null, it means that the feed has not been stored in the db yet
	public function __construct($url, $title, $items = null, $id = null) {
		$this->url = $url;
		$this->title = $title;
		if ($items !== null) {
			$this->items = $items;
		}
		if ($id !== null) {
			parent::__construct($id);
		}
	}
	
	public function getUrl() {
		return $this->url;
	}

	public function getTitle() {
		return $this->title;
	}
	
	public function setTitle($title) {
		$this->title = $title;
	}

	public function getFavicon() {
		return $this->favicon;
	}

	public function setFavicon($favicon) {
		$this->favicon = $favicon;
	}

	public function setItems($items) {
		$this->items = $items;
	}

	public function getItems() {
		return $this->items;
	}

}
