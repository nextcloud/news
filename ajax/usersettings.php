<?php
/**
* ownCloud - News app
*
* @author Bernhard Posselt
* Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
* 
* This file is licensed under the Affero General Public License version 3 or later.
* See the COPYING-README file
* 
*/

// Check if we are a user
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('news');
OCP\JSON::callCheck();

if(isset($_POST['showAll'])) {
    if($_POST['showAll'] === 'false') {
        $showAll = false;
    } else {
        $showAll = true;
    }
    OCP\Config::setUserValue(OCP\USER::getUser(), 'news', 'showAll', $showAll);     
}

OCP\JSON::success();    
