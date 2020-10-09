<?php

/**
 * ownCloud - App Framework
 *
 * @author    Bernhard Posselt
 * @copyright 2012 Bernhard Posselt dev@bernhard-posselt.com
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 */


namespace OCA\News\Tests\Unit\Db;

use Doctrine\DBAL\Driver\PDOStatement;
use OCP\IDBConnection;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Simple utility class for testing mappers
 */
abstract class MapperTestUtility extends TestCase
{

    /**
     * @var MockObject|IDBConnection
     */
    protected $db;

    /**
     * @var MockObject|PDOStatement
     */
    protected $query;


    /**
     * Run this function before the actual test to either set or initialize the
     * db. After this the db can be accessed by using $this->db
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->db = $this->getMockBuilder(IDBConnection::class)
                         ->disableOriginalConstructor()
                         ->getMock();

        $this->query = $this->getMockBuilder(\PDOStatement::class)
                            ->getMock();
    }
}
