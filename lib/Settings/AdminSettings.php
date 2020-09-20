<?php

namespace OCA\News\Settings;

use OCA\News\AppInfo\Application;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\Settings\ISettings;

class AdminSettings implements ISettings
{

    /**
     * @var IConfig
     */
    private $config;

    public function __construct(IConfig $config)
    {
        $this->config = $config;
    }

    public function getForm()
    {
        $data = [];

        foreach (array_keys(Application::DEFAULT_SETTINGS) as $setting) {
            $data[$setting] = $this->config->getAppValue(
                Application::NAME,
                $setting,
                Application::DEFAULT_SETTINGS[$setting]
            );
        }

        return new TemplateResponse(Application::NAME, 'admin', $data);
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
