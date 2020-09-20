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

use OCA\News\Db\ItemMapper;
use OCA\News\Db\MapperFactory;
use OCA\News\Utility\Time;
use PHPUnit\Framework\TestCase;

use OCP\IDBConnection;

use OCA\News\Db\Mysql\ItemMapper as MysqlMapper;


class MapperFactoryTest extends TestCase
{

    private $db;
    private $settings;

    public function setUp(): void
    {
        $this->db = $this->getMockBuilder(IDBConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testGetItemMapperSqlite()
    {
        $factory = new MapperFactory($this->db, 'sqlite', new Time());
        $this->assertTrue($factory->build() instanceof ItemMapper);
    }

    public function testGetItemMapperPostgres()
    {
        $factory = new MapperFactory($this->db, 'pgsql', new Time());
        $this->assertTrue($factory->build() instanceof ItemMapper);
    }

    public function testGetItemMapperMysql()
    {
        $factory = new MapperFactory($this->db, 'mysql', new Time());
        $this->assertTrue($factory->build() instanceof MysqlMapper);
    }

}
