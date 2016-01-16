<?php

return array(
    'grabber' => array(
        '%.*%' => array(
            'test_url' => 'http://www.linux.org/threads/lua-the-scripting-interpreter.8352/',
            'body' => '//div[@class="messageContent"]',
            'strip' => array(
            '//aside',
            ),
        ),
    ),
);
