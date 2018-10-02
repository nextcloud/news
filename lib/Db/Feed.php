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

class Feed extends Entity implements IAPI, \JsonSerializable
{

    use EntityJSONSerializer;

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
    /** @var int */
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
    /** @var int|null */
    protected $lastModified = 0;
    /** @var string|null */
    protected $httpEtag = null;
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
    public function getBasicAuthPassword()
    {
        return $this->basicAuthPassword;
    }

    /**
     * @return string|null
     */
    public function getBasicAuthUser()
    {
        return $this->basicAuthUser;
    }

    /**
     * @return int|null
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * @return string|null
     */
    public function getFaviconLink()
    {
        return $this->faviconLink;
    }

    /**
     * @return int
     */
    public function getFolderId(): int
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
    public function getHttpEtag()
    {
        return $this->httpEtag;
    }

    /**
     * @return string|null
     */
    public function getHttpLastModified()
    {
        return $this->httpLastModified;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int|null
     */
    public function getLastModified()
    {
        return $this->lastModified;
    }

    /**
     * @return string|null
     */
    public function getLastUpdateError()
    {
        return $this->lastUpdateError;
    }

    /**
     * @return string|null
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @return string|null
     */
    public function getLocation()
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
    public function setAdded(int $added = null)
    {
        if ($this->added !== $added) {
            $this->added = $added;
            $this->markFieldUpdated('added');
        }
    }

    /**
     * @param int $articlesPerUpdate
     */
    public function setArticlesPerUpdate(int $articlesPerUpdate)
    {
        if ($this->articlesPerUpdate !== $articlesPerUpdate) {
            $this->articlesPerUpdate = $articlesPerUpdate;
            $this->markFieldUpdated('articlesPerUpdate');
        }
    }

    /**
     * @param string|null $basicAuthPassword
     */
    public function setBasicAuthPassword(string $basicAuthPassword = null)
    {
        if ($this->basicAuthPassword !== $basicAuthPassword) {
            $this->basicAuthPassword = $basicAuthPassword;
            $this->markFieldUpdated('basicAuthPassword');
        }
    }

    /**
     * @param string|null $basicAuthUser
     */
    public function setBasicAuthUser(string $basicAuthUser = null)
    {
        if ($this->basicAuthUser !== $basicAuthUser) {
            $this->basicAuthUser = $basicAuthUser;
            $this->markFieldUpdated('basicAuthUser');
        }
    }

    /**
     * @param int|null $deletedAt
     */
    public function setDeletedAt(int $deletedAt = null)
    {
        if ($this->deletedAt !== $deletedAt) {
            $this->deletedAt = $deletedAt;
            $this->markFieldUpdated('deletedAt');
        }
    }

    /**
     * @param string|null $faviconLink
     */
    public function setFaviconLink(string $faviconLink = null)
    {
        if ($this->faviconLink !== $faviconLink) {
            $this->faviconLink = $faviconLink;
            $this->markFieldUpdated('faviconLink');
        }
    }

    /**
     * @param int $folderId
     */
    public function setFolderId(int $folderId)
    {
        if ($this->folderId !== $folderId) {
            $this->folderId = $folderId;
            $this->markFieldUpdated('folderId');
        }
    }

    /**
     * @param bool $fullTextEnabled
     */
    public function setFullTextEnabled(bool $fullTextEnabled)
    {
        if ($this->fullTextEnabled !== $fullTextEnabled) {
            $this->fullTextEnabled = $fullTextEnabled;
            $this->markFieldUpdated('fullTextEnabled');
        }
    }

    /**
     * @param string|null $httpEtag
     */
    public function setHttpEtag(string $httpEtag = null)
    {
        if ($this->httpEtag !== $httpEtag) {
            $this->httpEtag = $httpEtag;
            $this->markFieldUpdated('httpEtag');
        }
    }

    /**
     * @param string|null $httpLastModified
     */
    public function setHttpLastModified(string $httpLastModified = null)
    {
        if ($this->httpLastModified !== $httpLastModified) {
            $this->httpLastModified = $httpLastModified;
            $this->markFieldUpdated('httpLastModified');
        }
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        if ($this->id !== $id) {
            $this->id = $id;
            $this->markFieldUpdated('id');
        }
    }

    /**
     * @param int|null $lastModified
     */
    public function setLastModified(int $lastModified = null)
    {
        if ($this->lastModified !== $lastModified) {
            $this->lastModified = $lastModified;
            $this->markFieldUpdated('lastModified');
        }
    }

    /**
     * @param string|null $lastUpdateError
     */
    public function setLastUpdateError(string $lastUpdateError = null)
    {
        if ($this->lastUpdateError !== $lastUpdateError) {
            $this->lastUpdateError = $lastUpdateError;
            $this->markFieldUpdated('lastUpdateError');
        }
    }

    /**
     * @param string|null $link
     */
    public function setLink(string $link = null)
    {
        $link = trim($link);
        if (strpos($link, 'http') === 0 && $this->link !== $link) {
            $this->link = $link;
            $this->markFieldUpdated('link');
        }
    }

    /**
     * @param string|null $location
     */
    public function setLocation(string $location = null)
    {
        if ($this->location !== $location) {
            $this->location = $location;
            $this->markFieldUpdated('location');
        }
    }

    /**
     * @param int $ordering
     */
    public function setOrdering(int $ordering)
    {
        if ($this->ordering !== $ordering) {
            $this->ordering = $ordering;
            $this->markFieldUpdated('ordering');
        }
    }

    /**
     * @param bool $pinned
     */
    public function setPinned(bool $pinned)
    {
        if ($this->pinned !== $pinned) {
            $this->pinned = $pinned;
            $this->markFieldUpdated('pinned');
        }
    }

    /**
     * @param bool $preventUpdate
     */
    public function setPreventUpdate(bool $preventUpdate)
    {
        if ($this->preventUpdate !== $preventUpdate) {
            $this->preventUpdate = $preventUpdate;
            $this->markFieldUpdated('preventUpdate');
        }
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title)
    {
        if ($this->title !== $title) {
            $this->title = $title;
            $this->markFieldUpdated('title');
        }
    }

    /**
     * @param int $unreadCount
     */
    public function setUnreadCount(int $unreadCount)
    {
        if ($this->unreadCount !== $unreadCount) {
            $this->unreadCount = $unreadCount;
            $this->markFieldUpdated('unreadCount');
        }
    }

    /**
     * @param int $updateErrorCount
     */
    public function setUpdateErrorCount(int $updateErrorCount)
    {
        if ($this->updateErrorCount !== $updateErrorCount) {
            $this->updateErrorCount = $updateErrorCount;
            $this->markFieldUpdated('updateErrorCount');
        }
    }

    /**
     * @param int $updateMode
     */
    public function setUpdateMode(int $updateMode)
    {
        if ($this->updateMode !== $updateMode) {
            $this->updateMode = $updateMode;
            $this->markFieldUpdated('updateMode');
        }
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url)
    {
        $url = trim($url);
        if (strpos($url, 'http') === 0 && $this->url !== $url) {
            $this->url = $url;
            $this->setUrlHash(md5($url));
            $this->markFieldUpdated('url');
        }
    }

    /**
     * @param string $urlHash
     */
    public function setUrlHash(string $urlHash)
    {
        if ($this->urlHash !== $urlHash) {
            $this->urlHash = $urlHash;
            $this->markFieldUpdated('urlHash');
        }
    }

    /**
     * @param string $userId
     */
    public function setUserId(string $userId)
    {
        if ($this->userId !== $userId) {
            $this->userId = $userId;
            $this->markFieldUpdated('userId');
        }
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
                'lastUpdateError'
            ]
        );
    }
}
