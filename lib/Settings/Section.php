<?php

namespace OCA\News\Settings;

use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Settings\ISection;

class Section implements ISection {
    private $l;
    private $url;

    public function __construct(IURLGenerator $url, IL10N $l) {
        $this->url = $url;
        $this->l = $l;
    }

    public function getID() {
        return 'news';
    }

    public function getName() {
        return $this->l->t('News');
    }

    public function getPriority() {
        return 10;
    }

    public function getIcon() {
        return $this->url->imagePath('news', 'app.svg');
    }
}