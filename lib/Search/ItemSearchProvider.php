<?php
declare(strict_types=1);

namespace OCA\News\Search;

use OCA\News\Service\ItemServiceV2;
use OCA\News\AppInfo\Application;
use OCA\News\Db\ListType;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\Search\IProvider;
use OCP\Search\ISearchQuery;
use OCP\Search\SearchResult;
use OCP\Search\SearchResultEntry;

/**
 * Class ItemSearchProvider
 *
 * @package OCA\News\Search
 */
class ItemSearchProvider implements IProvider
{
    /** @var IL10N */
    private $l10n;

    /** @var IURLGenerator */
    private $urlGenerator;

    /** @var ItemServiceV2 */
    private $service;

    public function __construct(IL10N $l10n, IURLGenerator $urlGenerator, ItemServiceV2 $service)
    {
        $this->l10n = $l10n;
        $this->urlGenerator = $urlGenerator;
        $this->service = $service;
    }

    public function getId(): string
    {
        return 'news_item';
    }

    public function getName(): string
    {
        return $this->l10n->t('News articles');
    }

    public function getOrder(string $route, array $routeParameters): int
    {
        if (strpos($route, Application::NAME . '.') === 0) {
            // Active app, prefer my results
            return 1;
        }

        return 65;
    }

    private function stripTruncate(string $string, int $length = 50): string
    {
        $string = strip_tags(trim($string));

        if (strlen($string) > $length) {
            $string = wordwrap($string, $length);
            $string = explode("\n", $string, 2);
            $string = $string[0];
        }

        return $string;
    }

    public function search(IUser $user, ISearchQuery $query): SearchResult
    {
        $list = [];
        $offset = (int) ($query->getCursor() ?? 0);
        $limit = $query->getLimit();
        $term = $query->getFilter('term')?->get() ?? '';
        $search_result = $this->service->findAllWithFilters(
            $user->getUID(),
            ListType::ALL_ITEMS,
            $limit,
            $offset,
            false,
            [$term]
        );

        $last = end($search_result);
        if ($last === false) {
            return SearchResult::complete(
                $this->l10n->t('News'),
                []
            );
        }

        $icon = $this->urlGenerator->imagePath('core', 'filetypes/text.svg');

        foreach ($search_result as $item) {
            $list[] = new SearchResultEntry(
                $icon,
                $item->getTitle(),
                $this->stripTruncate($item->getBody(), 50),
                $this->urlGenerator->linkToRoute('news.page.index') . '#/feed/' . $item->getFeedId()
            );
        }

        return SearchResult::paginated($this->l10n->t('News'), $list, $last->getId());
    }
}
