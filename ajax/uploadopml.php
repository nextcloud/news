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

$l = OC_L10N::get('news');

function bailOut($msg) {
	OCP\JSON::error(array('data' => array('message' => $msg)));
	OCP\Util::writeLog('news','ajax/uploadopml.php: '.$msg, OCP\Util::ERROR);
	exit();
}

if (isset($_POST['path'])) {
	OCP\JSON::success(array('data' => array('source'=> 'cloud', 'path' => $_POST['path'])));

}
elseif (isset($_FILES['file'])) {
	$storage = \OCP\Files::getStorage('news');
	$filename = 'opmlfile.xml';
	if ($storage->fromTmpFile($_FILES['file']['tmp_name'], $filename)) {
		OCP\JSON::success(array('data' => array('source'=> 'local', 'path' => $filename)));
	} else {
		bailOut('Could not create the temporary file');
	}
	
}
else {
	bailOut('No file loaded');
}
