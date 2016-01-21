<?php

return array(
    'grabber' => array(
        '%.*%' => array(
            'test_url' => 'http://m.brewers.mlb.com/news/article/161364798',
            'body' => array(
                '//article',
            ),
            'strip' => array(
                '//div[@class="article-top"]',
                '//div[contains(@class, "contributor-bottom")]',
                '//p[@class="tagline"]',
                '//div[contains(@class, "social-")]',
                '//div[@class="button-wrap"]',
            ),
        ),
    ),
);
