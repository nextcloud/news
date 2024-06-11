<?php
declare(strict_types=1);

namespace OCA\News\Search;

use OCA\News\Service\FeedServiceV2;
use OCA\News\AppInfo\Application;
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

    public function __construct(
        private IL10N $l10n,
        private IURLGenerator $urlGenerator,
        private FeedServiceV2 $service
    ) {
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
        if (strpos($route, Application::NAME . '.') === 0) {
            // Active app, prefer my results
            return -1;
        }

        return 60;
    }

    public function search(IUser $user, ISearchQuery $query): SearchResult
    {
        $list = [];
        $term = $query->getFilter('term')?->get() ?? '';
        $term = strtolower($term);

        $imageurl  = $this->urlGenerator->imagePath('core', 'rss.svg');
        $feeduiurl = $this->urlGenerator->linkToRoute('news.page.index') . '#/feed/';
        foreach ($this->service->findAllForUser($user->getUID()) as $feed) {
            if (strpos(strtolower($feed->getTitle()), $term) === false) {
                continue;
            }

            $list[] = new SearchResultEntry(
                $imageurl,
                $feed->getTitle(),
                $this->l10n->t('Unread articles') . ': ' . $feed->getUnreadCount(),
                $feeduiurl . $feed->getId()
            );
        }

        return SearchResult::complete($this->l10n->t('News'), $list);
    }
}
