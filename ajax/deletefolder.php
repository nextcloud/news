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

$folderid = trim($_POST['folderid']);
$shownfeedid = trim($_POST['shownfeedid']);
$part_items = false;

$foldermapper = new OCA\News\FolderMapper($userid);

if(!$foldermapper->deleteById($folderid)) {
	OCP\JSON::error(array('data' => array('message' => $l->t('Error removing folder.'))));
	OCP\Util::writeLog('news','ajax/deletefolder.php: Error removing folder: '.$_POST['folderid'], OCP\Util::ERROR);
	exit();
}

// lets check if the currently shown feed is among the deleted feeds
if ($shownfeedid != null) {
	$feedmapper = new OCA\News\FeedMapper();
	if (!$feedmapper->findById($shownfeedid)) {
		$tmpl = new OCP\Template("news", "part.items.deleted");
		$part_items = $tmpl->fetchPage();
	}
}

OCP\JSON::success(array('data' => array( 'folderid' => $folderid, 'part_items' => $part_items )));
