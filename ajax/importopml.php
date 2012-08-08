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
	OCP\Util::writeLog('news','ajax/importopml.php: '.$msg, OCP\Util::ERROR);
	exit();
}

if(!isset($_POST['path'])) {
	bailOut($l->t('No file path was submitted.'));
} 

require_once('news/opmlparser.php');

$raw = file_get_contents($_POST['path']);

try {
	$parsed = OPMLParser::parse($raw);
} catch (Exception $e) {
	bailOut($e->getMessage());
}

if ($parsed == null) {
	bailOut($l->t('An error occurred while parsing the file.'));	
}

// $ch is the handler for the curl connection
function addFeed($feedurl, $folderid, $ch) {

	$data = array('feedurl' => $feedurl, 'folderid' => $folderid);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	$result = curl_exec($ch);

	if($result === false) {
		bailOut(curl_error($ch));
	} else {
		bailOut($result);      
	}
}

$data = $parsed->getData();
$url = OCP\Util::linkToAbsolute('news', 'ajax/createfeed.php');
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, TRUE);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

foreach($data as $collection) {
	if ($collection instanceOf OC_News_Feed) {
		$feedurl = $collection->getUrl(); 
		$folderid = 0;
		addFeed($feedurl, $folderid, $ch);
	}
}

addFeed(null, null, $ch);
$result = curl_exec($ch);

curl_close($ch);
	
OCP\JSON::success(array('data' => array('title'=>$parsed->getTitle(), 'count'=>$parsed->getCount())));

