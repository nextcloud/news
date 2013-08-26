<?php

/**
* ownCloud - News
*
* @author Alessandro Cosentino
* @author Bernhard Posselt
* @copyright 2012 Alessandro Cosentino cosenal@gmail.com
* @copyright 2012 Bernhard Posselt dev@bernhard-posselt.com
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
* License as published by the Free Software Foundation; either
* version 3 of the License, or any later version.
*
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*
* You should have received a copy of the GNU Affero General Public
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
*
*/

namespace OCA\News\Utility;

/**
* Exports the OPML
*/
class OPMLExporter {
	
	/**
	 * Generates the OPML for the active user
	 * @return the OPML as string
	 */
	public function build($folders, $feeds){
		$document = new \DomDocument('1.0', 'UTF-8');
		$document->formatOutput = true;

		$root = $document->createElement('opml');
		$root->setAttribute('version', '2.0');

		// head
		$head = $document->createElement('head');

		$title = $document->createElement('title', 'Subscriptions');
		$head->appendChild( $title );
		
		$root->appendChild($head);
		
		// body
		$body = $document->createElement('body');

		// feeds with folders
		foreach($folders as $folder) {
			$folderOutline = $document->createElement('outline');
			$folderOutline->setAttribute('title', $folder->getName());
			$folderOutline->setAttribute('text', $folder->getName());
			
			// feeds in folders
			foreach ($feeds as $feed) {
				if ($feed->getFolderId() === $folder->getId()) {
					$feedOutline = $this->createFeedOutline($feed, $document);
					$folderOutline->appendChild($feedOutline);
				}
			}

			$body->appendChild($folderOutline);
		}

		// feeds without folders
		foreach ($feeds as $feed) {
			if ($feed->getFolderId() === 0) {
				$feedOutline = $this->createFeedOutline($feed, $document);
				$body->appendChild($feedOutline);
			}
		}

		$root->appendChild($body);

		$document->appendChild($root);

		return $document;
	}


	protected function createFeedOutline($feed, $document) {
		$feedOutline = $document->createElement('outline');
		$feedOutline->setAttribute('title', $feed->getTitle());
		$feedOutline->setAttribute('text', $feed->getTitle());
		$feedOutline->setAttribute('type', 'rss');
		$feedOutline->setAttribute('xmlUrl', $feed->getUrl());
		$feedOutline->setAttribute('htmlUrl', $feed->getLink());
		return $feedOutline;
	}


}

