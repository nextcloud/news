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
session_write_close();

require_once OC_App::getAppPath('news') . '/lib/feedtypes.php';
require_once OC_App::getAppPath('news') . '/controllers/controller.php';
require_once OC_App::getAppPath('news') . '/controllers/news.controller.php';

$userid = OCP\USER::getUser();

$feedId = (int)$_POST['id'];
$feedType = (int)$_POST['type'];


OCP\Config::setUserValue(OCP\USER::getUser(), 'news', 'lastViewedFeed', $feedId);
OCP\Config::setUserValue(OCP\USER::getUser(), 'news', 'lastViewedFeedType', $feedType);

$showAll = OCP\Config::getUserValue(OCP\USER::getUser(), 'news', 'showAll');

$newsController = new OCA\News\NewsController();
$items = $newsController->getItems($feedType, $feedId, $showAll);
$unreadItemCount = $newsController->getItemUnreadCount($feedType, $feedId);

$l = OC_L10N::get('news');


$itemsTpl = new OCP\Template("news", "part.items");
$itemsTpl->assign('lastViewedFeedId', $feedId);
$itemsTpl->assign('lastViewedFeedType', $feedType);
$itemsTpl->assign('items', $items, false);
$feedItems = $itemsTpl->fetchPage();

$itemMapper = new OCA\News\ItemMapper();




OCP\JSON::success(array('data' => array( 'message' => $l->t('Feed loaded!'),
					                   'feedItems' => $feedItems,
                                       'unreadItemCount' => $unreadItemCount )));
