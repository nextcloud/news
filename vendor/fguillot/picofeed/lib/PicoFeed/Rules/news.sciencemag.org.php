<?php
return array(
    'grabber' => array(
        '%.*%' => array(
            'test_url' => 'http://news.sciencemag.org/biology/2015/09/genetic-engineering-turns-common-plant-cancer-fighter',
            'body' => array(
                '//div[@class="content"]',
            ),
            'strip' => array(
                '//h1[@class="snews-article__headline"]',
                '//div[contains(@class,"easy_social_box")]',
                '//div[@class="author-teaser"]',
                '//div[@class="article-byline"]',
            ),
        ),
    )
);

