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

global $l;
$l = OC_L10N::get('news');

function bailOut($msg) {
	global $eventSource;
	$eventSource->send('error', $msg);
	$eventSource->close();
	OCP\Util::writeLog('news','ajax/importopml.php: '.$msg, OCP\Util::ERROR);
	exit();
}

global $eventSource;
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

function importFeed($feedurl, $folderid, $feedtitle) {

	global $eventSource;
	global $l;

	$feedmapper = new OCA\News\FeedMapper();
	$feedid = $feedmapper->findIdFromUrl($feedurl);

	if ($feedid === null) {
		$feed = OCA\News\Utils::slimFetch($feedurl);
		
		if ($feed !== null) {
		      $feed->setTitle($feedtitle); //we want the title of the feed to be the one from the opml file
		      $feedid = $feedmapper->save($feed, $folderid);
		      
		      $itemmapper = new OCA\News\ItemMapper(OCP\USER::getUser());
		      $unreadItemsCount = $itemmapper->countAllStatus($feedid, OCA\News\StatusFlag::UNREAD);

		      $tmpl_listfeed = new OCP\Template("news", "part.listfeed");
		      $tmpl_listfeed->assign('feed', $feed);
		      $tmpl_listfeed->assign('unreadItemsCount', $unreadItemsCount);
		      $listfeed = $tmpl_listfeed->fetchPage();
		      
		      $eventSource->send('progress', array('data' => array('type'=>'feed', 'folderid'=>$folderid, 'listfeed'=>$listfeed)));
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

	global $eventSource;
	global $l;
	
	$foldermapper = new OCA\News\FolderMapper();

	if($parentid != 0) {
	    $folder = new OCA\News\Folder($name, null, $foldermapper->find($parentid));
	} else {
	    $folder = new OCA\News\Folder($name);
	}

	$folderid = $foldermapper->save($folder);

	$tmpl = new OCP\Template("news" , "part.listfolder");
	$tmpl->assign("folder", $folder);
	$listfolder = $tmpl->fetchPage();
	
	$eventSource->send('progress', array('data' => array('type'=>'folder', 'listfolder'=>$listfolder)));
	
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
			$feedtitle = $collection->getTitle();
			if (importFeed($feedurl, $parentid, $feedtitle)) {
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
