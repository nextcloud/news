<?php
/**
* ownCloud - News app
*
* @author Bernhard Posselt
* Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
*
* This file is licensed under the Affero General Public License version 3 or later.
* See the COPYING-README file
*
*/

namespace OCA\News;

/**
 * Class which handles all ajax calls
 */
class NewsAjaxController extends Controller {

	private $feedMapper;
	private $folderMapper;
	private $itemMapper;

	/**
	 * @param Request $request: the object with the request instance
	 * @param string $api: an instance of the api wrapper
	 * @param FeedMapper $feedMapepr an instance of the feed mapper
	 * @param FolderMapper $folderMapper an instance of the folder mapper
	 * @param ItemMapper $itemMapper an instance of the item mapper
	 */
	public function __construct($request, $api, $feedMapper, $folderMapper,
								$itemMapper){
		parent::__construct($request, $api);
		$this->feedMapper = $feedMapper;
		$this->folderMapper = $folderMapper;
		$this->itemMapper = $itemMapper;
	}


	/**
	 * @brief turns a post parameter which got a boolean from javascript to
	 * a boolean in PHP
	 * @param string $param the post parameter that should be turned into a bool
	 * @return a PHP boolean
	 */
	public function postParamToBool($param){
		if($param === 'false') {
			return false;
		} else {
			return true;
		}
	}


	/**
	 * This turns a folder result into an array which can be sent to the client
	 * as JSON
	 * @param array $folders the database query result for folders
	 * @return an array ready for sending as JSON
	 */
	private function foldersToArray($folders){
		$foldersArray = array();
		foreach($folders as $folder){
			if($folder instanceof \OCA\News\Folder){
				 array_push($foldersArray, array(
					'id' => (int)$folder->getId(),
					'name' => $folder->getName(),
					'open' => $folder->getOpened()==="1",
					'hasChildren' => count($folder->getChildren()) > 0,
					'show' => true
					)
				);
			}
		}
		return $foldersArray;
	}


	/**
	 * This turns a feed result into an array which can be sent to the client
	 * as JSON
	 * @param array $feeds the database query result for feeds
	 * @return an array ready for sending as JSON
	 */
	private function feedsToArray($feeds){
		$feedsArray = array();
		foreach($feeds as $feed){
			 array_push($feedsArray, array(
				'id' => (int)$feed->getId(),
				'name' => $feed->getTitle(),
				'unreadCount' => (int)$this->itemMapper->getUnreadCount(FeedType::FEED,
																$feed->getId()),
				'folderId' => (int)$feed->getFolderId(),
				'show' => true,
				'icon' => 'url(' . $feed->getFavicon() .')',
				'url' => $feed->getUrl()
				)
			);
		}
		return $feedsArray;
	}


	/**
	 * This turns an items result into an array which can be sent to the client
	 * as JSON
	 * @param array $items the database query result for items
	 * @return an array ready for sending as JSON
	 */
	private function itemsToArray($items){
		$itemsArray = array();
		foreach($items as $item){

			$enclosure = $item->getEnclosure();
			if($enclosure){
				$enclosure = array(
					'link' => $enclosure->getLink(),
					'type' => $enclosure->getMimeType()
				);
			}

			 array_push($itemsArray, array(
				'id' => (int)$item->getId(),
				'title' => $item->getTitle(),
				'isRead' => (bool)$item->isRead(),
				'isImportant' => (bool)$item->isImportant(),
				'feedId' => (int)$item->getFeedId(),
				'feedTitle' => $item->getFeedTitle(),
				'date' => (int)$item->getDate(),
				'body' => $item->getBody(),
				'author' => $item->getAuthor(),
				'url' => $item->getUrl(),
				'enclosure' => $enclosure
				)
			);
		}
		return $itemsArray;
	}


	/**
	 * This is being called when the app starts and all feeds
	 * and folders are requested
	 */
	public function init(){
		$folders = $this->folderMapper->childrenOfWithFeeds(0);
		$foldersArray = $this->foldersToArray($folders);

		$feeds = $this->feedMapper->findAll();
		$feedsArray = $this->feedsToArray($feeds);

		$activeFeed = array();
		$activeFeed['id'] = (int)$this->api->getUserValue('lastViewedFeed');
		$activeFeed['type'] = (int)$this->api->getUserValue('lastViewedFeedType');

		$showAll = $this->api->getUserValue('showAll') === "1";

		$starredCount = $this->itemMapper->getUnreadCount(\OCA\News\FeedType::STARRED, 0);

		$result = array(
			'folders' => $foldersArray,
			'feeds' => $feedsArray,
			'activeFeed' => $activeFeed,
			'showAll' => $showAll,
			'userId' => $this->userId,
			'starredCount' => $starredCount
		);

		return $this->renderJSON($result);
	}


	/**
	 * loads the next X feeds from the server
	 */
	public function loadFeed(){
		$feedType = (int)$this->params('type');
		$feedId = (int)$this->params('id');
		$latestFeedId = (int)$this->params('latestFeedId');
		$latestTimestamp = (int)$this->params('latestTimestamp');
		$limit = (int)$this->params('limit');

		// FIXME: integrate latestFeedId, latestTimestamp and limit
		$this->api->setUserValue('lastViewedFeed', $feedId);
		$this->api->setUserValue('lastViewedFeedType', $feedType);

		$showAll = $this->api->getUserValue('showAll');

		$items = $this->itemMapper->getItems($feedType, $feedId, $showAll);
		$itemsArray = $this->itemsToArray($items);

		// update unread count of all feeds
		$feeds = $this->feedMapper->findAll();
		$feedsArray = array();

		foreach($feeds as $feed){
			$unreadCount = $this->itemMapper->countAllStatus($feed->getId(), StatusFlag::UNREAD);
			$unreadArray = array(
				'id' => (int)$feed->getId(),
				'unreadCount' => (int)$unreadCount
			);
			array_push($feedsArray, $unreadArray);
		}

		$result = array(
			'items' => $itemsArray,
			'feeds' => $feedsArray
		);

		return $this->renderJSON($result);

	}


	/**
	 * Used for setting the showAll value from a post request
	 */
	public function setShowAll(){
		$showAll = $this->postParamToBool($this->params('showAll'));
		$this->api->setUserValue('showAll', $showAll);
		return $this->renderJSON();
	}


	/**
	 * Used for setting the showAll value from a post request
	 */
	public function collapseFolder(){
		$folderId = (int)$this->params('folderId');
		$opened = $this->postParamToBool($this->params('opened'));

		$folder = $this->folderMapper->find($folderId);
		$folder->setOpened($opened);
		$this->folderMapper->update($folder);
		return $this->renderJSON();
	}


	/**
	 * Deletes a feed
	 */
	public function deleteFeed(){
		$feedId = (int)$this->params('feedId');
		$this->feedMapper->deleteById($feedId);
		return $this->renderJSON();
	}


	/**
	 * Deletes a folder
	 */
	public function deleteFolder(){
		$folderId = (int)$this->params('folderId');
		$this->folderMapper->deleteById($folderId);
		return $this->renderJSON();
	}


	/**
	 * Sets the status of an item
	 */
	public function setItemStatus(){
		$itemId = (int)$this->params('itemId');
		$status = $this->params('status');
		$item = $this->itemMapper->findById($itemId);

		switch ($status) {
			case 'read':
				$item->setRead();
				break;
			case 'unread':
				$item->setUnread();
				break;
			case 'important':
				$item->setImportant();
				break;
			case 'unimportant':
				$item->setUnimportant();
				break;
			default:
				exit();
				break;
		}

		$this->itemMapper->update($item);
		return $this->renderJSON();
	}


	/**
	 * Changes the name of a folder
	 */
	public function changeFolderName(){
		$folderId = (int)$this->params('folderId');
		$folderName = $this->params('folderName');
		$folder = $this->folderMapper->find($folderId);
		$folder->setName($folderName);
		$this->folderMapper->update($folder);
		return $this->renderJSON();
	}


	/**
	 * Moves a feed to a new folder
	 */
	public function moveFeedToFolder(){
		$feedId = (int)$this->params('feedId');
		$folderId = (int)$this->params('folderId');
		$feed = $this->feedMapper->findById($feedId);
		if($folderId === 0) {
			$this->feedMapper->save($feed, $folderId);
		} else {
			$folder = $this->folderMapper->find($folderId);
			if(!$folder){
				$msgString = 'Can not move feed %s to folder %s';
				$msg = $this->trans->t($msgString, array($feedId, $folderId));
				return $this->renderJSONError($msg, __FILE__);
			}
			$this->feedMapper->save($feed, $folder->getId());
		}
		return $this->renderJSON();
	}


	/**
	 * Pulls new feed items from its url
	 */
	public function updateFeed(){
		$feedId = (int)$this->params('feedId');
		$feed = $this->feedMapper->findById($feedId);
		$newFeed = Utils::fetch($feed->getUrl());

		$newFeedId = false;
		if ($newFeed !== null) {
		    $newFeedId = $this->feedMapper->save($newFeed, $feed->getFolderId());
		}

		if($newFeedId){
			$feeds = array($this->feedMapper->findById($feedId));
			$feedsArray = array(
				'feeds' => $this->feedsToArray($feeds)
			);
			return $this->renderJSON($feedsArray);
		} else {
			$msgString = 'Error updating feed %s';
			$msg = $this->trans->t($msgString, array($feed->getUrl()));
			return $this->renderJSONError($msg, __FILE__);
		}

	}


	/**
	 * Creates a new folder
	 */
	public function createFolder(){
		$folderName = $this->params('folderName');
		$folder = new Folder($folderName);
		$folderId = $this->folderMapper->save($folder);
		$folders = array($this->folderMapper->findById($folderId));
		$foldersArray = array(
			'folders' => $this->foldersToArray($folders)
		);
		return $this->renderJSON($foldersArray);
	}


	/**
	 * Creates a new feed
	 */
	public function createFeed(){
		$feedUrl = trim($this->params('feedUrl'));
		$folderId = (int)$this->params('folderId');

		$folder = $this->folderMapper->findById($folderId);

		if(!$folder && $folderId !== 0){
			$msgString = 'Folder with id %s does not exist';
			$msg = $this->trans->t($msgString, array($folderId));
			var_dump($folder);
			return $this->renderJSONError($msg, __FILE__);
		}

		if($this->feedMapper->findIdFromUrl($feedUrl)){
			$msgString = 'Feed %s does already exist';
			$msg = $this->trans->t($msgString, array($feedUrl));
			return $this->renderJSONError($msg, __FILE__);
		}

		$feed = Utils::fetch($feedUrl);
		if($feed){
			$feedId = $this->feedMapper->save($feed, $folderId);
			$feeds = array($this->feedMapper->findById($feedId));
			$feedsArray = array(
				'feeds' => $this->feedsToArray($feeds)
			);
		return $this->renderJSON($feedsArray);
		} else {
			$msgString = 'Could not create feed %s';
			$msg = $this->trans->t($msgString, array($feedUrl));
			return $this->renderJSONError($msg, __FILE__);
		}
	}


	/**
	 * Sets all items read that are older than the current transmitted
	 * dates and ids
	 */
	public function setAllItemsRead($feedId, $mostRecentItemId){
		$feedId = (int)$this->params('feedId');
		$mostRecentItemId = (int)$this->params('mostRecentItemId');

		$feed = $this->feedMapper->findById($feedId);

		if($feed){
			$this->itemMapper->markAllRead($feed->getId(), $mostRecentItemId);

			$feeds = array($this->feedMapper->findById($feed->getId()));
			$feedsArray = array(
				'feeds' => $this->feedsToArray($feeds)
			);
			return $this->renderJSON($feedsArray);
		}

	}

}
