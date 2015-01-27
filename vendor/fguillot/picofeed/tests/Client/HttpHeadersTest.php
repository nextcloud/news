<?php
namespace PicoFeed\Client;

use PHPUnit_Framework_TestCase;


class HttpHeadersTest extends PHPUnit_Framework_TestCase
{

    public function testHttpHeadersSet() {
        $headers = new HttpHeaders(array('Content-Type' => 'test'));
        $this->assertEquals('test', $headers['content-typE']);
        $this->assertTrue(isset($headers['ConTent-Type']));

        unset($headers['Content-Type']);
        $this->assertFalse(isset($headers['ConTent-Type']));
    }

}