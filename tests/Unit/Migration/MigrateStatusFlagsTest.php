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

use OCP\DB\QueryBuilder\IParameter;
use OCP\DB\QueryBuilder\IQueryBuilder;
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
        $queryBuilder = $this->createMock(IQueryBuilder::class);
        $queryBuilder->expects($this->exactly(2))
            ->method('createParameter')
            ->with($this->logicalOr('unread_value', 'starred_value'))
            ->willReturn($this->createMock(IParameter::class));
        $queryBuilder->expects($this->exactly(2))
            ->method('update')
            ->with('news_items')
            ->willReturnSelf();
        $setParam = $this->logicalOr('unread', 'starred');
        $queryBuilder->expects($this->exactly(2))
            ->method('set')
            ->with($setParam, $this->isInstanceOf(IParameter::class))
            ->willReturnSelf();
        $queryBuilder->expects($this->exactly(2))
            ->method('where')
            ->with($this->logicalOr('(status & 2)', '(status & 4)'))
            ->willReturnSelf();
        $setParameterName = $this->logicalOr('unread_value', 'starred_value');
        $queryBuilder->expects($this->exactly(2))
            ->method('setParameter')
            ->with($setParameterName, true, IQueryBuilder::PARAM_BOOL)
            ->willReturnSelf();
        $queryBuilder->expects($this->exactly(2))
            ->method('execute')
            ->with();

        $this->config->expects($this->exactly(1))
            ->method('getAppValue')
            ->with('news', 'installed_version', '0.0.0')
            ->willReturn('11.0.5');
        $this->db->expects($this->exactly(1))
            ->method('getQueryBuilder')
            ->with()
            ->willReturn($queryBuilder);
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
            ->method('getQueryBuilder');

        $migration = new MigrateStatusFlags($this->db, $this->config);
        $migration->run($this->output);
    }
}