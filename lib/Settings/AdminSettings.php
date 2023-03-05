<?php

namespace OCA\News\Settings;

use OCA\News\AppInfo\Application;
use OCA\News\Service\StatusService;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\Settings\ISettings;
use OCP\AppFramework\Services\IInitialState;

class AdminSettings implements ISettings
{

    /**
     * @var IConfig
     */
    private $config;
    /** @var IInitialState */
    private $initialState;
    /** @var StatusService */
    private $service;

    public function __construct(IConfig $config, IInitialState $initialState, StatusService $service)
    {
        $this->config = $config;
        $this->initialState = $initialState;
        $this->service = $service;
    }

    public function getForm()
    {
        foreach (array_keys(Application::DEFAULT_SETTINGS) as $setting) {
            $this->initialState->provideInitialState($setting, $this->config->getAppValue(
                Application::NAME,
                $setting,
                (string)Application::DEFAULT_SETTINGS[$setting]
            ));
        }
        
        if ($this->service->isCronProperlyConfigured()) {
            $lastUpdate = $this->service->getUpdateTime();
        } else {
            $lastUpdate = 0;
        }
        
        $this->initialState->provideInitialState("lastCron", $lastUpdate);

        return new TemplateResponse(Application::NAME, 'admin', []);
    }

    public function getSection()
    {
        return 'news';
    }

    public function getPriority()
    {
        return 40;
    }
}
