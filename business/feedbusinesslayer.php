<?php

/**
* ownCloud - News
*
* @author Alessandro Copyright
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

namespace OCA\News\Business;

use \OCA\AppFramework\Core\API;
use \OCA\AppFramework\Db\DoesNotExistException;
use \OCA\AppFramework\Db\MultipleObjectsReturnedException;

use \OCA\News\Db\ObjectExistsException;


class FeedBusinessLayer extends BusinessLayer {

	private $updater;

	public function __construct(API $api, $feedMapper, $updater) {
		parent::__construct($api, $feedMapper);
		$this->updater = $updater;
	}


	/**
	 * Sets all items with id lower than $idLowerThan as read
	 * @param int $feedId the id of the feed
	 * @param int $idLowerThan all items lower than this id will be marked read
	 * @throws FeedDoesNotExistException if feed with id $id does not exist
	 * @throws MultipleFeedsReturnedException if more feeds than one exist with
	 * the same id
	 */
	public function setRead($feedId, $idLowerThan){
		$feed = $this->getById($feedId);
		$mapper->setAllReadWithIdLowerThan($feed->getId(), $idLowerThan);
	}


	protected function validate($feed){
		// TODO: validate feed (length, required fields etc)
	}


	protected function throwDoesNotExistException(DoesNotExistException $ex){
		throw new FeedDoesNotExistException($ex->getMessage());
	}


	protected function throwMultipleObjectsReturnedException(MultipleObjectsReturnedException $ex){
		throw new MultipleFeedsReturnedException($ex->getMessage());
	}


	protected function throwObjectExistsException(ObjectExistsException $ex){
		throw new FeedExistsException($ex->getMessage());
	}

}