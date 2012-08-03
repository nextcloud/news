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

function printError() {
	OCP\JSON::error(array('data' => array('message' => $l->t('Error removing folder.'))));
	OCP\Util::writeLog('news','ajax/deletefolder.php: Error removing folder: '.$_POST['folderid'], OCP\Util::ERROR);
	exit();
}

$userid = OCP\USER::getUser();

$folderid = trim($_POST['folderid']);

$foldermapper = new OC_News_FolderMapper();

$folder = $foldermapper->find($folderid);
$popfolder = $foldermapper->populate($folder);

// delete child folder
$children = $popfolder->getChildren();
foreach ($children as $child) {
	if ($child instanceOf OC_News_Folder) {
		if(!$foldermapper->deleteById($child->getId()))
			printError();
	}
}

if(!$foldermapper->deleteById($folderid))
	printError();

OCP\JSON::success(array('data' => array( 'folderid' => $folderid )));
