<?php

return array(
    'grabber' => array(
        '%.*%' => array(
            'test_url' => 'http://www.csmonitor.com/USA/Politics/2015/0925/John-Boehner-steps-down-Self-sacrificing-but-will-it-lead-to-better-government',
            'body' => array(
            '//figure[@id="image-top-1"]',
            '//div[@id="story-body"]',
            ),
            'strip' => array(
            '//script',
            '//img[@title="hide caption"]',
            '//*[contains(@class,"promo_link")]',
            '//div[@id="story-embed-column"]',
            ),
        ),
    ),
);
