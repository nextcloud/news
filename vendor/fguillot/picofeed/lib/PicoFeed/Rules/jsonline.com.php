<?php

return array(
    'grabber' => array(
        '%.*%' => array(
            'test_url' => 'http://www.jsonline.com/news/usandworld/as-many-as-a-million-expected-for-popes-last-mass-in-us-b99585180z1-329688131.html',
            'body' => array(
                '//div[@id="mainContent"]',
            ),
            'strip' => array(
                '//script',
                '//h1',
                '//h4[@class="credit"]',
                '//div[@class="columnist_container"]',
                '//div[@class="storyTimestamp"]',
                '//ul[@id="sharing-tools"]',
                '//div[@class="title"]',
                '//img[@class="floatLeft"]',
                '//div[@class="first feature"]',
                '//div[@class="collateral_article_content"]',
            ),
        ),
    ),
);
