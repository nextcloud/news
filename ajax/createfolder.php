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
session_write_close();

$userid = OCP\USER::getUser();

$name = trim($_POST['name']);
$parentid = trim($_POST['parentid']);

$foldermapper = new OCA\News\FolderMapper($userid);

if($parentid != 0) {
    $folder = new OCA\News\Folder($name, null, $foldermapper->find($parentid));
} else {
    $folder = new OCA\News\Folder($name);
}

$folderid = $foldermapper->save($folder);

$l = OC_L10N::get('news');

if(!$folderid) {
	OCP\JSON::error(array('data' => array('message' => $l->t('Error adding folder.'))));
	OCP\Util::writeLog('news','ajax/createfolder.php: Error adding folder: '.$_POST['name'], OCP\Util::ERROR);
	exit();
}

$tmpl = new OCP\Template("news" , "part.listfolder");
$tmpl->assign("folder", $folder);
$listfolder = $tmpl->fetchPage();

//TODO: replace the following with a real success case. see contact/ajax/createaddressbook.php for inspirations
OCP\JSON::success(array('data' => array('message' => $l->t('Folder added!'), 'listfolder' => $listfolder)));
