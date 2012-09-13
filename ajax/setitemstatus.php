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

$itemId = $_POST['itemId'];
$status = $_POST['status'];

$itemMapper = new OCA\News\ItemMapper();
$item = $itemMapper->findById($itemId);

switch ($status) {
    case 'read':
        $item->setRead();
        break;
    case 'unread':
        $item->setUnread();
        break;
    case 'important':
        $item->setImportant();
        break;
    case 'unimportant':
        $item->setUnimportant();
        break;
    default:
        break;
}

$success = $itemMapper->update($item);

$l = OC_L10N::get('news');

if(!$success) {
	OCP\JSON::error(array('data' => array('message' => $l->t('Error marking item as read.'))));
	OCP\Util::writeLog('news','ajax/setitemstatus.php: Error setting itemstatus to '. $status .': '.$_POST['itemid'], OCP\Util::ERROR);
	exit();
}

//TODO: replace the following with a real success case. see contact/ajax/createaddressbook.php for inspirations
OCP\JSON::success(array('data' => array('itemId' => $itemId )));
