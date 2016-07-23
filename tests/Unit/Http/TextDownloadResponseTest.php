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


namespace OCA\News\Http;


class TextDownloadResponseTest extends \PHPUnit_Framework_TestCase {


    protected function setUp() {
        $this->response = new TextDownloadResponse(
            'sometext', 'file', 'content'
        );
    }


    public function testRender() {
        $this->assertEquals('sometext', $this->response->render());
    }

}