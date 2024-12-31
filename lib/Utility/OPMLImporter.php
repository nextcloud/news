<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 */

namespace OCA\News\Utility;

use \DOMDocument;
use \DOMElement;
use \DOMText;

/**
 * Imports the OPML
 */
class OPMLImporter
{

    /**
     * The user ID to import to.
     */
    private ?string $userId = null;

    /**
     * Intermediate data.
     */
    private array $feeds = [];
    private array $folders = [];

    /**
     * Imports the OPML
     *
     * @return null|array{0: list<array<string,mixed>>, 1: list<array<string,mixed>>} the items to import
     */
    public function import(string $userId, string $data): ?array
    {
        $this->feeds = [];
        $this->folders = [];
        $this->userId = $userId;

        $document = new DOMDocument('1.0', 'UTF-8');
        $loaded = $document->loadXML($data);
        if ($loaded === false) {
            return null;
        }

        $bodies = $document->getElementsByTagName('body');
        if ($bodies->count() < 1) {
            return null;
        }

        foreach ($bodies[0]->childNodes as $node) {
            if ($node instanceof DOMText) {
                continue;
            }

            $this->outlineToItem($node);
        }

        return [$this->folders, $this->feeds];
    }

    private function outlineToItem(DOMElement $outline, ?string $parent = null): void
    {
        if ($outline->getAttribute('type') === 'rss') {
            // take title if available, otherwise use text #2896
            $title = $outline->getAttribute('title') ?? $outline->getAttribute('text');
            $feed = [
                'link' => $outline->getAttribute('htmlUrl'),
                'url' => $outline->getAttribute('xmlUrl'),
                'title' => $title,
                'folder' => $parent,
            ];

            $this->feeds[] = $feed;
            return;
        }

        $folder = ['name' => $outline->getAttribute('text'), 'parentName' => $parent];

        $this->folders[] = $folder;

        if ($outline->hasChildNodes() === false) {
            return;
        }

        foreach ($outline->childNodes as $child) {
            if ($child instanceof DOMText) {
                continue;
            }

            $this->outlineToItem($child, $folder['name']);
        }
    }
}
