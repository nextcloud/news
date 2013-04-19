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

use \OCA\AppFramework\Db\Entity;


class Feed extends Entity {

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

	public function __construct(){
		$this->addType('parentId', 'int');
		$this->addType('added', 'int');
		$this->addType('folderId', 'int');
		$this->addType('unreadCount', 'int');
		$this->addType('preventUpdate', 'bool');
	}

}