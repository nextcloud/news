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

namespace OCA\News\Service;

use OCA\News\Db\Filter;
use OCA\News\Db\FilterMapperV2;
use OCA\News\Db\Item;
use OCA\News\Db\ItemMapperV2;
use OCA\News\Service\Exceptions\ServiceNotFoundException;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use Psr\Log\LoggerInterface;

/**
 * Class FilterService
 *
 * @package OCA\News\Service
 */
class FilterService extends Service
{
    /**
     * FilterService constructor.
     *
     * @param FilterMapperV2  $mapper
     * @param ItemMapperV2    $itemMapper
     * @param LoggerInterface $logger
     */
    public function __construct(
        FilterMapperV2 $mapper,
        protected ItemMapperV2 $itemMapper,
        LoggerInterface $logger
    ) {
        parent::__construct($mapper, $logger);
    }

    /**
     * Find all filters for a user.
     *
     * @param string $userId
     * @param array  $params
     *
     * @return Filter[]
     */
    public function findAllForUser(string $userId, array $params = []): array
    {
        return $this->mapper->findAllFromUser($userId, $params);
    }

    /**
     * Find all filters.
     *
     * @return Filter[]
     */
    public function findAll(): array
    {
        return $this->mapper->findAll();
    }

    /**
     * Find a filter by feed ID for a user.
     *
     * @param string $userId
     * @param int    $feedId
     *
     * @return Filter|null
     */
    public function findByFeedId(string $userId, int $feedId): ?Filter
    {
        try {
            return $this->mapper->findByFeedId($userId, $feedId);
        } catch (DoesNotExistException $ex) {
            return null;
        }
    }

    /**
     * Apply filters to newly fetched items for a feed.
     *
     * Retrieves the filter for the given feed (if one exists) and marks
     * any matching unread items as read.
     *
     * @param string $userId
     * @param int    $feedId
     *
     * @return int Number of items marked as read
     */
    public function applyFilters(string $userId, int $feedId): int
    {
        $filter = $this->findByFeedId($userId, $feedId);

        if ($filter === null) {
            return 0;
        }

        if ($this->filterIsEmpty($filter)) {
            return 0;
        }

        $items = $this->itemMapper->findAllInFeedAfter($userId, $feedId, 0, false);

        $markedCount = 0;

        foreach ($items as $item) {
            /** @var Item $item */
            if (!$item->isUnread()) {
                continue;
            }

            if ($this->itemMatchesFilter($item, $filter)) {
                $item->setUnread(false);
                $item->setFiltered(true);
                $this->itemMapper->update($item);
                $markedCount++;
            }
        }

        return $markedCount;
    }

    /**
     * Check if a filter has no keywords in any field.
     *
     * @param Filter $filter
     *
     * @return bool
     */
    private function filterIsEmpty(Filter $filter): bool
    {
        $title  = trim($filter->getTitleKeywords() ?? '');
        $body   = trim($filter->getBodyKeywords() ?? '');
        $url    = trim($filter->getUrlKeywords() ?? '');

        return $title === '' && $body === '' && $url === '';
    }

    /**
     * Check if an item matches a filter's keyword rules.
     *
     * Uses OR logic: if ANY keyword field matches, the item matches.
     *
     * @param Item   $item
     * @param Filter $filter
     *
     * @return bool
     */
    private function itemMatchesFilter(Item $item, Filter $filter): bool
    {
        $titleKeywords = $this->parseKeywords($filter->getTitleKeywords());
        $bodyKeywords  = $this->parseKeywords($filter->getBodyKeywords());
        $urlKeywords   = $this->parseKeywords($filter->getUrlKeywords());

        // Check title
        if (!empty($titleKeywords) && $this->keywordsMatch($titleKeywords, $item->getTitle() ?? '')) {
            return true;
        }

        // Check body
        if (!empty($bodyKeywords) && $this->keywordsMatch($bodyKeywords, $item->getBody() ?? '')) {
            return true;
        }

        // Check URL
        if (!empty($urlKeywords) && $this->keywordsMatch($urlKeywords, $item->getUrl() ?? '')) {
            return true;
        }

        return false;
    }

    /**
     * Split a comma-separated keyword string into an array.
     *
     * Trims whitespace and removes empty entries.
     *
     * @param string|null $keywordString
     *
     * @return string[]
     */
    private function parseKeywords(?string $keywordString): array
    {
        if ($keywordString === null || trim($keywordString) === '') {
            return [];
        }

        $keywords = explode(',', $keywordString);
        $keywords = array_map('trim', $keywords);
        $keywords = array_filter($keywords, function (string $kw): bool {
            return $kw !== '';
        });

        return array_values($keywords);
    }

    /**
     * Check if ANY keyword from the list appears in the given text.
     *
     * Matching is case-insensitive.
     *
     * @param string[] $keywords
     * @param string   $text
     *
     * @return bool
     */
    private function keywordsMatch(array $keywords, string $text): bool
    {
        foreach ($keywords as $keyword) {
            if (mb_stripos($text, $keyword, 0, 'UTF-8') !== false) {
                return true;
            }
        }
        return false;
    }
}
