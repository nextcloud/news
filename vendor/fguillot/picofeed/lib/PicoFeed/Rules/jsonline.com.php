<?php

return array(
    'grabber' => array(
        '%.*%' => array(
            'test_url' => 'http://www.jsonline.com/news/usandworld/as-many-as-a-million-expected-for-popes-last-mass-in-us-b99585180z1-329688131.html',
            'body' => array(
            '//div[@id="article"]',
            '//div[@id="mainContent"]',
            ),
            'strip' => array(
            '//div[@class="storyTimestamp"]',
            '//img[@class="floatLeft"]',
            '//div[@class="overlineUpper"]',
            '//div[@class="updated"]',
            '//div[@class="columnist_link"]',
            '//div[@class="side_container_01"]',
            '//div[@class="credit"]',
            '//h1',
            '//h2',
            '//h4',
            '//ul',
            '//div[contains(@class, "footer-pkg")]',
            '//img[contains(@src,"analytics")]',
            ),
        ),
    ),
);
