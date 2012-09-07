<?php
/**
* ownCloud - News app
*
* @author Alessandro Cosentino
* Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
*
* This file is licensed under the Affero General Public License version 3 or later.
* See the COPYING-README file
*
*/

// Check if we are a user
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('news');
OCP\JSON::callCheck();

$userid = OCP\USER::getUser();

$feedId = $_POST['id'];
$feedType = $_POST['type'];
OCP\Config::setUserValue(OCP\USER::getUser(), 'news', 'lastViewedFeed', $feedId); 
OCP\Config::setUserValue(OCP\USER::getUser(), 'news', 'lastViewedFeedType', $feedType); 

$l = OC_L10N::get('news');

$itemsTpl = new OCP\Template("news", "part.items");
$itemsTpl->assign('feedid', $feedId);
$feedItems = $itemsTpl->fetchPage();

$itemMapper = new OCA\News\ItemMapper();


switch ($feedId) {
    case -1:
        $feedTitle = $l->t('Starred');
        $unreadItemCount = $itemMapper->countAllStatus($feedId, OCA\News\StatusFlag::IMPORTANT);
        break;

    case -2:
        $feedTitle = $l->t('New articles');
        $unreadItemCount = $itemMapper->countEveryItemByStatus(OCA\News\StatusFlag::UNREAD);
        break;
    
    default:
        $feedMapper = new OCA\News\FeedMapper();
        $feed = $feedMapper->findById($feedId);
        $feedTitle = $feed->getTitle();
        $unreadItemCount = $itemMapper->countAllStatus($feedId, OCA\News\StatusFlag::UNREAD);
        break;
}

OCP\JSON::success(array('data' => array( 'message' => $l->t('Feed loaded!'),
                                        'feedTitle' => $feedTitle,
					                   'feedItems' => $feedItems,
                                       'unreadItemCount' => $unreadItemCount )));
