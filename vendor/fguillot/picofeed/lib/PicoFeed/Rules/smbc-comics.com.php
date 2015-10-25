<?php

return array(
    'filter' => array(
        '%.*%' => array(
            '%(<img.+)(\.png"/>)%' => '$1$2$1after$2',
        ),
    ),
);
