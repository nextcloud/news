<?php
return array(
    'grabber' => array(
        '%.*%' => array(
            'test_url' => 'http://www.wired.com/gamelife/2013/09/ouya-free-the-games/',
            'body' => array(
                 '//div[@data-js="gallerySlides"]',
                 '//article',
            ),
            'strip' => array(
                '//*[@id="linker_widget"]',
                '//*[@class="credit"]',
                '//div[@data-js="slideCount"]',
                '//*[contains(@class="visually-hidden")]',
                '//*[@data-slide-number="_endslate"]',
                '//*[@id="related"]',
                '//*[contains(@class, "bio")]',
                '//*[contains(@class, "entry-footer")]',
                '//*[contains(@class, "mobify_backtotop_link")]',
                '//*[contains(@class, "gallery-navigation")]',
                '//*[contains(@class, "gallery-thumbnail")]',
                '//img[contains(@src, "1x1")]',
                '//a[contains(@href, "creativecommons")]',
                '//a[@href="#start-of-content"]',
                '//ul[@id="article-tags"]',
            ),
        )
    )
);


