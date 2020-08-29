<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Alessandro Cosentino <cosenal@gmail.com>
 * @author    Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright 2012 Alessandro Cosentino
 * @copyright 2012-2014 Bernhard Posselt
 */

namespace OCA\News\Utility;

use \DOMDocument;
use \DOMElement;
use OCA\News\Db\Feed;

/**
 * Exports the OPML
 */
class OPMLExporter
{

    /**
     * Generates the OPML for the active user
     *
     * @param  \OCA\News\Db\Folder[] $folders
     * @param  \OCA\News\Db\Feed[]   $feeds
     * @return DOMDocument the document
     */
    public function build(array $folders, array $feeds)
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $document->formatOutput = true;

        $root = $document->createElement('opml');
        $root->setAttribute('version', '2.0');

        // head
        $head = $document->createElement('head');

        $title = $document->createElement('title', 'Subscriptions');
        $head->appendChild($title);

        $root->appendChild($head);

        // body
        $body = $document->createElement('body');

        // feeds with folders
        foreach ($folders as $folder) {
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

    /**
     * @param Feed $feed
     * @param DOMDocument $document
     * @return DOMElement
     */
    protected function createFeedOutline(Feed $feed, DOMDocument $document)
    {
        $feedOutline = $document->createElement('outline');
        $feedOutline->setAttribute('title', $feed->getTitle());
        $feedOutline->setAttribute('text', $feed->getTitle());
        $feedOutline->setAttribute('type', 'rss');
        $feedOutline->setAttribute('xmlUrl', $feed->getUrl());
        $feedOutline->setAttribute('htmlUrl', $feed->getLink());

        return $feedOutline;
    }
}
