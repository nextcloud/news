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

$l = OC_L10N::get('news');

function bailOut($msg) {
	OCP\JSON::error(array('data' => array('message' => $msg)));
	OCP\Util::writeLog('news','ajax/selectfromcloud.php: '.$msg, OCP\Util::ERROR);
	exit();
}
function debug($msg) {
	OCP\Util::writeLog('news','ajax/selectfromcloud.php: '.$msg, OCP\Util::DEBUG);
}

if(!isset($_GET['path'])) {
	bailOut($l->t('No file path was submitted.'));
}

$localpath = OC_Filesystem::getLocalFile($_GET['path']);
$tmpfname = tempnam(get_temp_dir(), "occOrig");

if(!file_exists($localpath)) {
	bailOut($l->t('File doesn\'t exist:').$localpath);
}

if (file_put_contents($tmpfname, file_get_contents($localpath))) {
	OCP\JSON::success(array('data' => array('tmp'=>$tmpfname, 'path'=>$localpath)));
} else {
	bailOut($l->t('Couldn\'t save temporary image: ').$tmpfname);
}
