<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Daniel Opitz <dev@copynpaste.de>
 * @copyright Daniel Opitz 2017
 */

namespace OCA\News\Migration;

use OCP\IConfig;
use OCP\IDBConnection;
use OCP\Migration\IRepairStep;
use OCP\Migration\IOutput;

class MigrateStatusFlags implements IRepairStep {

    /** @var IDBConnection */
    private $db;

    /** @var IConfig */
    private $config;

    /**
     * @param IDBConnection $db
     * @param IConfig $config
     */
    public function __construct(IDBConnection $db, IConfig $config) {
        $this->db = $db;
        $this->config = $config;
    }

    public function getName() {
        return 'Migrate binary status into separate boolean fields';
    }

    public function run(IOutput $output) {
        $version = $this->config->getAppValue('news', 'installed_version', '0.0.0');
        if (version_compare($version, '11.0.6', '>=')) {
            return;
        }

        $update = 'UPDATE `*PREFIX*news_items` ' .
            'SET unread = IF(status & 2, 1, 0), starred = IF(status & 4, 1, 0)';

        $output->startProgress();
        $this->db->executeUpdate($update);
        $output->finishProgress();
    }

}