<?php

return array(
    'grabber' => array(
        '%.*%' => array(
            'test_url' => 'http://www.upi.com/Top_News/US/2015/09/26/Tech-giants-Hollywood-stars-among-guests-at-state-dinner-for-Chinas-Xi-Jinping/4541443281006/',
            'body' => array(
            '//div[@class="img"]',
            '//div[@class="st_text_c"]',
            ),
            'strip' => array(
            '//div[@align="center"]',
            '//div[@class="ad_slot"]',
            '//div[@class="ipara"]',
            '//div[@class="st_embed"]',
            '//div[contains(@styel,"font-size"]',
            '//ul',
            '//style[@type="text/css"]',
            ),
        ),
    ),
);
