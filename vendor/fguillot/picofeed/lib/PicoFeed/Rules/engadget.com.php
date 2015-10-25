<?php

return array(
    'grabber' => array(
        '%.*%' => array(
            'test_url' => 'http://www.engadget.com/2015/04/20/dark-matter-discovery/?ncid=rss_truncated',
            'body' => array('//div[@class="article-content"]/p[not(@class="read-more")] | //div[@class="article-content"]/div[@style="text-align: center;"]'),
            'strip' => array(),
        ),
    ),
);
