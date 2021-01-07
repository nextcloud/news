<?php

namespace OCA\News\Settings;

use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Settings\IIconSection;

class AdminSection implements IIconSection
{
    private $l;
    private $url;

    public function __construct(IURLGenerator $url, IL10N $l)
    {
        $this->url = $url;
        $this->l = $l;
    }

    /**
     * @return string
     */
    public function getID()
    {
        return 'news';
    }

    public function getName()
    {
        return $this->l->t('News');
    }

    public function getPriority()
    {
        return 10;
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return $this->url->imagePath('news', 'app-dark.svg');
    }
}
