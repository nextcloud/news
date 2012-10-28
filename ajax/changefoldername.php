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
$folderName = $_POST['folderName'];

$folderMapper = new OCA\News\FolderMapper();
$folder = $folderMapper->find($folderId);
$folder->setName($folderName);
$success = $folderMapper->update($folder);

$l = OC_L10N::get('news');

if(!$success) {
    OCP\JSON::error(array('data' => array('message' => $l->t('Error changing name of folder ' . $folderId . ' to ' . $folderName))));
    OCP\Util::writeLog('news','ajax/setallitemsread.php: Error changing name of folder ' . $folderId . ' to ' . $folderName, OCP\Util::ERROR);
    exit();
}

OCP\JSON::success();