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

$itemId = $_POST['itemId'];
$isImportant = $_POST['isImportant'];

$itemMapper = new OCA\News\ItemMapper();
$item = $itemMapper->find($itemId);

if($isImportant){
    $item->setImportant();    
} else {
    $item->setUnimportant();
}

$success = $itemMapper->update($item);

$l = OC_L10N::get('news');

if(!$success) {
    OCP\JSON::error(array('data' => array('message' => $l->t('Error marking item as important.'))));
    OCP\Util::writeLog('news','ajax/importantitem.php: Error marking item as important: '.$_POST['itemId'], OCP\Util::ERROR);
    exit();
}

//TODO: replace the following with a real success case. see contact/ajax/createaddressbook.php for inspirations
OCP\JSON::success(array('data' => array('itemId' => $itemId)));

