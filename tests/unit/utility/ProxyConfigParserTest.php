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


class ProxyConfigParserTest extends \PHPUnit_Framework_TestCase {

    private $config;
    private $feedService;
    private $itemService;
    private $parser;

    protected function setUp() {
        $this->config = $this->getMockBuilder(
            '\OCP\IConfig')
            ->disableOriginalConstructor()
            ->getMock();
        $this->parser = new ProxyConfigParser($this->config);
    }

    private function setExpectedProxy($proxy=null, $userpasswd=null) {
        $this->config->expects($this->at(0))
            ->method('getSystemValue')
            ->with($this->equalTo('proxy'))
            ->will($this->returnValue($proxy));
        $this->config->expects($this->at(1))
            ->method('getSystemValue')
            ->with($this->equalTo('proxyuserpwd'))
            ->will($this->returnValue($userpasswd));
    }

    public function testParsesNoProxy() {
        $expected = [
            'host' => null,
            'port' => null,
            'user' => null,
            'password' => null
        ];
        $this->setExpectedProxy();
        $result = $this->parser->parse();
        $this->assertEquals($expected, $result);
    }


    public function testParsesHost() {
        $expected = [
            'host' => 'http://google.com/mytest',
            'port' => null,
            'user' => null,
            'password' => null
        ];
        $this->setExpectedProxy('http://google.com/mytest');
        $result = $this->parser->parse();
        $this->assertEquals($expected, $result);
    }


    public function testParsesHostAndPort() {
        $expected = [
            'host' => 'http://google.com/mytest',
            'port' => 89,
            'user' => null,
            'password' => null
        ];
        $this->setExpectedProxy('http://google.com:89/mytest');
        $result = $this->parser->parse();
        $this->assertEquals($expected, $result);
    }


    public function testParsesUser() {
        $expected = [
            'host' => null,
            'port' => null,
            'user' => 'john',
            'password' => 'doe:hey'
        ];
        $this->setExpectedProxy(null, 'john:doe:hey');
        $result = $this->parser->parse();
        $this->assertEquals($expected, $result);
    }
}