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


class Feed extends Entity implements IAPI {

	public $userId;
	public $urlHash;
	public $url;
	public $title;
	public $faviconLink;
	public $added;
	public $folderId;
	public $unreadCount;
	public $link;
	public $preventUpdate;
	public $deletedAt;
	public $articlesPerUpdate;

	public function __construct(){
		$this->addType('parentId', 'integer');
		$this->addType('added', 'integer');
		$this->addType('folderId', 'integer');
		$this->addType('unreadCount', 'integer');
		$this->addType('preventUpdate', 'boolean');
		$this->addType('deletedAt', 'integer');
		$this->addType('articlesPerUpdate', 'integer');
	}


	public function toAPI() {
		return array(
			'id' => $this->getId(),
			'url' => $this->getUrl(),
			'title' => $this->getTitle(),
			'faviconLink' => $this->getFaviconLink(),
			'added' => $this->getAdded(),
			'folderId' => $this->getFolderId(),
			'unreadCount' => $this->getUnreadCount(),
			'link' => $this->getLink()
		);
	}


	public function setUrl($url) {
		$url = trim($url);
		if(strpos($url, 'http') === 0) {
			parent::setUrl($url);
			$this->setUrlHash(md5($url));
		}
	}


	public function setLink($url) {
		$url = trim($url);
		if(strpos($url, 'http') === 0) {
			parent::setLink($url);
		}
	}


}