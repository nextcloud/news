<?php

return array(
    'grabber' => array(
        '%.*%' => array(
            'test_url' => 'http://www.neustadt-ticker.de/36480/aktuell/nachrichten/buergerbuero-neustadt-ab-heute-wieder-geoeffnet',
            'body' => array('//div[contains(@class,"article")]/div[@class="PostContent" and *[not(contains(@class, "navigation"))]]'),
            'strip' => array(
                '//*[@id="wp_rp_first"]',
                '//*[@class="yarpp-related"]',
            ),
        ),
    ),
);
