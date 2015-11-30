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
                    'items' => [
                        ['title' => 'a title1'],
                        ['title' => 'a title2', 'starred' => true],
                        ['title' => 'a title3', 'starred' => true],
                        ['title' => 'del1', 'read' => true],
                        ['title' => 'del2', 'read' => true],
                        ['title' => 'del3', 'read' => true],
                        ['title' => 'del4', 'read' => true]
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
            'feeds' => [
                [
                    'title' => 'third feed',
                    'url' => 'http://heise.de',
                    'items' => [['title' => 'the title9']]
                ],
                [
                    'title' => 'sixth feed',
                    'url' => 'http://gremlins.de',
                    'deletedAt' => 999999999,
                    'items' => [['title' => 'not found feed']]
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
                    'items' => [['title' => 'not found folder']]
                ]
            ],
        ]
    ],
    'feeds' => [
        [
            'title' => 'fourth feed',
            'url' => 'http://blog.fefe.de',
            'items' => [
                ['title' => 'no folder', 'read' => true]
            ]
        ]
    ]
];