<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2015
 */

return [
    'feeds' => [
        [
            'title' => 'john feed',
            'userId' => 'john',
            'items' => [
                ['title' => 'blubb', 'unread' => true, 'starred' => false],
                ['title' => 'blubb', 'unread' => true, 'starred' => false]
            ]
        ],
        [
            'title' => 'test feed',
            'userId' => 'test',
            'items' => [
                ['title' => 'blubb', 'unread' => true, 'starred' => false],
                ['title' => 'blubbs', 'unread' => true, 'starred' => false],
                ['title' => 'blubb', 'unread' => true, 'starred' => false],
                ['title' => 'blubb', 'unread' => true, 'starred' => false]
            ]
        ]
    ]
];
