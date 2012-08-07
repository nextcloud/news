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

register_shutdown_function("shutdown");

/* handle the case of fatal error */
function shutdown() {
	$l = OC_L10N::get('news');
	$error = error_get_last();
	if($error['type'] & (E_ERROR | E_COMPILE_ERROR | E_CORE_ERROR)) { //all fatal errors
		if (strpos($error['message'], 'get_uri')) { 
			//handle a fatal error caused by a SimplePie bug (https://github.com/simplepie/simplepie/issues/214)
			OCP\Util::writeLog('news','ajax/createfeed.php: Fatal error:' . $error['message'] , OCP\Util::ERROR);
			exit();
		}
	}
}

// Check if we are a user
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('news');
OCP\JSON::callCheck();

$userid = OCP\USER::getUser();

$feedurl = trim($_POST['feedurl']);
$folderid = trim($_POST['folderid']);

$feedmapper = new OC_News_FeedMapper();
$feedid = $feedmapper->findIdFromUrl($feedurl);

$l = OC_L10N::get('news');

if ($feedid == null) {
	$feed = OC_News_Utils::fetch($feedurl);

	if ($feed != null) {
	      $feedid = $feedmapper->save($feed, $folderid);
	}
} else {
	OCP\JSON::error(array('data' => array('message' => $l->t('Feed already exists.'))));
	OCP\Util::writeLog('news','ajax/createfeed.php: Error adding feed: '.$_POST['feedurl'], OCP\Util::ERROR);
	exit();
}

if($feed == null || !$feedid) {
	OCP\JSON::error(array('data' => array('message' => $l->t('Error adding feed.'))));
	OCP\Util::writeLog('news','ajax/createfeed.php: Error adding feed: '.$_POST['feedurl'], OCP\Util::ERROR);
	exit();
}

// FIXME: assign counter
$tmpl_listfeed = new OCP\Template("news", "part.listfeed");
$tmpl_listfeed->assign('child', $feed);
$listfeed = $tmpl_listfeed->fetchPage();

$tmpl_newfeed = new OCP\Template("news", "part.items.new");
$tmpl_newfeed->assign('title', $feed->getTitle());
$part_newfeed = $tmpl_newfeed->fetchPage();

$tmpl_items = new OCP\Template("news", "part.items");
$tmpl_items->assign('feedid', $feedid);
$part_items = $tmpl_items->fetchPage();

OCP\JSON::success(array('data' => array( 'message' => $l->t('Feed added!'),
										'feedid' => $feedid,
										'listfeed' => $listfeed,
										'part_items' => $part_items,
										'part_newfeed' => $part_newfeed )));

