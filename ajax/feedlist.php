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

$feedmapper = new OCA\News\FeedMapper($userid);
$feeds = $feedmapper->findAll();

$l = OC_L10N::get('news');

if($feeds == null) {
	//TODO: handle error better here
	OCP\JSON::error(array('data' => array('message' => $l->t('Error updating feeds.'))));
	OCP\Util::writeLog('news','ajax/feedlist.php: Error updating feeds', OCP\Util::ERROR);
	exit();
}

OCP\JSON::success(array('data' => $feeds));
