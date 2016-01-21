<?php

return array(
    'grabber' => array(
        '%.*%' => array(
            'test_url' => 'http://international.thenewslens.com/post/255032/',
            'body' => array(
                '//article/main[contains(@class, "content-post")]',
            ),
            'strip' => array(
                '//div[@class="photo-credit"]',
                '//p[@align="center"]',
                '//div[@class="clearfix"]',
                '//div[@class="authorZone"]',
                '//style',
                '//div[@id="ttsbox"]',
                '//div[@id="ttscontrols"]',
                '//div[@class="author-info"]',
                '//div[contains(@id, "-ad")]',
                '//div[@style="font-size:small;margin:3px 0 0 0;vertical-align:top;line-height:24px;"]',
                '//div[contains(@class, "hidden-xs")]',
                '//div[contains(@class, "visible-xs")]',
                '//div[contains(@class, "visible-lg")]',
                '//a[@name="comment-panel"]',
            ),
        ),
    ),
);
