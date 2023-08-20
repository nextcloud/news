<?php

namespace OCA\News\Dashboard;

use OCA\News\Service\FeedServiceV2;
use OCP\AppFramework\Services\IInitialState;
use OCP\Dashboard\IAPIWidget;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Dashboard\Model\WidgetItem;

use Psr\Log\LoggerInterface;

use OCA\News\AppInfo\Application;
use OCA\News\Db\ListType;
use OCP\Util;

class FeedWidget implements IAPIWidget
{

    
    private $l10n;
    private $feedService;
    private $initialStateService;
    private $userId;
    private $urlGenerator;
    private $logger;

    public function __construct(IL10N $l10n,
        IURLGenerator $urlGenerator,
        FeedServiceV2 $feedService,
        IInitialState $initialStateService,
        LoggerInterface $loggerInterface,
        ?string $userId
    ) {
        $this->l10n = $l10n;
        $this->feedService = $feedService;
        $this->initialStateService = $initialStateService;
        $this->userId = $userId;
        $this->urlGenerator = $urlGenerator;
        $this->logger = $loggerInterface;
    }

    public function getId(): string
    {
        return 'news-feed-widget';
    }

    public function getTitle(): string
    {
        $this->logger->debug("Requested title");
        return $this->l10n->t('News Feed widget');
    }

    public function getOrder(): int
    {
        $this->logger->debug("Requested order");
        return 20;
    }

    public function getIconClass(): string
    {
        return 'icon-newsdashboard';
        $this->logger->debug("Requested icon");
    }

    public function getUrl(): ?string
    {
        return $this->urlGenerator->linkToRoute('news.page.index');
        $this->logger->debug("Requested url");
    }

    public function load(): void
    {
        $this->logger->debug("Requested load with user: " . $this->userId);
        if ($this->userId !== null) {
            $items = $this->getItems($this->userId);
            $this->initialStateService->provideInitialState('dashboard-widget-feeds', $items);
        }

        Util::addScript(Application::NAME, 'build/' . Application::NAME . '-dashboard-feeds');
        Util::addStyle(Application::NAME, 'dashboard');
    }

    public function getItems(string $userId, ?string $since = null, int $limit = 7): array
    {
        $items = $this->feedService->findAllForUser($userId);

        return $items;
    }
} 