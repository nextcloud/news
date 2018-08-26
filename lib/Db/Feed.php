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

use \OCP\AppFramework\Db\Entity;

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
     * Turns entity attributes into an array
     */
    public function jsonSerialize(): array
    {
        $serialized = $this->serializeFields(
            [
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
            ]
        );

        $url = parse_url($this->link, PHP_URL_HOST);

        // strip leading www. to avoid css class confusion
        if (strpos($url, 'www.') === 0) {
            $url = substr($url, 4);
        }

        $serialized['cssClass'] = 'custom-' . str_replace('.', '-', $url);

        return $serialized;
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

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
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
     * @return string
     */
    public function getUserId(): string
    {
        return $this->userId;
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

    /**
     * @return string
     */
    public function getUrlHash(): string
    {
        return $this->urlHash;
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
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url)
    {
        $url = trim($url);
        if(strpos($url, 'http') === 0 && $this->url !== $url) {
            $this->url = $url;
            $this->setUrlHash(md5($url));
            $this->markFieldUpdated('url');
        }
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
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
     * @return string|null
     */
    public function getFaviconLink()
    {
        return $this->faviconLink;
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
     * @return int|null
     */
    public function getAdded()
    {
        return $this->added;
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
     * @return int
     */
    public function getFolderId(): int
    {
        return $this->folderId;
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
     * @return int
     */
    public function getUnreadCount(): int
    {
        return $this->unreadCount;
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
     * @return string|null
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param string|null $link
     */
    public function setLink(string $link = null)
    {
        $link = trim($link);
        if(strpos($link, 'http') === 0 && $this->link !== $link) {
            $this->link = $link;
            $this->markFieldUpdated('link');
        }
    }

    /**
     * @return bool
     */
    public function getPreventUpdate(): bool
    {
        return $this->preventUpdate;
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
     * @return int|null
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
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
     * @return int
     */
    public function getArticlesPerUpdate(): int
    {
        return $this->articlesPerUpdate;
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
     * @return string|null
     */
    public function getHttpLastModified()
    {
        return $this->httpLastModified;
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
     * @return int|null
     */
    public function getLastModified()
    {
        return $this->lastModified;
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
     * @return string|null
     */
    public function getHttpEtag()
    {
        return $this->httpEtag;
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
     * @return string|null
     */
    public function getLocation()
    {
        return $this->location;
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
     * @return int
     */
    public function getOrdering(): int
    {
        return $this->ordering;
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
     * @return bool
     */
    public function getFullTextEnabled(): bool
    {
        return $this->fullTextEnabled;
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
     * @return bool
     */
    public function getPinned(): bool
    {
        return $this->pinned;
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
     * @return int
     */
    public function getUpdateMode(): int
    {
        return $this->updateMode;
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
     * @return int
     */
    public function getUpdateErrorCount(): int
    {
        return $this->updateErrorCount;
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
     * @return string|null
     */
    public function getLastUpdateError()
    {
        return $this->lastUpdateError;
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
     * @return string|null
     */
    public function getBasicAuthUser()
    {
        return $this->basicAuthUser;
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
     * @return string|null
     */
    public function getBasicAuthPassword()
    {
        return $this->basicAuthPassword;
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
}
