<?php
declare(strict_types=1);

namespace OCA\News\Search;

use OCA\News\AppInfo\Application;
use OCA\News\Db\Folder;
use OCA\News\Service\FolderServiceV2;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\Search\IProvider;
use OCP\Search\ISearchQuery;
use OCP\Search\SearchResult;
use OCP\Search\SearchResultEntry;

/**
 * Class FolderSearchProvider
 *
 * @package OCA\News\Search
 */
class FolderSearchProvider implements IProvider
{
    /** @var IL10N */
    private $l10n;

    /** @var IURLGenerator */
    private $urlGenerator;

    /** @var FolderServiceV2 */
    private $service;

    public function __construct(IL10N $l10n, IURLGenerator $urlGenerator, FolderServiceV2 $folderService)
    {
        $this->l10n = $l10n;
        $this->urlGenerator = $urlGenerator;
        $this->service = $folderService;
    }

    public function getId(): string
    {
        return 'news_folder';
    }

    public function getName(): string
    {
        return $this->l10n->t('News folders');
    }

    public function getOrder(string $route, array $routeParameters): int
    {
        if (strpos($route, Application::NAME . '.') === 0) {
            // Active app, prefer my results
            return 0;
        }

        return 55;
    }

    public function search(IUser $user, ISearchQuery $query): SearchResult
    {
        $list = [];
        $term = $query->getFilter('term')?->get() ?? '';
        $term = strtolower($term);

        $imageurl = $this->urlGenerator->imagePath('core', 'filetypes/folder.svg');
        foreach ($this->service->findAllForUser($user->getUID()) as $folder) {
            if (strpos(strtolower($folder->getName()), $term) === false) {
                continue;
            }

            $list[] = new SearchResultEntry(
                $imageurl,
                $folder->getName(),
                '',
                $this->urlGenerator->linkToRoute('news.page.index') . '#/folder/' . $folder->getId()
            );
        }

        return SearchResult::complete($this->l10n->t('News'), $list);
    }
}
