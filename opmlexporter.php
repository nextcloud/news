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

function feedsToXML($data, $xml_el, $dom) {

	foreach($data as $collection) {
		$outline_el = $dom->createElement('outline');
		if ($collection instanceOf OCA\News\Folder) {
			$outline_el->setAttribute('title', $collection->getName());
			$outline_el->setAttribute('text', $collection->getName());
			feedsToXML($collection->getChildren(), $outline_el, $dom);
		}
		elseif ($collection instanceOf OCA\News\Feed) {
			$outline_el->setAttribute('title', $collection->getTitle());
			$outline_el->setAttribute('text', $collection->getTitle());
			$outline_el->setAttribute('type', 'rss');
			$outline_el->setAttribute('xmlUrl', $collection->getUrl());
		}
		$xml_el->appendChild( $outline_el );
	}
}

$l = OC_L10N::get('news');

OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('news');

$userid = OCP\USER::getUser();
$foldermapper = new OCA\News\FolderMapper($userid);
$allfeeds = $foldermapper->childrenOfWithFeeds(0);

header('Content-Type: application/x.opml+xml');
$filename = 'ownCloud ' . $l->t('News') . ' ' . $userid; 
header('Content-Disposition: inline; filename="' . $filename . '.opml"');

$dom = new DomDocument('1.0', 'UTF-8');
$dom->formatOutput = true;

$opml_el = $dom->createElement('opml');
$opml_el->setAttribute('version', '2.0');

$head_el = $dom->createElement('head');
$title_el = $dom->createElement('title', $userid . ' ' . $l->t('subscriptions in ownCloud - News'));
$head_el->appendChild( $title_el );
$opml_el->appendChild( $head_el );

$body_el = $dom->createElement('body');
feedsToXML($allfeeds, $body_el, $dom);

$opml_el->appendChild( $body_el );
$dom->appendChild( $opml_el );

echo $dom->saveXML();