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

require_once('controllers/controller.php');
require_once('controllers/news.controller.php');

OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('news');
OCP\App::setActiveNavigationEntry('news');

$controller = new OCA\News\NewsController();

// routes
if(isset($_GET['jstest'])){
	$controller->javascriptTests();
} else {
	$controller->index();	
}