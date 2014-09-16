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

use \OCP\AppFramework\Db\Entity;

/**
 * @method integer getId()
 * @method void setId(integer $value)
 * @method string getUserId()
 * @method void setUserId(string $value)
 * @method string getUrlHash()
 * @method void setUrlHash(string $value)
 * @method string getUrl()
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
 * @method boolean getPreventUpdate()
 * @method void setPreventUpdate(boolean $value)
 * @method integer getDeletedAt()
 * @method void setDeletedAt(integer $value)
 * @method integer getArticlesPerUpdate()
 * @method void setArticlesPerUpdate(integer $value)
 */
class Feed extends Entity implements IAPI, \JsonSerializable {

	use EntityJSONSerializer;

	protected $userId;
	protected $urlHash;
	protected $url;
	protected $title;
	protected $faviconLink;
	protected $added;
	protected $folderId;
	protected $unreadCount;
	protected $link;
	protected $preventUpdate;
	protected $deletedAt;
	protected $articlesPerUpdate;

	public function __construct(){
		$this->addType('parentId', 'integer');
		$this->addType('added', 'integer');
		$this->addType('folderId', 'integer');
		$this->addType('unreadCount', 'integer');
		$this->addType('preventUpdate', 'boolean');
		$this->addType('deletedAt', 'integer');
		$this->addType('articlesPerUpdate', 'integer');
	}


	/**
	 * Turns entitie attributes into an array
	 */
	public function jsonSerialize() {
		$serialized = $this->serializeFields([
			'id',
			'userId',
			'urlHash',
			'url',
			'title',
			'faviconLink',
			'added',
			'folderId',
			'unreadCount',
			'link',
			'preventUpdate',
			'deletedAt',
			'articlesPerUpdate',
		]);

		$url = parse_url($this->link)['host'];

		// strip leading www. to avoid css class confusion
		if (strpos($url, 'www.') === 0) {
			$url = substr($url, 4);
		}

		$serialized['cssClass'] = 'custom-' . str_replace('.', '-', $url);

		return $serialized;
	}


	public function toAPI() {
		return $this->serializeFields([
			'id',
			'url',
			'title',
			'faviconLink',
			'added',
			'folderId',
			'unreadCount',
			'link'
		]);
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