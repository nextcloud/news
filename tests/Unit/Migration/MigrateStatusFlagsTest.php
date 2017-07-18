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
use OCP\Migration\IOutput;
use Test\TestCase;

class MigrateStatusFlagsTest extends TestCase {

    /** @var IDBConnection|\PHPUnit_Framework_MockObject_MockObject */
    protected $db;
    /** @var IConfig|\PHPUnit_Framework_MockObject_MockObject */
    protected $config;
    /** @var IOutput|\PHPUnit_Framework_MockObject_MockObject */
    protected $output;

    protected function setUp() {
        $this->db = $this->createMock(IDBConnection::class);
        $this->config = $this->createMock(IConfig::class);
        $this->output = $this->createMock(IOutput::class);
    }

    public function testRun() {
        $sql = $update = 'UPDATE `*PREFIX*news_items` ' .
            'SET unread = IF(status & 2, 1, 0), starred = IF(status & 4, 1, 0)';

        $this->config->expects($this->exactly(1))
            ->method('getAppValue')
            ->with('news', 'installed_version', '0.0.0')
            ->willReturn('11.0.5');
        $this->db->expects($this->exactly(1))
            ->method('executeUpdate')
            ->with($sql);
        $this->output->expects($this->exactly(1))
            ->method('startProgress');
        $this->output->expects($this->exactly(1))
            ->method('finishProgress');

        $migration = new MigrateStatusFlags($this->db, $this->config);
        $migration->run($this->output);
    }

    public function testRunNewerVersion() {
        $this->config->expects($this->exactly(1))
            ->method('getAppValue')
            ->with('news', 'installed_version', '0.0.0')
            ->willReturn('11.1.0');
        $this->db->expects($this->exactly(0))
            ->method('executeUpdate');

        $migration = new MigrateStatusFlags($this->db, $this->config);
        $migration->run($this->output);
    }
}