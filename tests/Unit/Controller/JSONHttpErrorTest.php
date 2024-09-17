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

namespace OCA\News\Tests\Unit\Controller;

use OCA\News\Controller\JSONHttpErrorTrait;
use PHPUnit\Framework\TestCase;

class JSONHttpErrorTest extends TestCase
{


    public function testError()
    {
        $ex = new \Exception('hi');
        $test = new DummyTraitingClass();
        $result = $test->error($ex, 3);

        $this->assertEquals(['message' => 'hi'], $result->getData());
        $this->assertEquals(3, $result->getStatus());
    }
}


class DummyTraitingClass
{
    use JSONHttpErrorTrait;
}
