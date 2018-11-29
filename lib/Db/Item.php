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

namespace OCA\News\Db;

use OCP\AppFramework\Db\Entity;

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
    /** @var int|null */
    protected $updatedDate;
    /** @var string|null */
    protected $body;
    /** @var string|null */
    protected $enclosureMime;
    /** @var string|null */
    protected $enclosureLink;
    /** @var int */
    protected $feedId;
    /** @var int */
    protected $status = 0;
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
        $item = new static();
        $item->setGuid($import['guid']);
        $item->setGuidHash($import['guid']);
        $item->setUrl($import['url']);
        $item->setTitle($import['title']);
        $item->setAuthor($import['author']);
        $item->setPubDate($import['pubDate']);
        $item->setUpdatedDate($import['updatedDate']);
        $item->setBody($import['body']);
        $item->setEnclosureMime($import['enclosureMime']);
        $item->setEnclosureLink($import['enclosureLink']);
        $item->setRtl($import['rtl']);
        $item->setUnread($import['unread']);
        $item->setStarred($import['starred']);

        return $item;
    }

    public function generateSearchIndex()
    {
        $this->setSearchIndex(
            mb_strtolower(
                html_entity_decode(strip_tags($this->getBody())) .
                html_entity_decode($this->getAuthor()) .
                html_entity_decode($this->getTitle()) .
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
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @return null|string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return null|string
     */
    public function getContentHash()
    {
        return $this->contentHash;
    }

    /**
     * @return null|string
     */
    public function getEnclosureLink()
    {
        return $this->enclosureLink;
    }

    /**
     * @return null|string
     */
    public function getEnclosureMime()
    {
        return $this->enclosureMime;
    }

    public function getFeedId(): int
    {
        return $this->feedId;
    }

    /**
     * @return null|string
     */
    public function getFingerprint()
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

    public function getId(): int
    {
        return $this->id;
    }

    public function getIntro()
    {
        return strip_tags($this->getBody());
    }

    /**
     * @return string|null
     */
    public function getLastModified()
    {
        return $this->lastModified;
    }

    /**
     * @return int|null
     */
    public function getPubDate()
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
    public function getSearchIndex()
    {
        return $this->searchIndex;
    }

    /**
     * @return null|string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return int|null
     */
    public function getUpdatedDate()
    {
        return $this->updatedDate;
    }

    /**
     * @return null|string
     */
    public function getUrl()
    {
        return $this->url;
    }

    public function isStarred()
    {
        return $this->starred;
    }

    public function isUnread()
    {
        return $this->unread;
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
            'updatedDate' => $this->getUpdatedDate(),
            'body' => $this->getBody(),
            'enclosureMime' => $this->getEnclosureMime(),
            'enclosureLink' => $this->getEnclosureLink(),
            'feedId' => $this->getFeedId(),
            'unread' => $this->isUnread(),
            'starred' => $this->isStarred(),
            'lastModified' => $this->getLastModified(),
            'rtl' => $this->getRtl(),
            'intro' => $this->getIntro(),
            'fingerprint' => $this->getFingerprint(),
        ];
    }

    public function setAuthor(string $author = null)
    {
        $author = strip_tags($author);

        if ($this->author !== $author) {
            $this->author = $author;
            $this->markFieldUpdated('author');
        }
    }

    public function setBody(string $body = null)
    {
        // FIXME: this should not happen if the target="_blank" is already
        // on the link
        $body = str_replace('<a', '<a target="_blank" rel="noreferrer"', $body);

        if ($this->body !== $body) {
            $this->body = $body;
            $this->markFieldUpdated('body');
        }
    }

    public function setContentHash(string $contentHash = null)
    {
        if ($this->contentHash !== $contentHash) {
            $this->contentHash = $contentHash;
            $this->markFieldUpdated('contentHash');
        }
    }

    public function setEnclosureLink(string $enclosureLink = null)
    {
        if ($this->enclosureLink !== $enclosureLink) {
            $this->enclosureLink = $enclosureLink;
            $this->markFieldUpdated('enclosureLink');
        }
    }

    public function setEnclosureMime(string $enclosureMime = null)
    {
        if ($this->enclosureMime !== $enclosureMime) {
            $this->enclosureMime = $enclosureMime;
            $this->markFieldUpdated('enclosureMime');
        }
    }

    public function setFeedId(int $feedId)
    {
        if ($this->feedId !== $feedId) {
            $this->feedId = $feedId;
            $this->markFieldUpdated('feedId');
        }
    }

    public function setFingerprint(string $fingerprint = null)
    {
        if ($this->fingerprint !== $fingerprint) {
            $this->fingerprint = $fingerprint;
            $this->markFieldUpdated('fingerprint');
        }
    }

    public function setGuid(string $guid)
    {
        if ($this->guid !== $guid) {
            $this->guid = $guid;
            $this->markFieldUpdated('guid');
        }
    }

    public function setGuidHash(string $guidHash)
    {
        if ($this->guidHash !== $guidHash) {
            $this->guidHash = $guidHash;
            $this->markFieldUpdated('guidHash');
        }
    }

    public function setId(int $id)
    {
        if ($this->id !== $id) {
            $this->id = $id;
            $this->markFieldUpdated('id');
        }
    }

    public function setLastModified(string $lastModified = null)
    {
        if ($this->lastModified !== $lastModified) {
            $this->lastModified = $lastModified;
            $this->markFieldUpdated('lastModified');
        }
    }

    public function setPubDate(int $pubDate = null)
    {
        if ($this->pubDate !== $pubDate) {
            $this->pubDate = $pubDate;
            $this->markFieldUpdated('pubDate');
        }
    }

    public function setRtl(bool $rtl)
    {
        if ($this->rtl !== $rtl) {
            $this->rtl = $rtl;
            $this->markFieldUpdated('rtl');
        }
    }

    public function setSearchIndex(string $searchIndex = null)
    {
        if ($this->searchIndex !== $searchIndex) {
            $this->searchIndex = $searchIndex;
            $this->markFieldUpdated('searchIndex');
        }
    }

    public function setStarred(bool $starred)
    {
        if ($this->starred !== $starred) {
            $this->starred = $starred;
            $this->markFieldUpdated('starred');
        }
    }

    public function setTitle(string $title = null)
    {
        $title = strip_tags($title);

        if ($this->title !== $title) {
            $this->title = $title;
            $this->markFieldUpdated('title');
        }
    }

    public function setUnread(bool $unread)
    {
        if ($this->unread !== $unread) {
            $this->unread = $unread;
            $this->markFieldUpdated('unread');
        }
    }

    public function setUpdatedDate(int $updatedDate = null)
    {
        if ($this->updatedDate !== $updatedDate) {
            $this->updatedDate = $updatedDate;
            $this->markFieldUpdated('updatedDate');
        }
    }

    public function setUrl(string $url = null)
    {
        $url = trim($url);
        if ((strpos($url, 'http') === 0 || strpos($url, 'magnet') === 0)
            && $this->url !== $url
        ) {
            $this->url = $url;
            $this->markFieldUpdated('url');
        }
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
            'updatedDate' => $this->getUpdatedDate(),
            'body' => $this->getBody(),
            'enclosureMime' => $this->getEnclosureMime(),
            'enclosureLink' => $this->getEnclosureLink(),
            'feedId' => $this->getFeedId(),
            'unread' => $this->isUnread(),
            'starred' => $this->isStarred(),
            'lastModified' => $this->cropApiLastModified(),
            'rtl' => $this->getRtl(),
            'fingerprint' => $this->getFingerprint(),
            'contentHash' => $this->getContentHash()
        ];
    }

    public function toExport($feeds): array
    {
        return [
            'guid' => $this->getGuid(),
            'url' => $this->getUrl(),
            'title' => $this->getTitle(),
            'author' => $this->getAuthor(),
            'pubDate' => $this->getPubDate(),
            'updatedDate' => $this->getUpdatedDate(),
            'body' => $this->getBody(),
            'enclosureMime' => $this->getEnclosureMime(),
            'enclosureLink' => $this->getEnclosureLink(),
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
}
