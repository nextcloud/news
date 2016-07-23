<?php
/**
 * ownCloud - News
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
                        ['title' => 'a title2', 'status' => 4, 'guid' => 'def'],
                        ['title' => 'a title3', 'status' => 6, 'guid' => 'gih'],
                        ['title' => 'del1', 'status' => 0],
                        ['title' => 'del2', 'status' => 0],
                        ['title' => 'del3', 'status' => 0],
                        ['title' => 'del4', 'status' => 0]
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
                ['title' => 'no folder', 'status' => 0]
            ]
        ]
    ]
];