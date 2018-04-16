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
    protected $userId;
    /** @var string */
    protected $urlHash;
    /** @var string */
    protected $url;
    /** @var string */
    protected $title;
    /** @var string */
    protected $faviconLink;
    /** @var int */
    protected $added;
    /** @var int */
    protected $folderId;
    /** @var int */
    protected $unreadCount;
    /** @var string */
    protected $link;
    /** @var bool */
    protected $preventUpdate;
    /** @var int */
    protected $deletedAt;
    /** @var int */
    protected $articlesPerUpdate;
    /** @var string */
    protected $httpLastModified;
    /** @var string */
    protected $lastModified;
    /** @var string */
    protected $httpEtag;
    /** @var string */
    protected $location;
    /** @var int */
    protected $ordering;
    /** @var bool */
    protected $fullTextEnabled;
    /** @var bool */
    protected $pinned;
    /** @var int */
    protected $updateMode;
    /** @var int */
    protected $updateErrorCount;
    /** @var string */
    protected $lastUpdateError;
    /** @var string */
    protected $basicAuthUser;
    /** @var string */
    protected $basicAuthPassword;

    /**
     * Turns entitie attributes into an array
     */
    public function jsonSerialize()
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


    public function toAPI()
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
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        if ($this->id !== $id) {
            $this->id = (int)$id;
            $this->markFieldUpdated('id');
        }
    }

    /**
     * @return string
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param string $userId
     */
    public function setUserId($userId)
    {
        if ($this->userId !== $userId) {
            $this->userId = (string)$userId;
            $this->markFieldUpdated('userId');
        }
    }

    /**
     * @return string
     */
    public function getUrlHash()
    {
        return $this->urlHash;
    }

    /**
     * @param string $urlHash
     */
    public function setUrlHash($urlHash)
    {
        if ($this->urlHash !== $urlHash) {
            $this->urlHash = (string)$urlHash;
            $this->markFieldUpdated('urlHash');
        }
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $url = trim((string)$url);
        if(strpos($url, 'http') === 0 && $this->url !== $url) {
            $this->url = $url;
            $this->setUrlHash(md5($url));
            $this->markFieldUpdated('url');
        }
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        if ($this->title !== $title) {
            $this->title = (string)$title;
            $this->markFieldUpdated('title');
        }
    }

    /**
     * @return string
     */
    public function getFaviconLink()
    {
        return $this->faviconLink;
    }

    /**
     * @param string $faviconLink
     */
    public function setFaviconLink($faviconLink)
    {
        if ($this->faviconLink !== $faviconLink) {
            $this->faviconLink = (string)$faviconLink;
            $this->markFieldUpdated('faviconLink');
        }
    }

    /**
     * @return int
     */
    public function getAdded()
    {
        return $this->added;
    }

    /**
     * @param int $added
     */
    public function setAdded($added)
    {
        if ($this->added !== $added) {
            $this->added = (int)$added;
            $this->markFieldUpdated('added');
        }
    }

    /**
     * @return int
     */
    public function getFolderId()
    {
        return $this->folderId;
    }

    /**
     * @param int $folderId
     */
    public function setFolderId($folderId)
    {
        if ($this->folderId !== $folderId) {
            $this->folderId = (int)$folderId;
            $this->markFieldUpdated('folderId');
        }
    }

    /**
     * @return int
     */
    public function getUnreadCount()
    {
        return $this->unreadCount;
    }

    /**
     * @param int $unreadCount
     */
    public function setUnreadCount($unreadCount)
    {
        if ($this->unreadCount !== $unreadCount) {
            $this->unreadCount = (int)$unreadCount;
            $this->markFieldUpdated('unreadCount');
        }
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param string $link
     */
    public function setLink($link)
    {
        $link = trim((string)$link);
        if(strpos($link, 'http') === 0 && $this->link !== $link) {
            $this->link = (string)$link;
            $this->markFieldUpdated('link');
        }
    }

    /**
     * @return bool
     */
    public function getPreventUpdate()
    {
        return $this->preventUpdate;
    }

    /**
     * @param bool $preventUpdate
     */
    public function setPreventUpdate($preventUpdate)
    {
        if ($this->preventUpdate !== $preventUpdate) {
            $this->preventUpdate = (bool)$preventUpdate;
            $this->markFieldUpdated('preventUpdate');
        }
    }

    /**
     * @return int
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * @param int $deletedAt
     */
    public function setDeletedAt($deletedAt)
    {
        if ($this->deletedAt !== $deletedAt) {
            $this->deletedAt = (int)$deletedAt;
            $this->markFieldUpdated('deletedAt');
        }
    }

    /**
     * @return int
     */
    public function getArticlesPerUpdate()
    {
        return $this->articlesPerUpdate;
    }

    /**
     * @param int $articlesPerUpdate
     */
    public function setArticlesPerUpdate($articlesPerUpdate)
    {
        if ($this->articlesPerUpdate !== $articlesPerUpdate) {
            $this->articlesPerUpdate = (int)$articlesPerUpdate;
            $this->markFieldUpdated('articlesPerUpdate');
        }
    }

    /**
     * @return string
     */
    public function getHttpLastModified()
    {
        return $this->httpLastModified;
    }

    /**
     * @param string $httpLastModified
     */
    public function setHttpLastModified($httpLastModified)
    {
        if ($this->httpLastModified !== $httpLastModified) {
            $this->httpLastModified = (string)$httpLastModified;
            $this->markFieldUpdated('httpLastModified');
        }
    }

    /**
     * @return string
     */
    public function getLastModified()
    {
        return $this->lastModified;
    }

    /**
     * @param string $lastModified
     */
    public function setLastModified($lastModified)
    {
        if ($this->lastModified !== $lastModified) {
            $this->lastModified = (string)$lastModified;
            $this->markFieldUpdated('lastModified');
        }
    }

    /**
     * @return string
     */
    public function getHttpEtag()
    {
        return $this->httpEtag;
    }

    /**
     * @param string $httpEtag
     */
    public function setHttpEtag($httpEtag)
    {
        if ($this->httpEtag !== $httpEtag) {
            $this->httpEtag = (string)$httpEtag;
            $this->markFieldUpdated('httpEtag');
        }
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param string $location
     */
    public function setLocation($location)
    {
        if ($this->location !== $location) {
            $this->location = (string)$location;
            $this->markFieldUpdated('location');
        }
    }

    /**
     * @return int
     */
    public function getOrdering()
    {
        return $this->ordering;
    }

    /**
     * @param int $ordering
     */
    public function setOrdering($ordering)
    {
        if ($this->ordering !== $ordering) {
            $this->ordering = (int)$ordering;
            $this->markFieldUpdated('ordering');
        }
    }

    /**
     * @return bool
     */
    public function getFullTextEnabled()
    {
        return $this->fullTextEnabled;
    }

    /**
     * @param bool $fullTextEnabled
     */
    public function setFullTextEnabled($fullTextEnabled)
    {
        if ($this->fullTextEnabled !== $fullTextEnabled) {
            $this->fullTextEnabled = (bool)$fullTextEnabled;
            $this->markFieldUpdated('fullTextEnabled');
        }
    }

    /**
     * @return bool
     */
    public function getPinned()
    {
        return $this->pinned;
    }

    /**
     * @param bool $pinned
     */
    public function setPinned($pinned)
    {
        if ($this->pinned !== $pinned) {
            $this->pinned = (bool)$pinned;
            $this->markFieldUpdated('pinned');
        }
    }

    /**
     * @return int
     */
    public function getUpdateMode()
    {
        return $this->updateMode;
    }

    /**
     * @param int $updateMode
     */
    public function setUpdateMode($updateMode)
    {
        if ($this->updateMode !== $updateMode) {
            $this->updateMode = (int)$updateMode;
            $this->markFieldUpdated('updateMode');
        }
    }

    /**
     * @return int
     */
    public function getUpdateErrorCount()
    {
        return $this->updateErrorCount;
    }

    /**
     * @param int $updateErrorCount
     */
    public function setUpdateErrorCount($updateErrorCount)
    {
        if ($this->updateErrorCount !== $updateErrorCount) {
            $this->updateErrorCount = (int)$updateErrorCount;
            $this->markFieldUpdated('updateErrorCount');
        }
    }

    /**
     * @return string
     */
    public function getLastUpdateError()
    {
        return $this->lastUpdateError;
    }

    /**
     * @param string $lastUpdateError
     */
    public function setLastUpdateError($lastUpdateError)
    {
        if ($this->lastUpdateError !== $lastUpdateError) {
            $this->lastUpdateError = (string)$lastUpdateError;
            $this->markFieldUpdated('lastUpdateError');
        }
    }

    /**
     * @return string
     */
    public function getBasicAuthUser()
    {
        return $this->basicAuthUser;
    }

    /**
     * @param string $basicAuthUser
     */
    public function setBasicAuthUser($basicAuthUser)
    {
        if ($this->basicAuthUser !== $basicAuthUser) {
            $this->basicAuthUser = (string)$basicAuthUser;
            $this->markFieldUpdated('basicAuthUser');
        }
    }

    /**
     * @return string
     */
    public function getBasicAuthPassword()
    {
        return $this->basicAuthPassword;
    }

    /**
     * @param string $basicAuthPassword
     */
    public function setBasicAuthPassword($basicAuthPassword)
    {
        if ($this->basicAuthPassword !== $basicAuthPassword) {
            $this->basicAuthPassword = (string)$basicAuthPassword;
            $this->markFieldUpdated('basicAuthPassword');
        }
    }
}
