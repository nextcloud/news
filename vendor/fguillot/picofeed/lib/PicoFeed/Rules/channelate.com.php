<?php

return array(
    'filter' => array(
        '%.*%' => array(
            '%href="http://www.channelate.com/(\\d+)/(\\d+)/(\\d+)/[^"]*"%' => 'href="http://www.channelate.com/extra-panel/$1$2$3/"',
        ),
    ),
);
