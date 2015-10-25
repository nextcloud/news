<?php

return array(
    'grabber' => array(
        '%.*%' => array(
            'test_url' => 'http://www.buenosairesherald.com/article/199344/manzur-named-next-governor-of-tucum%C3%A1n',
            'body' => array(
            '//div[@class="img_despliege"]',
            '//div[@id="nota_despliegue"]',
            ),
            'strip' => array(
            '//script',
            '//span[@id="fecha"]',
            '//h1',
            '//div[@class="autor"]',
            ),
        ),
    ),
);
