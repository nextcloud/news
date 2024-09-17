<?php
/**
 * @author Sean Molenaar <sean@seanmolenaar.eu>
 *
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\News\Tests\Unit\Utility;

use OCA\News\Utility\Time;
use PHPUnit\Framework\TestCase;

class TimeTest extends TestCase
{
    /**
     * Test if the correct type is returned
     */
    public function testTime(): void
    {
        $cur = time();

        $time = new Time();
        $result = $time->getTime();
        $this->assertIsInt($result);
        $this->assertTrue($result >= $cur);
    }


    public function testMicroTime(): void
    {
        $cur = microtime(true) * 1000000;

        $time = new Time();
        $result = (float) $time->getMicroTime();
        $this->assertTrue($result >= $cur);
    }
}
