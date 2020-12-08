<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2015
 */


namespace OCA\News\Tests\Integration\Fixtures;

use OCA\News\Db\Folder;

class FolderFixture extends Folder
{
    use Fixture;

    public function __construct(array $defaults = [])
    {
        parent::__construct();

        $defaults = array_merge(
            [
                'parentId' => null,
                'name' => 'folder',
                'userId' => 'test',
                'opened' => true,
                'deletedAt' => 0,
                'lastModified' => 9
            ],
            $defaults
        );
        unset($defaults['feeds']);
        $this->fillDefaults($defaults);
    }

}