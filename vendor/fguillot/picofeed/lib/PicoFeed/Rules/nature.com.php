<?php
return array(
    'grabber' => array(
       'http://dx.doi.org/10.1038.*%' => array(
            'test_url' => 'http://dx.doi.org/10.1038/525184a',
            'body' => array(
                '//div[@class="content "]',
            ),
            'strip' => array()
        ),
        '%.*%' => array(
            'test_url' => 'http://www.nature.com/doifinder/10.1038/nature.2015.18340',
            'body' => array(
                '//div[contains(@class,"main-content")]',
            ),
            'strip' => array()
        ),
    )
);

