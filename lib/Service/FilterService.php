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
use OCA\News\Service\Exceptions\ServiceValidationException;
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
    private const MAX_FIELD_LENGTH = 2048;
    private const MAX_KEYWORDS_PER_FIELD = 100;
    private const MAX_KEYWORD_LENGTH = 128;

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

        // Only unread items can be marked as read here.
        $items = $this->itemMapper->findAllInFeedAfter($userId, $feedId, 0, true);

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
     * Clear filtered flag on all items in a feed, then re-apply the filter
     * to all unread items. Used when a user updates their filter keywords.
     *
     * @param string $userId
     * @param int    $feedId
     *
     * @return int Number of items marked as read
     */
    public function clearAndReapplyFilter(string $userId, int $feedId): int
    {
        $filter = $this->findByFeedId($userId, $feedId);
        $items = $this->itemMapper->findAllInFeedAfter($userId, $feedId, 0, false);
        $hasActiveFilter = $filter !== null && !$this->filterIsEmpty($filter);

        $markedCount = 0;

        foreach ($items as $item) {
            /** @var Item $item */
            $wasFiltered = $item->isFiltered();
            $matches = $hasActiveFilter && $this->itemMatchesFilter($item, $filter);

            $needsUpdate = false;

            if ($item->isFiltered() !== $matches) {
                $item->setFiltered($matches);
                $needsUpdate = true;
            }

            if ($matches && $item->isUnread()) {
                $item->setUnread(false);
                $needsUpdate = true;
                $markedCount++;
            }

            // When filter is removed, restore unread state for items hidden by that filter.
            if (!$hasActiveFilter && $wasFiltered && !$item->isUnread()) {
                $item->setUnread(true);
                $needsUpdate = true;
            }

            if ($needsUpdate) {
                $this->itemMapper->update($item);
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
    public function itemMatchesFilter(Item $item, Filter $filter): bool
    {
        $titleKeywords = $this->parseKeywords($filter->getTitleKeywords());
        $bodyKeywords  = $this->parseKeywords($filter->getBodyKeywords());
        $urlKeywords   = $this->parseKeywords($filter->getUrlKeywords());

        // Check title using whole-word matching to avoid partial-word false positives.
        if ($titleKeywords !== [] && $this->keywordsMatchByWordBoundary($titleKeywords, $item->getTitle() ?? '')) {
            return true;
        }

        // Check body using whole-word matching to avoid partial-word false positives.
        if ($bodyKeywords !== [] && $this->keywordsMatchByWordBoundary($bodyKeywords, $item->getBody() ?? '')) {
            return true;
        }

        // URL matching remains substring-based for path fragments like "/sport/".
        if ($urlKeywords !== [] && $this->keywordsMatchBySubstring($urlKeywords, $item->getUrl() ?? '')) {
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
    private function keywordsMatchBySubstring(array $keywords, string $text): bool
    {
        foreach ($keywords as $keyword) {
            if (mb_stripos($text, $keyword, 0, 'UTF-8') !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if ANY keyword appears as a full term separated by non-word boundaries.
     *
     * Matching is case-insensitive and unicode-aware.
     *
     * @param string[] $keywords
     * @param string   $text
     *
     * @return bool
     */
    private function keywordsMatchByWordBoundary(array $keywords, string $text): bool
    {
        foreach ($keywords as $keyword) {
            $pattern = '/(?<![\\p{L}\\p{N}_])' . preg_quote($keyword, '/') . '(?![\\p{L}\\p{N}_])/iu';
            if (preg_match($pattern, $text) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Normalize and validate user keyword input across all fields.
     *
     * @param string|null $titleKeywords
     * @param string|null $bodyKeywords
     * @param string|null $urlKeywords
     *
     * @return array{titleKeywords: string, bodyKeywords: string, urlKeywords: string}
     *
     * @throws ServiceValidationException
     */
    public function sanitizeAndValidateFilterKeywords(
        ?string $titleKeywords,
        ?string $bodyKeywords,
        ?string $urlKeywords
    ): array {
        return [
            'titleKeywords' => $this->normalizeAndValidateKeywordField($titleKeywords, 'titleKeywords'),
            'bodyKeywords' => $this->normalizeAndValidateKeywordField($bodyKeywords, 'bodyKeywords'),
            'urlKeywords' => $this->normalizeAndValidateKeywordField($urlKeywords, 'urlKeywords'),
        ];
    }

    /**
     * @param string|null $keywordString
     * @param string      $fieldName
     *
     * @return string
     *
     * @throws ServiceValidationException
     */
    private function normalizeAndValidateKeywordField(?string $keywordString, string $fieldName): string
    {
        $raw = trim($keywordString ?? '');

        if ($raw === '') {
            return '';
        }

        if (mb_strlen($raw, 'UTF-8') > self::MAX_FIELD_LENGTH) {
            throw new ServiceValidationException(
                "$fieldName exceeds max length of " . self::MAX_FIELD_LENGTH . ' characters'
            );
        }

        $parts = array_map('trim', explode(',', $raw));
        $parts = array_values(array_filter($parts, static function (string $kw): bool {
            return $kw !== '';
        }));

        if (count($parts) > self::MAX_KEYWORDS_PER_FIELD) {
            throw new ServiceValidationException(
                "$fieldName exceeds max keyword count of " . self::MAX_KEYWORDS_PER_FIELD
            );
        }

        $normalized = [];
        $seen = [];

        foreach ($parts as $kw) {
            if (mb_strlen($kw, 'UTF-8') > self::MAX_KEYWORD_LENGTH) {
                throw new ServiceValidationException(
                    "A keyword in $fieldName exceeds max length of "
                    . self::MAX_KEYWORD_LENGTH
                    . ' characters'
                );
            }

            $key = mb_strtolower($kw, 'UTF-8');
            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $normalized[] = $kw;
        }

        $normalizedString = implode(', ', $normalized);
        if (mb_strlen($normalizedString, 'UTF-8') > self::MAX_FIELD_LENGTH) {
            throw new ServiceValidationException(
                "$fieldName exceeds max length of " . self::MAX_FIELD_LENGTH . ' characters'
            );
        }

        return $normalizedString;
    }
}
