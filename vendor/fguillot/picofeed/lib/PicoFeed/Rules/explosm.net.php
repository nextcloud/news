<?php
return array(
    'grabber' => array(
        '%.*%' => array(
            'test_url' => 'http://explosm.net/comics/3803/',
            'body' => array(
                '//div[@id="comic-container"]',
                '//div[@id="comic-container"]//img/@src'
            ),
            'strip' => array(
            ),
        )
    )
);
