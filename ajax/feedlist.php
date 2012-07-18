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

$feedmapper = new OC_News_FeedMapper();
$feeds = $feedmapper->findAll($userid);

$l = OC_L10N::get('news');

if($feeds == null) {
	OCP\JSON::error(array('data' => array('message' => $l->t('Error adding folder.'))));
// 	FIXME undefinded index feedurl
	OCP\Util::writeLog('news','ajax/feedlist.php: Error updating feeds: '.$_POST['feedurl'], OCP\Util::ERROR);
	exit();
}

OCP\JSON::success(array('data' => $feeds));

