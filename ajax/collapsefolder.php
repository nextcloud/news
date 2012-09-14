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
if($_POST['opened'] === 'false'){
    $opened = false;
} else {
    $opened = true;
}


$folderMapper = new OCA\News\FolderMapper();
$folder = $folderMapper->find($folderId);
$folder->setOpened($opened);
echo $folder->getOpened();
$success = $folderMapper->update($folder);

$l = OC_L10N::get('news');

if(!$success) {
    OCP\JSON::error(array('data' => array('message' => $l->t('Error collapsing folder.'))));
    OCP\Util::writeLog('news','ajax/setallitemsread.php: Error collapsing folder with id '. $folderId, OCP\Util::ERROR);
    exit();
}

OCP\JSON::success();