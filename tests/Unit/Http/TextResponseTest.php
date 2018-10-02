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


namespace OCA\News\Tests\Unit\Http;

use PHPUnit\Framework\TestCase;
use OCA\News\Http\TextResponse;

class TextResponseTest extends TestCase
{


    protected function setUp() 
    {
        $this->response = new TextResponse('sometext');
    }


    public function testRender() 
    {
        $this->assertEquals('sometext', $this->response->render());
    }

    public function testContentTypeDefaultsToText()
    {
        $headers = $this->response->getHeaders();

        $this->assertEquals('text/plain', $headers['Content-type']);
    }


    public function testContentTypeIsSetableViaConstructor()
    {
        $response = new TextResponse('sometext', 'html');
        $headers = $response->getHeaders();

        $this->assertEquals('text/html', $headers['Content-type']);
    }

}