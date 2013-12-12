<?php

/**
* ownCloud - News
*
* @author Alessandro Cosentino
* @author Bernhard Posselt
* @copyright 2012 Alessandro Cosentino cosenal@gmail.com
* @copyright 2012 Bernhard Posselt dev@bernhard-posselt.com
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

use \OCA\AppFramework\Db\Entity;


class Item extends Entity implements IAPI {

	public $guidHash;
	public $guid;
	public $url;
	public $title;
	public $author;
	public $pubDate;
	public $body;
	public $enclosureMime;
	public $enclosureLink;
	public $feedId;
	public $status = 0;
	public $lastModified;


	public function __construct(){
		$this->addType('pubDate', 'int');
		$this->addType('feedId', 'int');
		$this->addType('status', 'int');
		$this->addType('lastModified', 'int');
	}


	public function setRead() {
		$this->markFieldUpdated('status');
		$this->status &= ~StatusFlag::UNREAD;
	}

	public function isRead() {
		return !(($this->status & StatusFlag::UNREAD) === StatusFlag::UNREAD);
	}

	public function setUnread() {
		$this->markFieldUpdated('status');
		$this->status |= StatusFlag::UNREAD;
	}

	public function isUnread() {
		return !$this->isRead();
	}

	public function setStarred() {
		$this->markFieldUpdated('status');
		$this->status |= StatusFlag::STARRED;
	}

	public function isStarred() {
		return ($this->status & StatusFlag::STARRED) === StatusFlag::STARRED;
	}

	public function setUnstarred() {
		$this->markFieldUpdated('status');
		$this->status &= ~StatusFlag::STARRED;
	}

	public function isUnstarred() {
		return !$this->isStarred();
	}


	public function toAPI() {
		return array(
			'id' => $this->getId(),
			'guid' => $this->getGuid(),
			'guidHash' => $this->getGuidHash(),
			'url' => $this->getUrl(),
			'title' => $this->getTitle(),
			'author' => $this->getAuthor(),
			'pubDate' => $this->getPubDate(),
			'body' => $this->getBody(),
			'enclosureMime' => $this->getEnclosureMime(),
			'enclosureLink' => $this->getEnclosureLink(),
			'feedId' => $this->getFeedId(),
			'unread' => $this->isUnread(),
			'starred' => $this->isStarred(),
			'lastModified' => $this->getLastModified()
		);
	}


	public function toExport($feeds) {
		return array(
			'guid' => $this->getGuid(),
			'url' => $this->getUrl(),
			'title' => $this->getTitle(),
			'author' => $this->getAuthor(),
			'pubDate' => $this->getPubDate(),
			'body' => $this->getBody(),
			'enclosureMime' => $this->getEnclosureMime(),
			'enclosureLink' => $this->getEnclosureLink(),
			'unread' => $this->isUnread(),
			'starred' => $this->isStarred(),
			'feedLink' => $feeds['feed'. $this->getFeedId()]->getLink()
		);
	}


	public static function fromImport($import) {
		$item = new static();
		$item->setGuid($import['guid']);
		$item->setUrl($import['url']);
		$item->setTitle($import['title']);
		$item->setAuthor($import['author']);
		$item->setPubDate($import['pubDate']);
		$item->setBody($import['body']);
		$item->setEnclosureMime($import['enclosureMime']);
		$item->setEnclosureLink($import['enclosureLink']);
		if($import['unread']) {
			$item->setUnread();
		} else {
			$item->setRead();
		}
		if($import['starred']) {
			$item->setStarred();
		} else {
			$item->setUnstarred();
		}
		
		$item->setFeedId(null);
		return $item;
	}


	public function fromRow(array $array) {
		// sanitize them fucked up non utf-8 strings because PHP's glorious idea
		// on how to handle non utf-8 srtings in json_encode is to just *BOOM*
		// in your face, which results in a spinning wheel. Forever.
		foreach($array as $key => $value) {
			if($key === 'body' || $key === 'author' || $key === 'title' || 
			   $key === 'guid' || $key === 'guidHash') {
				$array[$key] === iconv('UTF-8', 'UTF-8//IGNORE', $value);
			}
		}
		parent::fromRow($array);
	}


	public function setAuthor($name) {
		parent::setAuthor(strip_tags($name));
	}


	public function setTitle($title) {
		parent::setTitle(strip_tags($title));
	}


	public function setUrl($url) {
		$url = trim($url);
		if(strpos($url, 'http') === 0 || strpos($url, 'magnet') === 0) {
			parent::setUrl($url);
		}
	}


	public function setGuid($guid) {
		parent::setGuid($guid);
		$this->setGuidHash(md5($guid));
	}


	public function setBody($body) {
		// FIXME: this should not happen if the target="_blank" is already on the link
		parent::setBody(str_replace('<a', '<a target="_blank"',	$body));
	}

}

