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

$feedId = trim($_POST['feedId']);

$l = OC_L10N::get('news');

$itemsTpl = new OCP\Template("news", "part.items");
$itemsTpl->assign('feedid', $feedId);
$feedItems = $itemsTpl->fetchPage();

$feedMapper = new OCA\News\FeedMapper();
$feed = $feedMapper->findById($feedId);
$feedTitle = $feed->getTitle();

$itemMapper = new OCA\News\ItemMapper();
$unreadItemCount = $itemMapper->countAllStatus($feedId, OCA\News\StatusFlag::UNREAD);

OCP\JSON::success(array('data' => array( 'message' => $l->t('Feed loaded!'),
                                        'feedTitle' => $feedTitle,
					                   'feedItems' => $feedItems,
                                       'unreadItemCount' => $unreadItemCount )));

