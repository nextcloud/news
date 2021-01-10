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
 * Class Feed
 *
 * @package OCA\News\Db
 * @Embeddable
 */
class Feed extends Entity implements IAPI, \JsonSerializable
{
    use EntityJSONSerializer;

    /**
     * Silently import new items
     */
    const UPDATE_MODE_SILENT = 0;

    /**
     * Mark new items as unread.
     */
    const UPDATE_MODE_NORMAL = 1;

    /** @var string */
    protected $userId = '';
    /** @var string */
    protected $urlHash;
    /** @var string */
    protected $url;
    /** @var string */
    protected $title;
    /** @var string|null */
    protected $faviconLink = null;
    /** @var int|null */
    protected $added = 0;
    /** @var int|null */
    protected $folderId;
    /** @var int */
    protected $unreadCount;
    /** @var string|null */
    protected $link = null;
    /** @var bool */
    protected $preventUpdate = false;
    /** @var int|null */
    protected $deletedAt = 0;
    /** @var int */
    protected $articlesPerUpdate = 0;
    /** @var string|null */
    protected $httpLastModified = null;
    /** @var string|null */
    protected $lastModified = '0';
    /** @var string|null */
    protected $location = null;
    /** @var int */
    protected $ordering = 0;
    /** @var bool */
    protected $fullTextEnabled = false;
    /** @var bool */
    protected $pinned = false;
    /** @var int */
    protected $updateMode = 0;
    /** @var int */
    protected $updateErrorCount = 0;
    /** @var string|null */
    protected $lastUpdateError = '';
    /** @var string|null */
    protected $basicAuthUser = '';
    /** @var string|null */
    protected $basicAuthPassword = '';
    /** @var Item[] */
    public $items = [];

    public function __construct()
    {
        $this->addType('userId', 'string');
        $this->addType('urlHash', 'string');
        $this->addType('url', 'string');
        $this->addType('title', 'string');
        $this->addType('faviconLink', 'string');
        $this->addType('added', 'integer');
        $this->addType('folderId', 'integer');
        $this->addType('link', 'string');
        $this->addType('preventUpdate', 'boolean');
        $this->addType('deletedAt', 'integer');
        $this->addType('articlesPerUpdate', 'integer');
        $this->addType('httpLastModified', 'string');
        $this->addType('lastModified', 'string');
        $this->addType('location', 'string');
        $this->addType('ordering', 'integer');
        $this->addType('fullTextEnabled', 'boolean');
        $this->addType('pinned', 'boolean');
        $this->addType('updateMode', 'integer');
        $this->addType('updateErrorCount', 'integer');
        $this->addType('lastUpdateError', 'string');
        $this->addType('basicAuthUser', 'string');
        $this->addType('basicAuthPassword', 'string');
    }

    /**
     * @return int|null
     */
    public function getAdded()
    {
        return $this->added;
    }

    /**
     * @return int
     */
    public function getArticlesPerUpdate(): int
    {
        return $this->articlesPerUpdate;
    }

    /**
     * @return string|null
     */
    public function getBasicAuthPassword(): ?string
    {
        return $this->basicAuthPassword;
    }

    /**
     * @return string|null
     */
    public function getBasicAuthUser(): ?string
    {
        return $this->basicAuthUser;
    }

    /**
     * @return int|null
     */
    public function getDeletedAt(): ?int
    {
        return $this->deletedAt;
    }

    /**
     * @return string|null
     */
    public function getFaviconLink(): ?string
    {
        return $this->faviconLink;
    }

    /**
     * @return int|null
     */
    public function getFolderId(): ?int
    {
        return $this->folderId;
    }

    /**
     * @return bool
     */
    public function getFullTextEnabled(): bool
    {
        return $this->fullTextEnabled;
    }

    /**
     * @return string|null
     */
    public function getHttpLastModified(): ?string
    {
        return $this->httpLastModified;
    }

    /**
     * @return string|null
     */
    public function getLastModified(): ?string
    {
        return $this->lastModified;
    }

    /**
     * @return string|null
     */
    public function getLastUpdateError(): ?string
    {
        return $this->lastUpdateError;
    }

    /**
     * @return string|null
     */
    public function getLink(): ?string
    {
        return $this->link;
    }

    /**
     * @return string|null
     */
    public function getLocation(): ?string
    {
        return $this->location;
    }

    /**
     * @return int
     */
    public function getOrdering(): int
    {
        return $this->ordering;
    }

    /**
     * @return bool
     */
    public function getPinned(): bool
    {
        return $this->pinned;
    }

    /**
     * @return bool
     */
    public function getPreventUpdate(): bool
    {
        return $this->preventUpdate;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return int
     */
    public function getUnreadCount(): int
    {
        return $this->unreadCount;
    }

    /**
     * @return int
     */
    public function getUpdateErrorCount(): int
    {
        return $this->updateErrorCount;
    }

    /**
     * @return int
     */
    public function getUpdateMode(): int
    {
        return $this->updateMode;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getUrlHash(): string
    {
        return $this->urlHash;
    }

    /**
     * @return string
     */
    public function getUserId(): string
    {
        return $this->userId;
    }

    /**
     * Turns entity attributes into an array
     */
    public function jsonSerialize(): array
    {
        $serialized = $this->serializeFields([
            'id',
            'userId',
            'urlHash',
            'url',
            'title',
            'faviconLink',
            'added',
            'folderId',
            'unreadCount',
            'link',
            'preventUpdate',
            'deletedAt',
            'articlesPerUpdate',
            'location',
            'ordering',
            'fullTextEnabled',
            'pinned',
            'updateMode',
            'updateErrorCount',
            'lastUpdateError',
            'basicAuthUser',
            'basicAuthPassword'
        ]);

        $url = parse_url($this->link, PHP_URL_HOST);

        // strip leading www. to avoid css class confusion
        if (strpos($url, 'www.') === 0) {
            $url = substr($url, 4);
        }

        $serialized['cssClass'] = 'custom-' . str_replace('.', '-', $url);

        return $serialized;
    }

    /**
     * @param int|null $added
     */
    public function setAdded(?int $added = null): Feed
    {
        if ($this->added !== $added) {
            $this->added = $added;
            $this->markFieldUpdated('added');
        }

        return $this;
    }

    /**
     * @param int $articlesPerUpdate
     */
    public function setArticlesPerUpdate(int $articlesPerUpdate): Feed
    {
        if ($this->articlesPerUpdate !== $articlesPerUpdate) {
            $this->articlesPerUpdate = $articlesPerUpdate;
            $this->markFieldUpdated('articlesPerUpdate');
        }

        return $this;
    }

    /**
     * @param string|null $basicAuthPassword
     */
    public function setBasicAuthPassword(?string $basicAuthPassword = null): Feed
    {
        if ($this->basicAuthPassword !== $basicAuthPassword) {
            $this->basicAuthPassword = $basicAuthPassword;
            $this->markFieldUpdated('basicAuthPassword');
        }

        return $this;
    }

    /**
     * @param string|null $basicAuthUser
     */
    public function setBasicAuthUser(?string $basicAuthUser = null): Feed
    {
        if ($this->basicAuthUser !== $basicAuthUser) {
            $this->basicAuthUser = $basicAuthUser;
            $this->markFieldUpdated('basicAuthUser');
        }

        return $this;
    }

    /**
     * @param int|null $deletedAt
     */
    public function setDeletedAt(?int $deletedAt = null): Feed
    {
        if ($this->deletedAt !== $deletedAt) {
            $this->deletedAt = $deletedAt;
            $this->markFieldUpdated('deletedAt');
        }

        return $this;
    }

    /**
     * @param string|null $faviconLink
     */
    public function setFaviconLink(?string $faviconLink = null): Feed
    {
        if ($this->faviconLink !== $faviconLink) {
            $this->faviconLink = $faviconLink;
            $this->markFieldUpdated('faviconLink');
        }

        return $this;
    }

    /**
     * @param int|null $folderId
     *
     * @return Feed
     */
    public function setFolderId(?int $folderId): Feed
    {
        if ($this->folderId !== $folderId) {
            $this->folderId = $folderId;
            $this->markFieldUpdated('folderId');
        }

        return $this;
    }

    /**
     * @param bool $fullTextEnabled
     */
    public function setFullTextEnabled(bool $fullTextEnabled): Feed
    {
        if ($this->fullTextEnabled !== $fullTextEnabled) {
            $this->fullTextEnabled = $fullTextEnabled;
            $this->markFieldUpdated('fullTextEnabled');
        }

        return $this;
    }

    /**
     * @param string|null $httpLastModified
     */
    public function setHttpLastModified(?string $httpLastModified = null): Feed
    {
        if ($this->httpLastModified !== $httpLastModified) {
            $this->httpLastModified = $httpLastModified;
            $this->markFieldUpdated('httpLastModified');
        }

        return $this;
    }

    /**
     * @param string|null $lastModified
     */
    public function setLastModified(?string $lastModified = null): Feed
    {
        if ($this->lastModified !== $lastModified) {
            $this->lastModified = $lastModified;
            $this->markFieldUpdated('lastModified');
        }

        return $this;
    }

    /**
     * @param string|null $lastUpdateError
     */
    public function setLastUpdateError(?string $lastUpdateError = null): Feed
    {
        if ($this->lastUpdateError !== $lastUpdateError) {
            $this->lastUpdateError = $lastUpdateError;
            $this->markFieldUpdated('lastUpdateError');
        }

        return $this;
    }

    /**
     * @param string|null $link
     */
    public function setLink(?string $link = null): Feed
    {
        $link = trim($link);
        if (strpos($link, 'http') === 0 && $this->link !== $link) {
            $this->link = $link;
            $this->markFieldUpdated('link');
        }

        return $this;
    }

    /**
     * @param string|null $location
     */
    public function setLocation(?string $location = null): Feed
    {
        if ($this->location !== $location) {
            $this->location = $location;
            $this->markFieldUpdated('location');
        }

        return $this;
    }

    /**
     * @param int $ordering
     */
    public function setOrdering(int $ordering): Feed
    {
        if ($this->ordering !== $ordering) {
            $this->ordering = $ordering;
            $this->markFieldUpdated('ordering');
        }

        return $this;
    }

    /**
     * @param bool $pinned
     */
    public function setPinned(bool $pinned): Feed
    {
        if ($this->pinned !== $pinned) {
            $this->pinned = $pinned;
            $this->markFieldUpdated('pinned');
        }

        return $this;
    }

    /**
     * @param bool $preventUpdate
     */
    public function setPreventUpdate(bool $preventUpdate): Feed
    {
        if ($this->preventUpdate !== $preventUpdate) {
            $this->preventUpdate = $preventUpdate;
            $this->markFieldUpdated('preventUpdate');
        }

        return $this;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): Feed
    {
        if ($this->title !== $title) {
            $this->title = $title;
            $this->markFieldUpdated('title');
        }

        return $this;
    }

    /**
     * @param int $unreadCount
     */
    public function setUnreadCount(int $unreadCount): Feed
    {
        if ($this->unreadCount !== $unreadCount) {
            $this->unreadCount = $unreadCount;
            $this->markFieldUpdated('unreadCount');
        }

        return $this;
    }

    /**
     * @param int $updateErrorCount
     */
    public function setUpdateErrorCount(int $updateErrorCount): Feed
    {
        if ($this->updateErrorCount !== $updateErrorCount) {
            $this->updateErrorCount = $updateErrorCount;
            $this->markFieldUpdated('updateErrorCount');
        }

        return $this;
    }

    /**
     * @param int $updateMode
     */
    public function setUpdateMode(int $updateMode): Feed
    {
        if ($this->updateMode !== $updateMode) {
            $this->updateMode = $updateMode;
            $this->markFieldUpdated('updateMode');
        }

        return $this;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url): Feed
    {
        $url = trim($url);
        if (strpos($url, 'http') === 0 && $this->url !== $url) {
            $this->url = $url;
            $this->setUrlHash(md5($url));
            $this->markFieldUpdated('url');
        }

        return $this;
    }

    /**
     * @param string $urlHash
     */
    public function setUrlHash(string $urlHash): Feed
    {
        if ($this->urlHash !== $urlHash) {
            $this->urlHash = $urlHash;
            $this->markFieldUpdated('urlHash');
        }

        return $this;
    }

    /**
     * @param string $userId
     */
    public function setUserId(string $userId): Feed
    {
        if ($this->userId !== $userId) {
            $this->userId = $userId;
            $this->markFieldUpdated('userId');
        }

        return $this;
    }

    public function toAPI(): array
    {
        return $this->serializeFields(
            [
                'id',
                'url',
                'title',
                'faviconLink',
                'added',
                'folderId',
                'unreadCount',
                'ordering',
                'link',
                'pinned',
                'updateErrorCount',
                'lastUpdateError',
                'items'
            ]
        );
    }

    public function toAPI2(bool $reduced = false): array
    {
        $result = [
            'id' => $this->getId(),
            'name' => $this->getTitle(),
            'faviconLink' => $this->getFaviconLink(),
            'folderId' => $this->getFolderId(),
            'ordering' => $this->getOrdering(),
            'fullTextEnabled' => $this->getFullTextEnabled(),
            'updateMode' => $this->getUpdateMode(),
            'isPinned' => $this->getPinned()
        ];

        if (!empty($this->getLastUpdateError())) {
            $result['error'] = [
                'code' => 1,
                'message' => $this->getLastUpdateError()
            ];
        }

        return $result;
    }
}
