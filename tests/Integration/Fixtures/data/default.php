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
    'folders' => [
        [
            'name' => 'first folder',
            'feeds' => [
                [
                    'title' => 'first feed',
                    'url' => 'http://google.de',
                    'articlesPerUpdate' => 1,
                    'items' => [
                        ['title' => 'a title1', 'guid' => 'abc'],
                        ['title' => 'a title2', 'unread' => false, 'starred' => true, 'guid' => 'def'],
                        ['title' => 'a title3', 'unread' => true, 'starred' => true, 'guid' => 'gih'],
                        ['title' => 'del1', 'unread' => false, 'starred' => false],
                        ['title' => 'del2', 'unread' => false, 'starred' => false],
                        ['title' => 'del3', 'unread' => false, 'starred' => false],
                        ['title' => 'del4', 'unread' => false, 'starred' => false]
                    ]
                ],
                [
                    'title' => 'second feed',
                    'url' => 'http://golem.de',
                    'items' => []
                ],
            ],
        ],
        [
            'name' => 'second folder',
            'opened' => false,
            'feeds' => [
                [
                    'title' => 'third feed',
                    'url' => 'http://heise.de',
                    'items' => [['title' => 'a title9']]
                ],
                [
                    'title' => 'sixth feed',
                    'url' => 'http://gremlins.de',
                    'deletedAt' => 999999999,
                    'items' => [['title' => 'not found feed', 'guid' => 'not found']]
                ]
            ],
        ],
        [
            'name' => 'third folder',
            'deletedAt' => 9999999999,
            'feeds' => [
                [
                    'title' => 'fifth feed',
                    'url' => 'http://prolinux.de',
                    'items' => [['title' => 'not found folder', 'guid' => 'not found']]
                ]
            ],
        ]
    ],
    'feeds' => [
        [
            'title' => 'fourth feed',
            'url' => 'http://blog.fefe.de',
            'items' => [
                ['title' => 'no folder', 'unread' => false, 'starred' => false]
            ]
        ]
    ]
];