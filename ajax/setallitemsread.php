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

$feedId = $_POST['feedId'];
$mostRecentItemId = (int)$_POST['mostRecentItemId'];

$itemMapper = new OCA\News\ItemMapper();

//echo $mostRecentItem->getDate();
switch ($feedId) {
    case -2:
        $items = $itemMapper->findEveryItemByStatus(OCA\News\StatusFlag::UNREAD);
        break;
    
    case -1:
        $items = $itemMapper->findEveryItemByStatus(OCA\News\StatusFlag::UNREAD | OCA\News\StatusFlag::IMPORTANT);
        break;

    default:
        $items = $itemMapper->findAllStatus($feedId, OCA\News\StatusFlag::UNREAD);        
        break;
}


// FIXME: maybe there is a way to set all items read in the
// FeedMapper instead of iterating through every item and updating as 
// necessary
$success = false;
if($mostRecentItemId !== 0) {
    $mostRecentItem = $itemMapper->findById($mostRecentItemId);
}
foreach($items as $item) {
    // FIXME: this should compare the modified date
    if($mostRecentItemId === 0 || $item->getDate() <= $mostRecentItem->getDate()) {
        $item->setRead();
        $success = $itemMapper->update($item);    
    }
}

$l = OC_L10N::get('news');

if(!$success) {
    OCP\JSON::error(array('data' => array('message' => $l->t('Error setting all items as read.'))));
    OCP\Util::writeLog('news','ajax/setallitemsread.php: Error setting all items as read of feed '. $feedId, OCP\Util::ERROR);
    exit();
}

//TODO: replace the following with a real success case. see contact/ajax/createaddressbook.php for inspirations
OCP\JSON::success(array('data' => array('feedId' => $feedId )));
