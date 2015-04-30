<?php
return array(
    'grabber' => array(
        '%/comic.*%' => array(
            'test_url' => 'http://www.loadingartist.com/comic/lifted-spirits/',
            'body' => array('//div[@class="comic"]'),
            'strip' => array(),
        )
    )
);