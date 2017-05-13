<?php

namespace OCA\News\Settings;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\Settings\ISettings;

use OCA\News\Config\Config;

class Admin implements ISettings {
    private $config;

    public function __construct(Config $config) {
        $this->config = $config;
    }

    public function getForm() {
        $data = [
            'autoPurgeMinimumInterval' =>
                $this->config->getAutoPurgeMinimumInterval(),
            'autoPurgeCount' => $this->config->getAutoPurgeCount(),
            'maxRedirects' => $this->config->getMaxRedirects(),
            'feedFetcherTimeout' => $this->config->getFeedFetcherTimeout(),
            'useCronUpdates' => $this->config->getUseCronUpdates(),
            'maxSize' => $this->config->getMaxSize(),
            'exploreUrl' => $this->config->getExploreUrl(),
        ];
        return new TemplateResponse('news', 'admin', $data, '');
    }

    public function getSection() {
        return 'news';
    }

    public function getPriority() {
        return 40;
    }
}
