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

class Folder extends Entity implements IAPI, \JsonSerializable
{

    use EntityJSONSerializer;

    /** @var int|null */
    protected $parentId;
    /** @var string */
    protected $name;
    /** @var string */
    protected $userId = '';
    /** @var bool */
    protected $opened = true;
    /** @var int|null */
    protected $deletedAt = 0;
    /** @var string|null */
    protected $lastModified = '0';

    /**
     * @return int|null
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getLastModified()
    {
        return $this->lastModified;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getOpened(): bool
    {
        return $this->opened;
    }

    /**
     * @return int|null
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    /**
     * Turns entity attributes into an array
     */
    public function jsonSerialize(): array
    {
        return $this->serializeFields(
            [
                'id',
                'parentId',
                'name',
                'userId',
                'opened',
                'deletedAt',
            ]
        );
    }

    public function setDeletedAt(int $deletedAt = null)
    {
        if ($this->deletedAt !== $deletedAt) {
            $this->deletedAt = $deletedAt;
            $this->markFieldUpdated('deletedAt');
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

    public function setName(string $name)
    {
        if ($this->name !== $name) {
            $this->name = $name;
            $this->markFieldUpdated('name');
        }
    }

    public function setOpened(bool $opened)
    {
        if ($this->opened !== $opened) {
            $this->opened = $opened;
            $this->markFieldUpdated('opened');
        }
    }

    public function setParentId(int $parentId = null)
    {
        if ($this->parentId !== $parentId) {
            $this->parentId = $parentId;
            $this->markFieldUpdated('parentId');
        }
    }

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
                'name'
            ]
        );
    }
}
