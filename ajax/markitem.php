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

$itemid = trim($_POST['itemid']);

$itemmapper = new OC_News_ItemMapper();
$item = $itemmapper->find($itemid);
$feedid = $itemmapper->save($feed, 0);

$l = OC_L10N::get('news');

if(!$feedid) {
	OCP\JSON::error(array('data' => array('message' => $l->t('Error adding folder.'))));
	OCP\Util::writeLog('news','ajax/newfeed.php: Error adding feed: '.$_POST['feedurl'], OCP\Util::ERROR);
	exit();
}

//TODO: replace the following with a real success case. see contact/ajax/createaddressbook.php for inspirations
OCP\JSON::success(array('data' => array('message' => $l->t('Feed added!'))));

