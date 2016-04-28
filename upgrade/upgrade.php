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

namespace OCA\News\Upgrade;

use OCP\IConfig;
use OCA\News\Service\ItemService;
use OCP\IDBConnection;

class Upgrade {

    /** @var IConfig */
    private $config;

    /** @var ItemService */
    private $itemService;

    private $appName;
    /**
     * @var IDBConnection
     */
    private $db;

    /**
     * Upgrade constructor.
     * @param IConfig $config
     * @param $appName
     */
    public function __construct(IConfig $config, ItemService $itemService,
                                IDBConnection $db, $appName) {
        $this->config = $config;
        $this->appName = $appName;
        $this->itemService = $itemService;
        $this->db = $db;
    }

    public function upgrade() {
        $previousVersion = $this->config->getAppValue(
            $this->appName, 'installed_version'
        );

        if (version_compare($previousVersion, '8.7.3', '<=')) {
            $this->itemService->generateSearchIndices();
        }
    }

    public function preUpgrade() {
        $previousVersion = $this->config->getAppValue(
            $this->appName, 'installed_version'
        );

        $dbType = $this->config->getSystemValue('dbtype');
        if (version_compare($previousVersion, '8.2.2', '<') &&
            $dbType !== 'sqlite3'
        ) {
            $sql = 'ALTER TABLE `*PREFIX*news_feeds` DROP COLUMN 
                      `last_modified`';
            $query = $this->db->prepare($sql);
            $query->execute();
        }
    }

}
