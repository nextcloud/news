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

function debug($msg) {
	OCP\Util::writeLog('news','ajax/importopml.php: '.$msg, OCP\Util::DEBUG);
}

if(!isset($_POST['path'])) {
	bailOut($l->t('No file path was submitted.'));
} 

require_once('news/opmlparser.php');

$raw = file_get_contents($_POST['path']);

$parser = new OPMLParser($raw);
$title = $parser->getTitle();
$data = $parser->parse();
$count = 0; //number of feeds imported 

OCP\JSON::success(array('data' => array('title'=>$title, 'count'=>$count)));

