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
session_write_close();

$folderId = (int)$_POST['folderId'];
$feedId = $_POST['feedId'];


$feedMapper = new OCA\News\FeedMapper();
$feed = $feedMapper->findById($feedId);

if($folderId === 0) {
    $success = $feedMapper->save($feed, $folderId);
} else {
    $folderMapper = new OCA\News\FolderMapper();
    $folder = $folderMapper->find($folderId);
    $success = $feedMapper->save($feed, $folder->getId());
}

$l = OC_L10N::get('news');

if(!$success) {
    OCP\JSON::error(array('data' => array('message' => $l->t('Error moving feed into folder.'))));
    OCP\Util::writeLog('news','ajax/setallitemsread.php: Error moving feed ' . $feedId . ' into folder '. $folderId, OCP\Util::ERROR);
    exit();
}

OCP\JSON::success();