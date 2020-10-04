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
    /** @var Feed[] */
    public $feeds = [];

    public function __construct()
    {
        $this->addType('parentId', 'integer');
        $this->addType('name', 'string');
        $this->addType('userId', 'string');
        $this->addType('opened', 'boolean');
        $this->addType('deletedAt', 'integer');
        $this->addType('lastModified', 'string');
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
    public function getLastModified(): ?string
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
    public function getParentId(): ?int
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

    public function setDeletedAt(?int $deletedAt = null): self
    {
        if ($this->deletedAt !== $deletedAt) {
            $this->deletedAt = $deletedAt;
            $this->markFieldUpdated('deletedAt');
        }

        return $this;
    }

    public function setLastModified(?string $lastModified = null): self
    {

        if ($this->lastModified !== $lastModified) {
            $this->lastModified = $lastModified;
            $this->markFieldUpdated('lastModified');
        }

        return $this;
    }

    public function setName(string $name): self
    {
        if ($this->name !== $name) {
            $this->name = $name;
            $this->markFieldUpdated('name');
        }

        return $this;
    }

    public function setOpened(bool $opened): self
    {
        if ($this->opened !== $opened) {
            $this->opened = $opened;
            $this->markFieldUpdated('opened');
        }

        return $this;
    }

    public function setParentId(?int $parentId = null): self
    {
        if ($this->parentId !== $parentId) {
            $this->parentId = $parentId;
            $this->markFieldUpdated('parentId');
        }

        return $this;
    }

    public function setUserId(string $userId): self
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
                'name',
                'feeds'
            ]
        );
    }
}
