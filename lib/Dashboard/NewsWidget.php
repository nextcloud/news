<?php
namespace OCA\News\Dashboard;

use OCP\Dashboard\IWidget;
use OCP\IL10N;
use OCP\IURLGenerator;

class NewsWidget implements IWidget
{
    /**
     * @var IL10N
     */
    private $l10n;
    /**
     * @var IURLGenerator
     */
    private $urlGenerator;

    public function __construct(IL10N $l10n, IURLGenerator $urlGenerator)
    {
        $this->l10n = $l10n;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return 'news';
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return $this->l10n->t('Latest news');
    }

    /**
     * @inheritDoc
     */
    public function getOrder(): int
    {
        return 20;
    }

    /**
     * @inheritDoc
     */
    public function getIconClass(): string
    {
        return 'icon-news';
    }

    /**
     * @inheritDoc
     */
    public function getUrl(): ?string
    {
        return $this->urlGenerator->linkToRoute('/apps/news');
    }

    /**
     * @inheritDoc
     */
    public function load(): void
    {
        \OCP\Util::addScript('news', 'build/widget.min');
    }
}
