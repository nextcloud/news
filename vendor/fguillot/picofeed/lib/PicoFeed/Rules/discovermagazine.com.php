<?php

return array(
    'grabber' => array(
        '%.*%' => array(
            'test_url' => 'http://blogs.discovermagazine.com/the-extremo-files/2015/09/11/have-scientists-found-the-worlds-deepest-fish/',
            'body' => array(
            '//div[@class="entry"]',
            ),
            'strip' => array(
            '//h1',
            '//div[@class="meta"]',
            '//div[@class="shareIcons"]',
            '//div[@class="navigation"]',
            ),
         ),
    ),
);
