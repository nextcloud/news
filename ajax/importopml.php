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

global $eventSource;

// Check if we are a user
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('news');
OCP\JSON::callCheck();

$l = OC_L10N::get('news');

function bailOut($msg) {
	global $eventSource;
	$eventSource->send('error', $msg);
	$eventSource->close();
	OCP\Util::writeLog('news','ajax/importopml.php: '.$msg, OCP\Util::ERROR);
	exit();
}

$eventSource=new OC_EventSource();

require_once 'news/opmlparser.php';

$source = isset( $_REQUEST['source'] ) ? $_REQUEST['source'] : '';
$path = isset( $_REQUEST['path'] ) ? $_REQUEST['path'] : '';

if($path == '') {
	bailOut($l->t('Empty filename'));
	exit();
}

if($source == 'cloud') {
	$raw = file_get_contents($path);
} elseif ($source == 'local') {
	$storage = \OCP\Files::getStorage('news');
	$raw = $storage->file_get_contents($path);
} else {
	bailOut($l->t('No source argument passed'));
}

if ($raw == false) {
	bailOut($l->t('Error while reading file'));
}

try {
	$parsed = OPMLParser::parse($raw);
} catch (Exception $e) {
	bailOut($e->getMessage());
}

if ($parsed == null) {
	bailOut($l->t('An error occurred while parsing the file.'));
}

$data = $parsed->getData();

function importFeed($feedurl, $folderid) {
	$feedmapper = new OCA\News\FeedMapper();
	$feedid = $feedmapper->findIdFromUrl($feedurl);

	$l = OC_L10N::get('news');

	if ($feedid === null) {
		$feed = OCA\News\Utils::slimFetch($feedurl);

		if ($feed !== null) {
		      $feedid = $feedmapper->save($feed, $folderid);
		}
	} else {
		OCP\Util::writeLog('news','ajax/importopml.php: This feed is already here: '. $feedurl, OCP\Util::WARN);
		return true;
	}

	if($feed === null || !$feedid) {
		OCP\Util::writeLog('news','ajax/importopml.php: Error adding feed: '. $feedurl, OCP\Util::ERROR);
		return false;
	}

	return true;
}

function importFolder($name, $parentid) {
	$foldermapper = new OCA\News\FolderMapper();

	if($parentid != 0) {
	    $folder = new OCA\News\Folder($name, null, $foldermapper->find($parentid));
	} else {
	    $folder = new OCA\News\Folder($name);
	}

	$folderid = $foldermapper->save($folder);

	$l = OC_L10N::get('news');

	if(!$folderid) {
		OCP\Util::writeLog('news','ajax/importopml.php: Error adding folder' . $name, OCP\Util::ERROR);
		return null;
	}

	return $folderid;
}

function importList($data, $parentid) {
	$countsuccess = 0;
	foreach($data as $collection) {
		if ($collection instanceOf OCA\News\Feed) {
			$feedurl = $collection->getUrl();
			if (importFeed($feedurl, $parentid)) {
				$countsuccess++;
			}
		}
		else if ($collection instanceOf OCA\News\Folder) {
			$folderid = importFolder($collection->getName(), $parentid);
			if ($folderid) {
				$children = $collection->getChildren();
				$countsuccess += importList($children, $folderid);
			}
		}
		else {
			OCP\Util::writeLog('news','ajax/importopml.php: Error importing OPML',OCP\Util::ERROR);
		}
	}
	return $countsuccess;
}

$countsuccess = importList($data, 0);

$eventSource->send('success', array('data' => array('title'=>$parsed->getTitle(), 'count'=>$parsed->getCount(),
	'countsuccess'=>$countsuccess)));
$eventSource->close();
