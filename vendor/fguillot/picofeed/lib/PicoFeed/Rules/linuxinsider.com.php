<?php

return array(
    'grabber' => array(
        '%.*%' => array(
            'test_url' => 'http://www.linuxinsider.com/story/82526.html?rss=1',
            'body' => array(
            '//div[@id="story-graphic-xlarge"]',
            '//div[@id="story-body"]',
            ),
            'strip' => array(
            '//script',
            '//div[@class="story-advertisement"]',
            '//iframe',
            ),
        ),
    ),
);
