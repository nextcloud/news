<?php

return array(
    'grabber' => array(
        '%.*%' => array(
            'test_url' => 'http://arstechnica.com/tech-policy/2015/09/judge-warners-2m-happy-birthday-copyright-is-bogus/',
            'body' => array(
            '//section[@id="article-guts"]',
            '//div[@class="superscroll-content show"]',
            ),
            'strip' => array(
            '//figcaption',
            '//aside',
            '//div[@class="article-expander"]',
            ),
        ),
    ),
);
