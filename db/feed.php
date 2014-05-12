<?php
/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Alessandro Cosentino <cosenal@gmail.com>
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Alessandro Cosentino 2012
 * @copyright Bernhard Posselt 2012, 2014
 */

namespace OCA\News\Db;

/**
 * @method integer getId()
 * @method void setId(integer $value)
 * @method string getUserId()
 * @method void setUserId(string $value)
 * @method string getUrlHash()
 * @method void setUrlHash(string $value)
 * @method string getUrl()
 * @method void setUrl(string $value)
 * @method string getTitle()
 * @method void setTitle(string $value)
 * @method string getFaviconLink()
 * @method void setFaviconLink(string $value)
 * @method integer getAdded()
 * @method void setAdded(integer $value)
 * @method integer getFolderId()
 * @method void setFolderId(integer $value)
 * @method integer getUnreadCount()
 * @method void setUnreadCount(integer $value)
 * @method string getLink()
 * @method void setLink(string $value)
 * @method boolean getPreventUpdate()
 * @method void setPreventUpdate(boolean $value)
 * @method integer getDeletedAt()
 * @method void setDeletedAt(integer $value)
 * @method integer getArticlesPerUpdate()
 * @method void setArticlesPerUpdate(integer $value)
 */
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