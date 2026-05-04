<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Eryk J. <infiniti@inventati.org>
 * @copyright 2026 Eryk J.
 */

namespace OCA\News\Db;

use OCP\AppFramework\Db\Entity;

class Filter extends Entity implements IAPI, \JsonSerializable
{
    use EntityJSONSerializer;

    /** @var int|null */
    protected $feedId;
    /** @var string|null */
    protected $titleKeywords;
    /** @var string|null */
    protected $bodyKeywords;
    /** @var string|null */
    protected $urlKeywords;
    /** @var string|null */
    protected $lastModified = '0';

    public function __construct()
    {
        $this->addType('feedId', 'integer');
        $this->addType('titleKeywords', 'string');
        $this->addType('bodyKeywords', 'string');
        $this->addType('urlKeywords', 'string');
        $this->addType('lastModified', 'string');
    }

    public function getFeedId(): ?int
    {
        return $this->feedId;
    }

    public function getTitleKeywords(): ?string
    {
        return $this->titleKeywords;
    }

    public function getBodyKeywords(): ?string
    {
        return $this->bodyKeywords;
    }

    public function getUrlKeywords(): ?string
    {
        return $this->urlKeywords;
    }

    public function getLastModified(): ?string
    {
        return $this->lastModified;
    }

    public function setFeedId(?int $feedId = null): self
    {
        if ($this->feedId !== $feedId) {
            $this->feedId = $feedId;
            $this->markFieldUpdated('feedId');
        }

        return $this;
    }

    public function setTitleKeywords(?string $titleKeywords = null): self
    {
        if ($this->titleKeywords !== $titleKeywords) {
            $this->titleKeywords = $titleKeywords;
            $this->markFieldUpdated('titleKeywords');
        }

        return $this;
    }

    public function setBodyKeywords(?string $bodyKeywords = null): self
    {
        if ($this->bodyKeywords !== $bodyKeywords) {
            $this->bodyKeywords = $bodyKeywords;
            $this->markFieldUpdated('bodyKeywords');
        }

        return $this;
    }

    public function setUrlKeywords(?string $urlKeywords = null): self
    {
        if ($this->urlKeywords !== $urlKeywords) {
            $this->urlKeywords = $urlKeywords;
            $this->markFieldUpdated('urlKeywords');
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

    /**
     * Turns entity attributes into an array
     */
    public function jsonSerialize(): array
    {
        return $this->serializeFields(
            [
                'id',
                'feedId',
                'titleKeywords',
                'bodyKeywords',
                'urlKeywords',
            ]
        );
    }

    public function toAPI(): array
    {
        return $this->serializeFields(
            [
                'id',
                'feedId',
                'titleKeywords',
                'bodyKeywords',
                'urlKeywords',
            ]
        );
    }

    public function toAPI2(bool $reduced = false): array
    {
        return $this->toAPI();
    }
}
