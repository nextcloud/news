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
OCP\User::checkLoggedIn();

OCP\App::checkAppEnabled('news');
OCP\App::setActiveNavigationEntry('news');

OCP\Util::addScript('news','news');
OCP\Util::addStyle('news','news');
OCP\Util::addStyle('news','settings');

$l = OC_L10N::get('news');

$userid = OCP\USER::getUser();

$foldermapper = new OC_News_FolderMapper($userid);

$folder = new OC_News_Folder($l->t('Subscriptions'), 0);

$allfeeds = $foldermapper->populate($folder);

if ($allfeeds) {
	$feedid = isset( $_GET['feedid'] ) ? $_GET['feedid'] : null;
	if ($feedid == null) {
		$feedmapper = new OC_News_FeedMapper(OCP\USER::getUser($userid));
		$feedid =  $feedmapper->mostRecent();
	}
}
else {
	$feedid = 0;
}

$tmpl = new OCP\Template( 'news', 'main', 'user' );
$tmpl->assign('allfeeds', $allfeeds);
$tmpl->assign('feedid', $feedid);
$tmpl->printPage();

?>
