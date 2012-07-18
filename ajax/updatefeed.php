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

$feedid = $_POST['feedid'];
$feedurl = $_POST['feedurl'];
$folderid = $_POST['folderid'];

$newfeed = OC_News_Utils::fetch($feedurl);

$feedmapper = new OC_News_FeedMapper();
$newfeedid = $feedmapper->save($newfeed, $folderid);

$l = OC_L10N::get('news');

if(!$newfeedid) {
	OCP\JSON::error(array('data' => array('message' => $l->t('Error updating feed.'))));
	OCP\Util::writeLog('news','ajax/updatefeed.php: Error updating feed: '.$_POST['feedid'], OCP\Util::ERROR);
	exit();
}

//TODO: replace the following with a real success case. see contact/ajax/createaddressbook.php for inspirations
OCP\JSON::success(array('data' => array('message' => $l->t('Feed updated!'))));

