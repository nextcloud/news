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

class NewsController extends Controller {

    /**
     * Decides wether to show the feedpage or the firstrun page
     */
    public function index(){
        $feedMapper = new FeedMapper($this->userId);

        if($feedMapper->feedCount() > 0){
            $this->feedPage();
        } else {
            $this->firstRun();
        }
    }


    public function firstRun(){
        $this->addScript('news');
        $this->addScript('firstrun');
        $this->addStyle('firstrun');
        $this->render('firstrun');
    }


    public function feedPage(){
        $this->addScript('main');
        $this->addScript('news');
        $this->addScript('menu');
        $this->addScript('items');
        $this->add3rdPartyScript('jquery.timeago');

        $this->addStyle('news');
        $this->addStyle('settings');

        $folderMapper = new FolderMapper($this->userId);
        $feedMapper = new FeedMapper($this->userId);
        $itemMapper = new ItemMapper($this->userId);

        // if no feed id is passed as parameter, then show the last viewed feed on reload
        $lastViewedFeedId = isset( $_GET['feedid'] ) ? $_GET['feedid'] : (int)$this->getUserValue('lastViewedFeed');
        $lastViewedFeedType = isset( $_GET['feedid'] ) ? FeedType::FEED : (int)$this->getUserValue('lastViewedFeedType');
        
	$showAll = $this->getUserValue('showAll');

        if( $lastViewedFeedId === null || $lastViewedFeedType === null) {
            $lastViewedFeedId = $feedMapper->mostRecent();
        } else {
            // check if the last selected feed or folder exists
            if( (
                    $lastViewedFeedType === FeedType::FEED &&
                    $feedMapper->findById($lastViewedFeedId) === null
                ) ||
                (
                    $lastViewedFeedType === FeedType::FOLDER &&
                    $folderMapper->findById($lastViewedFeedId) === null
                ) ){
                $lastViewedFeedId = $feedMapper->mostRecent();
            }
        }

        $feeds = $folderMapper->childrenOfWithFeeds(0);
        $folderForest = $folderMapper->childrenOf(0); //retrieve all the folders
        $starredCount = $itemMapper->countEveryItemByStatus(StatusFlag::IMPORTANT);
        $items = $this->getItems($lastViewedFeedType, $lastViewedFeedId, $showAll);

        $params = array(
            'allfeeds' => $feeds,
            'folderforest' => $folderForest,
            'showAll' => $showAll,
            'lastViewedFeedId' => $lastViewedFeedId,
            'lastViewedFeedType' => $lastViewedFeedType,
            'starredCount' => $starredCount,
            'items' => $items
        );

        $this->render('main', $params, array('items' => true));
    }


    /**
     * Returns all items
     * @param $feedType the type of the feed
     * @param $feedId the id of the feed or folder
     * @param $showAll if true, it will also include unread items
     * @return an array with all items
     */
    public function getItems($feedType, $feedId, $showAll){
        $items = array();
        $itemMapper = new ItemMapper($this->userId);

        // starred or subscriptions
        if ($feedType === FeedType::STARRED || $feedType === FeedType::SUBSCRIPTIONS) {

            if($feedType === FeedType::STARRED){
                $statusFlag = StatusFlag::IMPORTANT;
            }

            if($feedType === FeedType::SUBSCRIPTIONS){
                $statusFlag = StatusFlag::UNREAD;
            }

            $items = $itemMapper->findEveryItemByStatus($statusFlag);

        // feed
        } elseif ($feedType === FeedType::FEED){

            if($showAll) {
                $items = $itemMapper->findByFeedId($feedId);
            } else {
                $items = $itemMapper->findAllStatus($feedId, StatusFlag::UNREAD);
            }

        // folder
        } elseif ($feedType === FeedType::FOLDER){
            $feedMapper = new FeedMapper($this->userId);
            $feeds = $feedMapper->findByFolderId($feedId);

            foreach($feeds as $feed){
                if($showAll) {
                    $items = array_merge($items, $itemMapper->findByFeedId($feed->getId()));
                } else {
                    $items = array_merge($items,
                        $itemMapper->findAllStatus($feed->getId(), StatusFlag::UNREAD));
                }
            }
        }
        return $items;
    }


    /**
     * Returns the unread count
     * @param $feedType the type of the feed
     * @param $feedId the id of the feed or folder
     * @return the unread count
     */
    public function getItemUnreadCount($feedType, $feedId){
        $unreadCount = 0;
        $itemMapper = new ItemMapper($this->userId);

        switch ($feedType) {
            case FeedType::STARRED:
                $unreadCount = $itemMapper->countEveryItemByStatus(StatusFlag::IMPORTANT);
                break;

            case FeedType::SUBSCRIPTIONS:
                $unreadCount = $itemMapper->countEveryItemByStatus(StatusFlag::UNREAD);
                break;

            case FeedType::FOLDER:
                $feedMapper = new FeedMapper($this->userId);
                $feeds = $feedMapper->findByFolderId($feedId);
                foreach($feeds as $feed){
                    $unreadCount += $itemMapper->countAllStatus($feed->getId(), StatusFlag::UNREAD);
                }
                break;

            case FeedType::FEED:
                $unreadCount = $itemMapper->countAllStatus($feedId, StatusFlag::UNREAD);
                break;
        }

        return $unreadCount;
    }


}
