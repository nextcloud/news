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


class FeedType {
    const FEED          = 0;
    const FOLDER        = 1;
    const STARRED       = 2;
    const SUBSCRIPTIONS = 3;
}


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

        // always show the last viewed feed on reload
        $lastViewedId = $this->getUserValue('lastViewedFeed');
        $lastViewedType = $this->getUserValue('lastViewedFeedType');
        $showAll = $this->getUserValue('showAll'); 

        if( $lastViewedId === null || $lastViewedType === null) {
            $lastViewedId = $feedMapper->mostRecent();
        } else {
            // check if the last selected feed or folder exists
            if( (
                    $lastViewedType === FeedType::FEED &&
                    $feedMapper->findById($lastViewedId) === null
                ) || 
                (
                    $lastViewedType === FeedType::FOLDER &&
                    $folderMapper->findById($lastViewedId) === null
                ) ){
                $lastViewedId = $feedMapper->mostRecent();
            }
        }

        $feeds = $folderMapper->childrenOfWithFeeds(0);
        $folderForest = $folderMapper->childrenOf(0); //retrieve all the folders

        $params = array(
            'allfeeds' => $feeds,
            'folderforest' => $folderForest,
            'showAll' => $showAll,
            'lastViewedId' => $lastViewedType,
            'lastViewedType' => $lastViewedType,
            // FIXME: for compability, remove this after refactoring
            'feedid' => $lastViewedId,
            'feedtype' => $lastViewedType,
        );

        $this->render('main', $params);
    }


    public function javascriptTests(){
        $this->add3rdPartyScript('jasmine-1.2.0/jasmine.js');
        $this->add3rdPartyStyle('jasmine-1.2.0/jasmine.css');
        $this->render('javascript.tests');
    }


}

?>