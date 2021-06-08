<?php
declare(strict_types=1);

namespace OCA\News\Search;

use OCA\News\Service\FeedServiceV2;
use OCA\News\Service\FolderServiceV2;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\Search\IProvider;
use OCP\Search\ISearchQuery;
use OCP\Search\SearchResult;
use OCP\Search\SearchResultEntry;

/**
 * Class FeedSearchProvider
 *
 * @package OCA\News\Search
 */
class FeedSearchProvider implements IProvider
{
    /** @var IL10N */
    private $l10n;

    /** @var IURLGenerator */
    private $urlGenerator;

    /** @var FeedServiceV2 */
    private $service;

    public function __construct(IL10N $l10n, IURLGenerator $urlGenerator, FeedServiceV2 $service)
    {
        $this->l10n = $l10n;
        $this->urlGenerator = $urlGenerator;
        $this->service = $service;
    }

    public function getId(): string
    {
        return 'news_feed';
    }

    public function getName(): string
    {
        return $this->l10n->t('News feeds');
    }

    public function getOrder(string $route, array $routeParameters): int
    {
        if ($route === 'news.page.index') {
            // Active app, prefer my results
            return -1;
        }

        return 60;
    }

    public function search(IUser $user, ISearchQuery $query): SearchResult
    {
        $list = [];
        $term = strtolower($query->getTerm());

        foreach ($this->service->findAllForUser($user->getUID()) as $feed) {
            if (strpos(strtolower($feed->getTitle()), $term) === false) {
                continue;
            }

            $list[] = new SearchResultEntry(
                $this->urlGenerator->imagePath('core', 'filetypes/text.svg'),
                $feed->getTitle(),
                $this->l10n->t('Unread articles') . ': ' . $feed->getUnreadCount(),
                $this->urlGenerator->linkToRoute('news.page.index') . '#/items/feeds/' . $feed->getId()
            );
        }

        return SearchResult::complete($this->l10n->t('News'), $list);
    }
}
