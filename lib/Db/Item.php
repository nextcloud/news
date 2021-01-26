<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Alessandro Cosentino <cosenal@gmail.com>
 * @author    Bernhard Posselt <dev@bernhard-posselt.com>
 * @author    Paul Tirk <paultirk@paultirk.com>
 * @copyright 2012 Alessandro Cosentino
 * @copyright 2012-2014 Bernhard Posselt
 * @copyright 2020 Paul Tirk
 */

namespace OCA\News\Db;

use OCP\AppFramework\Db\Entity;

/**
 * Class Item
 *
 * @package OCA\News\Db
 * @Embeddable
 */
class Item extends Entity implements IAPI, \JsonSerializable
{
    use EntityJSONSerializer;

    /** @var string|null */
    protected $contentHash;
    /** @var string */
    protected $guidHash;
    /** @var string */
    protected $guid;
    /** @var string|null */
    protected $url;
    /** @var string|null */
    protected $title;
    /** @var string|null */
    protected $author;
    /** @var int|null */
    protected $pubDate;
    /** @var string|null */
    protected $body;
    /** @var string|null */
    protected $enclosureMime;
    /** @var string|null */
    protected $enclosureLink;
    /** @var string|null */
    protected $mediaThumbnail;
    /** @var string|null */
    protected $mediaDescription;
    /** @var int|null */
    protected $feedId;
    /** @var string|null */
    protected $lastModified = '0';
    /** @var string|null */
    protected $searchIndex;
    /** @var bool */
    protected $rtl = false;
    /** @var string|null */
    protected $fingerprint;
    /** @var bool */
    protected $unread = false;
    /** @var bool */
    protected $starred = false;
    /** @var string|null */
    protected $categoriesJson;
    /** @var string */
    protected $sharedBy = '';
    /** @var string */
    protected $sharedWith = '';


    public function __construct()
    {
        $this->addType('contentHash', 'string');
        $this->addType('guidHash', 'string');
        $this->addType('guid', 'string');
        $this->addType('url', 'string');
        $this->addType('title', 'string');
        $this->addType('author', 'string');
        $this->addType('pubDate', 'integer');
        $this->addType('body', 'string');
        $this->addType('enclosureMime', 'string');
        $this->addType('enclosureLink', 'string');
        $this->addType('mediaThumbnail', 'string');
        $this->addType('mediaDescription', 'string');
        $this->addType('feedId', 'integer');
        $this->addType('lastModified', 'string');
        $this->addType('searchIndex', 'string');
        $this->addType('rtl', 'boolean');
        $this->addType('fingerprint', 'string');
        $this->addType('unread', 'boolean');
        $this->addType('starred', 'boolean');
        $this->addType('categoriesJson', 'string');
        $this->addType('sharedBy', 'string');
        $this->addType('sharedWith', 'string');
    }

    /**
     * @return int
     */
    public function cropApiLastModified(): int
    {
        $lastModified = $this->getLastModified();
        if (strlen((string)$lastModified) > 10) {
            return (int)substr($lastModified, 0, -6);
        } else {
            return (int)$lastModified;
        }
    }

    public static function fromImport($import): Item
    {
        $item = new Item();
        $item->setGuid($import['guid']);
        $item->setGuidHash(md5($import['guid']));
        $item->setUrl($import['url']);
        $item->setTitle($import['title']);
        $item->setAuthor($import['author']);
        $item->setPubDate($import['pubDate']);
        $item->setBody($import['body']);
        $item->setEnclosureMime($import['enclosureMime']);
        $item->setEnclosureLink($import['enclosureLink']);
        $item->setMediaThumbnail($import['mediaThumbnail']);
        $item->setMediaDescription($import['mediaDescription']);
        $item->setRtl($import['rtl']);
        $item->setUnread($import['unread']);
        $item->setStarred($import['starred']);

        return $item;
    }

    public function generateSearchIndex(): void
    {
        $categoriesString = !is_null($this->getCategories())
            ? implode('', $this->getCategories())
            : '';

        $this->setSearchIndex(
            mb_strtolower(
                html_entity_decode(strip_tags($this->getBody())) .
                html_entity_decode($this->getAuthor()) .
                html_entity_decode($this->getTitle()) .
                html_entity_decode($categoriesString) .
                $this->getUrl(),
                'UTF-8'
            )
        );
        $this->setFingerprint($this->computeFingerprint());
        $this->setContentHash($this->computeContentHash());
    }

    /**
     * @return null|string
     */
    public function getAuthor(): ?string
    {
        return $this->author;
    }

    /**
     * @return null|string
     */
    public function getBody(): ?string
    {
        return $this->body;
    }

    /**
     * @return null|string
     */
    public function getContentHash(): ?string
    {
        return $this->contentHash;
    }

    /**
     * @return null|string
     */
    public function getEnclosureLink(): ?string
    {
        return $this->enclosureLink;
    }

    /**
     * @return null|string
     */
    public function getMediaThumbnail(): ?string
    {
        return $this->mediaThumbnail;
    }

    /**
     * @return null|string
     */
    public function getMediaDescription(): ?string
    {
        return $this->mediaDescription;
    }

    /**
     * @return null|string
     */
    public function getEnclosureMime(): ?string
    {
        return $this->enclosureMime;
    }

    /**
     * @return null|int
     */
    public function getFeedId(): ?string
    {
        return $this->feedId;
    }

    /**
     * @return null|string
     */
    public function getFingerprint(): ?string
    {
        return $this->fingerprint;
    }

    public function getGuid(): string
    {
        return $this->guid;
    }

    public function getGuidHash(): string
    {
        return $this->guidHash;
    }

    public function getIntro(): string
    {
        return strip_tags($this->getBody());
    }

    /**
     * @return string|null
     */
    public function getLastModified(): ?string
    {
        return $this->lastModified;
    }

    /**
     * @return int|null
     */
    public function getPubDate(): ?int
    {
        return $this->pubDate;
    }

    public function getRtl(): bool
    {
        return $this->rtl;
    }

    /**
     * @return null|string
     */
    public function getSearchIndex(): ?string
    {
        return $this->searchIndex;
    }

    /**
     * @return null|string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @return null|string
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function isStarred(): bool
    {
        return $this->starred;
    }

    public function isShared(): bool
    {
        return $this->getSharedBy == '' && $this->getSharedWith == '';
    }

    public function isUnread(): bool
    {
        return $this->unread;
    }

    public function getSharedBy(): string
    {
        return $this->sharedBy;
    }

    public function getSharedWith(): string
    {
        return $this->sharedWith;
    }

    /**
     * @return null|string
     */
    public function getCategoriesJson(): ?string
    {
        return $this->categoriesJson;
    }

    /**
     * @return null|array
     */
    public function getCategories(): ?array
    {
        return json_decode($this->getCategoriesJson());
    }

    /**
     * Turns entity attributes into an array
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'guid' => $this->getGuid(),
            'guidHash' => $this->getGuidHash(),
            'url' => $this->getUrl(),
            'title' => $this->getTitle(),
            'author' => $this->getAuthor(),
            'pubDate' => $this->getPubDate(),
            'updatedDate' => null,
            'body' => $this->getBody(),
            'enclosureMime' => $this->getEnclosureMime(),
            'enclosureLink' => $this->getEnclosureLink(),
            'mediaThumbnail' => $this->getMediaThumbnail(),
            'mediaDescription' => $this->getMediaDescription(),
            'feedId' => $this->getFeedId(),
            'unread' => $this->isUnread(),
            'starred' => $this->isStarred(),
            'lastModified' => $this->getLastModified(),
            'rtl' => $this->getRtl(),
            'intro' => $this->getIntro(),
            'fingerprint' => $this->getFingerprint(),
            'categories' => $this->getCategories(),
            'sharedBy' => $this->getSharedBy(),
            'sharedWith' => $this->getSharedWith(),
            'isShared' => $this->isShared()
        ];
    }

    public function setAuthor(string $author = null): self
    {
        $author = strip_tags($author);

        if ($this->author !== $author) {
            $this->author = $author;
            $this->markFieldUpdated('author');
        }

        return $this;
    }

    public function setBody(string $body = null): self
    {
        // FIXME: this should not happen if the target="_blank" is already
        // on the link
        $body = str_replace('<a ', '<a target="_blank" rel="noreferrer" ', $body);

        if ($this->body !== $body) {
            $this->body = $body;
            $this->markFieldUpdated('body');
        }

        return $this;
    }

    public function setContentHash(string $contentHash = null): self
    {
        if ($this->contentHash !== $contentHash) {
            $this->contentHash = $contentHash;
            $this->markFieldUpdated('contentHash');
        }

        return $this;
    }

    public function setEnclosureLink(string $enclosureLink = null): self
    {
        if ($this->enclosureLink !== $enclosureLink) {
            $this->enclosureLink = $enclosureLink;
            $this->markFieldUpdated('enclosureLink');
        }

        return $this;
    }

    public function setEnclosureMime(string $enclosureMime = null): self
    {
        if ($this->enclosureMime !== $enclosureMime) {
            $this->enclosureMime = $enclosureMime;
            $this->markFieldUpdated('enclosureMime');
        }

        return $this;
    }

    public function setMediaThumbnail(string $mediaThumbnail = null): self
    {
        if ($this->mediaThumbnail !== $mediaThumbnail) {
            $this->mediaThumbnail = $mediaThumbnail;
            $this->markFieldUpdated('mediaThumbnail');
        }

        return $this;
    }

    public function setMediaDescription(string $mediaDescription = null): self
    {
        if ($this->mediaDescription !== $mediaDescription) {
            $this->mediaDescription = $mediaDescription;
            $this->markFieldUpdated('mediaDescription');
        }

        return $this;
    }

    public function setFeedId(?int $feedId = null): self
    {
        if ($this->feedId !== $feedId) {
            $this->feedId = $feedId;
            $this->markFieldUpdated('feedId');
        }

        return $this;
    }

    public function setFingerprint(string $fingerprint = null): self
    {
        if ($this->fingerprint !== $fingerprint) {
            $this->fingerprint = $fingerprint;
            $this->markFieldUpdated('fingerprint');
        }

        return $this;
    }

    public function setGuid(string $guid): self
    {
        if ($this->guid !== $guid) {
            $this->guid = $guid;
            $this->markFieldUpdated('guid');
        }

        return $this;
    }

    public function setGuidHash(string $guidHash): self
    {
        if ($this->guidHash !== $guidHash) {
            $this->guidHash = $guidHash;
            $this->markFieldUpdated('guidHash');
        }

        return $this;
    }

    public function setLastModified(string $lastModified = null): self
    {
        if ($this->lastModified !== $lastModified) {
            $this->lastModified = $lastModified;
            $this->markFieldUpdated('lastModified');
        }

        return $this;
    }

    public function setPubDate(int $pubDate = null): self
    {
        if ($this->pubDate !== $pubDate) {
            $this->pubDate = $pubDate;
            $this->markFieldUpdated('pubDate');
        }

        return $this;
    }

    public function setRtl(bool $rtl): self
    {
        if ($this->rtl !== $rtl) {
            $this->rtl = $rtl;
            $this->markFieldUpdated('rtl');
        }

        return $this;
    }

    public function setSearchIndex(string $searchIndex = null): self
    {
        if ($this->searchIndex !== $searchIndex) {
            $this->searchIndex = $searchIndex;
            $this->markFieldUpdated('searchIndex');
        }

        return $this;
    }

    public function setStarred(bool $starred): self
    {
        if ($this->starred !== $starred) {
            $this->starred = $starred;
            $this->markFieldUpdated('starred');
        }

        return $this;
    }

    public function setTitle(string $title = null): self
    {
        $title = trim(strip_tags($title));

        if ($this->title !== $title) {
            $this->title = $title;
            $this->markFieldUpdated('title');
        }

        return $this;
    }

    public function setSharedBy(string $sharedBy): self
    {
        if ($this->sharedBy !== $sharedBy) {
            $this->sharedBy = $sharedBy;
            $this->markFieldUpdated('sharedBy');
        }

        return $this;
    }

    public function setSharedWith(string $sharedWith): self
    {
        if ($this->sharedWith !== $sharedWith) {
            $this->sharedWith = $sharedWith;
            $this->markFieldUpdated('sharedWith');
        }

        return $this;
    }

    public function setUnread(bool $unread): self
    {
        if ($this->unread !== $unread) {
            $this->unread = $unread;
            $this->markFieldUpdated('unread');
        }

        return $this;
    }

    public function setUrl(string $url = null): self
    {
        $url = trim($url);
        if ((strpos($url, 'http') === 0 || strpos($url, 'magnet') === 0)
            && $this->url !== $url
        ) {
            $this->url = $url;
            $this->markFieldUpdated('url');
        }

        return $this;
    }

    public function setCategoriesJson(string $categoriesJson = null): self
    {
        if ($this->categoriesJson !== $categoriesJson) {
            $this->categoriesJson = $categoriesJson;
            $this->markFieldUpdated('categoriesJson');
        }

        return $this;
    }

    public function setCategories(array $categories = null): self
    {
        $categoriesJson = !is_null($categories) ? json_encode($categories) : null;
        $this->setCategoriesJson($categoriesJson);

        return $this;
    }

    public function toAPI(): array
    {
        return [
            'id' => $this->getId(),
            'guid' => $this->getGuid(),
            'guidHash' => $this->getGuidHash(),
            'url' => $this->getUrl(),
            'title' => $this->getTitle(),
            'author' => $this->getAuthor(),
            'pubDate' => $this->getPubDate(),
            'updatedDate' => null,
            'body' => $this->getBody(),
            'enclosureMime' => $this->getEnclosureMime(),
            'enclosureLink' => $this->getEnclosureLink(),
            'mediaThumbnail' => $this->getMediaThumbnail(),
            'mediaDescription' => $this->getMediaDescription(),
            'feedId' => $this->getFeedId(),
            'unread' => $this->isUnread(),
            'starred' => $this->isStarred(),
            'lastModified' => $this->cropApiLastModified(),
            'rtl' => $this->getRtl(),
            'fingerprint' => $this->getFingerprint(),
            'contentHash' => $this->getContentHash()
        ];
    }

    public function toAPI2(bool $reduced = false): array
    {
        if ($reduced) {
            return [
                'id' => $this->getId(),
                'isUnread' => $this->isUnread(),
                'isStarred' => $this->isStarred()
            ];
        }

        return [
            'id' => $this->getId(),
            'url' => $this->getUrl(),
            'title' => $this->getTitle(),
            'author' => $this->getAuthor(),
            'publishedAt' => date('c', $this->getPubDate()),
            'lastModifiedAt' => date('c', $this->cropApiLastModified()),
            'enclosure' => [
                'mimeType' => $this->getEnclosureMime(),
                'url' => $this->getEnclosureLink()
            ],
            'body' => $this->getBody(),
            'feedId' => $this->getFeedId(),
            'isUnread' => $this->isUnread(),
            'isStarred' => $this->isStarred(),
            'fingerprint' => $this->getFingerprint(),
            'contentHash' => $this->getContentHash()
        ];
    }

    /**
     * Format for exporting.
     *
     * @param array $feeds List of feeds
     *
     * @return array
     */
    public function toExport(array $feeds): array
    {
        return [
            'guid' => $this->getGuid(),
            'url' => $this->getUrl(),
            'title' => $this->getTitle(),
            'author' => $this->getAuthor(),
            'pubDate' => $this->getPubDate(),
            'updatedDate' => null,
            'body' => $this->getBody(),
            'enclosureMime' => $this->getEnclosureMime(),
            'enclosureLink' => $this->getEnclosureLink(),
            'mediaThumbnail' => $this->getMediaThumbnail(),
            'mediaDescription' => $this->getMediaDescription(),
            'unread' => $this->isUnread(),
            'starred' => $this->isStarred(),
            'feedLink' => $feeds['feed' . $this->getFeedId()]->getLink(),
            'rtl' => $this->getRtl(),
        ];
    }

    private function computeContentHash(): string
    {
        return md5(
            $this->getTitle() . $this->getUrl() . $this->getBody() .
            $this->getEnclosureLink() . $this->getEnclosureMime() .
            $this->getAuthor()
        );
    }

    private function computeFingerprint(): string
    {
        return md5(
            $this->getTitle() . $this->getUrl() . $this->getBody() .
            $this->getEnclosureLink()
        );
    }

    /**
     * Check if a given mimetype is supported
     *
     * @param string|null $mime mimetype to check
     *
     * @return boolean
     */
    public function isSupportedMime(?string $mime): bool
    {

        return (
            $mime !== null && (
            stripos($mime, 'audio/') !== false ||
            stripos($mime, 'image/') !== false ||
            stripos($mime, 'video/') !== false));
    }
}
