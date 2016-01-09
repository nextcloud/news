<?php
return array(
    'grabber' => array(
        '%.*%' => array(
            'test_url' => 'http://www.franceculture.fr/emission-culture-eco-la-finance-aime-toujours-la-france-2016-01-08',
            'body' => array(
                '//div[@class="listen"]',
                '//div[@class="field-items"]',
            ),
            'strip' => array(
            ),
        )
    )
);
