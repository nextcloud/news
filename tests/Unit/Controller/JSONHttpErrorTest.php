<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Alessandro Cosentino <cosenal@gmail.com>
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Alessandro Cosentino 2012
 * @copyright Bernhard Posselt 2012, 2014
 */

namespace OCA\News\Tests\Unit\Controller;

use OCA\News\Controller\JSONHttpError;

class Test {
    use JSONHttpError;
}

class JSONHttpErrorTest extends \PHPUnit_Framework_TestCase {


    public function testError() {
        $ex = new \Exception('hi');
        $test = new Test();
        $result = $test->error($ex, 3);

        $this->assertEquals(['message' => 'hi'], $result->getData());
        $this->assertEquals(3, $result->getStatus());
    }


}