<?php
/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Alessandro Cosentino <cosenal@gmail.com>
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Alessandro Cosentino 2012
 * @copyright Bernhard Posselt 2012, 2014
 */

namespace OCA\News\Controller;

require_once(__DIR__ . "/../../classloader.php");


class JSONHttpErrorTest extends \PHPUnit_Framework_TestCase {


    public function testError() {
        $ex = new \Exception('hi');
        $result = JSONHttpError::error($ex, 3);

        $this->assertEquals(array('message' => 'hi'), $result->getData());
        $this->assertEquals(3, $result->getStatus());
    }


}