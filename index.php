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

// load SimplePie library
require_once('3rdparty/SimplePie/SimplePieAutoloader.php');

// Check if we are a user
OCP\User::checkLoggedIn();

OCP\App::checkAppEnabled('news');
OCP\App::setActiveNavigationEntry('news');

OCP\Util::addscript('news','news');
OCP\Util::addStyle('news', 'news');

$foldermapper = new OC_News_FolderMapper(OCP\USER::getUser());

$allfeeds = $foldermapper->root();

$tmpl = new OCP\Template( 'news', 'main', 'user' );
$tmpl->assign('allfeeds', $allfeeds);
$tmpl->printPage();

?>
