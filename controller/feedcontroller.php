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

namespace OCA\News\Controller;

use \OCA\AppFramework\Controller\Controller;
use \OCA\AppFramework\Core\API;
use \OCA\AppFramework\Http\Request;
use \OCA\AppFramework\Db\DoesNotExistException;
use \OCA\AppFramework\Db\MultipleObjectsReturnedException;

use \OCA\News\Bl\FeedBl;
use \OCA\News\Bl\FolderBl;
use \OCA\News\Bl\BLException;
use \OCA\News\Db\FeedType;


class FeedController extends Controller {

	private $feedBl;
	private $folderBl;

	public function __construct(API $api, Request $request, FeedBl $feedBl,
		                        FolderBl $folderBl){
		parent::__construct($api, $request);
		$this->feedBl = $feedBl;
		$this->folderBl = $folderBl;
	}


	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function feeds(){
		$userId = $this->api->getUserId();
		$result = $this->feedBl->findAllFromUser($userId);

		$params = array(
			'feeds' => $result
		);

		return $this->renderJSON($params);
	}


	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function active(){
		$userId = $this->api->getUserId();
		$feedId = $this->api->getUserValue($userId, 'lastViewedFeedId');
		$feedType = $this->api->getUserValue($userId, 'lastViewedFeedType');

		// check if feed or folder exist
		try {
			if($feedType === FeedType::FOLDER){
				$this->folderBl->find($feedId, $userId);
			} elseif ($feedType === FeedType::FEED){
				$this->feedBl->find($feedId, $userId);
			}
		} catch (BLException $ex){
			$feedId = 0;
			$feedType = FeedType::SUBSCRIPTIONS;
		}

		$params = array(
			'activeFeed' => array(
				'id' => $feedId,
				'type' => $feedType
			)
		);

		return $this->renderJSON($params);
	}


	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function create(){
		$url = $this->params('url');
		$parentFolderId = $this->params('parentFolderId');
		$userId = $this->api->getUserId();

		try {
			$feed = $this->feedBl->create($url, $parentFolderId, $userId);
			$params = array(
				'feeds' => array($feed)
			);

			return $this->renderJSON($params);
		} catch(BLException $ex) {

			return $this->renderJSON(array(), $ex->getMessage());
		}
	}


	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function delete(){
		$feedId = $this->params('feedId');
		$userId = $this->api->getUserId();

		$this->feedBl->delete($feedId, $userId);

		return $this->renderJSON();
	}


	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function update(){
		$feedId = $this->params('feedId');
		$userId = $this->api->getUserId();

		$feed = $this->feedBl->update($feedId, $userId);

		$params = array(
			'feeds' => array($feed)
		);

		return $this->renderJSON($params);
	}


	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 */
	public function move(){
		$feedId = $this->params('feedId');
		$parentFolderId = $this->params('parentFolderId');
		$userId = $this->api->getUserId();

		$this->feedBl->move($feedId, $parentFolderId, $userId);

		return $this->renderJSON();	
	}


}