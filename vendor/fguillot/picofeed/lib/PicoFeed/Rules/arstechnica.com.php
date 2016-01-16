<?php

return array(
    'grabber' => array(
        '%.*%' => array(
            'test_url' => 'http://arstechnica.com/tech-policy/2015/09/judge-warners-2m-happy-birthday-copyright-is-bogus/',
            'body' => array(
                '//header/h2',
                '//section[@id="article-guts"]',
                '//div[@class="superscroll-content show"]',
                '//div[@class="gallery"]',
            ),
            'next_page' => '//span[@class="numbers"]/a',
            'strip' => array(
                '//figcaption',
                '//div[@class="post-meta"]',
                '//div[@class="gallery-image-credit"]',
                '//aside',
                '//div[@class="article-expander"]',
            ),
        ),
    ),
);
