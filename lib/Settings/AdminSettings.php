<?php

namespace OCA\News\Settings;

use OCA\News\AppInfo\Application;
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

    public function __construct(IConfig $config, IInitialState $initialState)
    {
        $this->config = $config;
        $this->initialState = $initialState;
    }

    public function getForm()
    {
        foreach (array_keys(Application::DEFAULT_SETTINGS) as $setting) {
            $this->initialState->provideInitialState($setting, $this->config->getAppValue(
                Application::NAME,
                $setting,
                Application::DEFAULT_SETTINGS[$setting])
            );
        }

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
