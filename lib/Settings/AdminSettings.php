<?php

namespace OCA\News\Settings;

use OCA\News\AppInfo\Application;
use OCA\News\Service\StatusService;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IAppConfig;
use OCP\Settings\ISettings;
use OCP\AppFramework\Services\IInitialState;

class AdminSettings implements ISettings
{

    public function __construct(
        private IAppConfig $config,
        private IInitialState $initialState,
        private StatusService $service
    ) {
    }

    public function getForm()
    {
        foreach (array_keys(Application::DEFAULT_SETTINGS) as $setting) {
            $this->initialState->provideInitialState($setting, $this->config->getValueString(
                Application::NAME,
                $setting,
                (string) Application::DEFAULT_SETTINGS[$setting]
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
