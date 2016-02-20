<?php

return array(
    'grabber' => array(
        '%/joyoftech/.*%' => array(
            'body' => array(
                '//img[@width="640"]',
            ),
            'test_url' => 'http://www.geekculture.com/joyoftech/joyarchives/2235.html',
        ),
    ),
);
