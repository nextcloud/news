<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Alessandro Cosentino <cosenal@gmail.com>
 * @author    Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright 2012 Alessandro Cosentino
 * @copyright 2012-2014 Bernhard Posselt
 */

namespace OCA\News\Tests\Unit\Db;

use OCA\News\Db\Folder;
use PHPUnit\Framework\TestCase;

class FolderTest extends TestCase
{


    public function testToAPI()
    {
        $folder = new Folder();
        $folder->setId(3);
        $folder->setName('name');
        $folder->setOpened(false);

        $this->assertEquals(
            [
                'id' => 3,
                'name' => 'name',
                'opened' => false,
                'feeds' => [],
            ], $folder->toAPI()
        );
    }


    public function testSerialize()
    {
        $folder = new Folder();
        $folder->setId(3);
        $folder->setName('john');
        $folder->setParentId(4);
        $folder->setUserId('abc');
        $folder->setOpened(true);
        $folder->setDeletedAt(9);

        $this->assertEquals(
            [
            'id' => 3,
            'parentId' => 4,
            'name' => 'john',
            'userId' => 'abc',
            'opened' => true,
            'deletedAt' => 9,
            ], $folder->jsonSerialize()
        );
    }
}