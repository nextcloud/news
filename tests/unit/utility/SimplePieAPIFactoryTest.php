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

namespace OCA\News\Utility;


class SimplePieAPIFactoryTest extends \PHPUnit_Framework_TestCase {


    public function testGetFile() {
        $factory = new SimplePieAPIFactory();
        $file = $factory->getFile('php://input', 10, 5, $headers='headers',
                            $useragent='flashce', $force_fsockopen=true);
        $this->assertTrue($file instanceof \SimplePie_File);
    }


    public function testGetCore() {
        $factory = new SimplePieAPIFactory();
        $this->assertTrue($factory->getCore() instanceof \SimplePie);
    }


}