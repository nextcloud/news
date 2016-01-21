<?php

return array(
    'grabber' => array(
        '%.*%' => array(
            'test_url' => 'http://www.aljazeera.com/news/2015/09/xi-jinping-seattle-china-150922230118373.html',
            'body' => array(
                '//figure[@class="article-content"]',
                '//div[@class="article-body"]',
            ),
            'strip' => array(
                '//h1',
                '//h3',
                '//ul',
                '//table[contains(@class, "in-article-item")]',
                '//a[@target="_self"]',
                '//div[@data-embed-type="Brightcove"]',
                '//div[@class="QuoteContainer"]',
            ),
        ),
    ),
);
