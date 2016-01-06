<?php
return array(
    'grabber' => array(
        '%.*%' => array(
            'test_url' => 'http://www.thelocal.se/20151018/swedish-moderates-tighten-focus-on-begging-ban',
            'body' => array(
                '//article',
            ),
            'strip' => array(
                '//p[@id="mobile-signature"]',
                '//article/div[4]',
                '//article/ul[1]',
                '//div[@class="clr"]',
                '//p[@class="small"]',
                '//p[@style="font-weight: bold; font-size: 14px;"]',
                '//div[@class="author"]',
                '//div[@class="ad_container"]',
        )
        )
    )
);
