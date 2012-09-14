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

$userid = OCP\USER::getUser();

$feedid = $_POST['feedid'];
$feedurl = $_POST['feedurl'];
$folderid = $_POST['folderid'];

$newfeed = OCA\News\Utils::fetch($feedurl);

$newfeedid = false;

if ($newfeed !== null) {
      $feedmapper = new OCA\News\FeedMapper();
      $newfeedid = $feedmapper->save($newfeed, $folderid);
}

$l = OC_L10N::get('news');

if(!$newfeedid) {
	OCP\JSON::error(array('data' => array('message' => $l->t('Error updating feed.'))));
	OCP\Util::writeLog('news','ajax/updatefeed.php: Error updating feed: '.$_POST['feedid'], OCP\Util::ERROR);
	exit();
}
else {
	$itemmapper = new OCA\News\ItemMapper($userid);
	$unreadcounter = $itemmapper->countAllStatus($newfeedid, OCA\News\StatusFlag::UNREAD);
	
	OCP\JSON::success(array('data' => array('message' => $l->t('Feed updated!'), 'unreadcount' => $unreadcounter)));
	exit();
}
