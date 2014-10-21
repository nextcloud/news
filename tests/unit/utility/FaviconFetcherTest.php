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


class FaviconFetcherTest extends \PHPUnit_Framework_TestCase {

    private $fetcher;
    private $fileFactory;
    private $png;

    protected function setUp(){
        $this->png = "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A";
        $this->fileFactory = $this->getMockBuilder(
            '\OCA\News\Utility\SimplePieAPIFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->config = $this->getMockBuilder(
            '\OCA\News\Utility\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $this->fetcher = new FaviconFetcher($this->fileFactory);
    }


    protected function getFileMock($body='') {
        $mock = $this->getMockBuilder('\SimplePie_File')
            ->disableOriginalConstructor()
            ->getMock();
        $mock->body = $body;
        return $mock;
    }


    protected function getFileMockCallback($onEqual, $returnMock) {
        $defaultMock = $this->getFileMock();

        return function($url) use ($onEqual, $returnMock, $defaultMock) {
            if($url === $onEqual){
                return $returnMock;
            } else {
                return $defaultMock;
            }
        };
    }


    public function testFetchNoResponseReturnsNull() {
        $mock = $this->getFileMock();

        $this->fileFactory->expects($this->any())
            ->method('getFile')
            ->will($this->returnValue($mock));

        $favicon = $this->fetcher->fetch('dfdf');
        $this->assertNull($favicon);
    }


    public function testNoProxySettingsAreUsed() {
        $faviconPath = "/owncloud/core/img/favicon.png";
        $html = $this->getFaviconHTML($faviconPath);

        $url = 'http://google.com';
        $pageMock = $this->getFileMock($html);
        $pngMock = $this->getFileMock($this->png);

        $this->fileFactory->expects($this->at(0))
            ->method('getFile')
            ->with($this->equalTo('http://google.com'))
            ->will($this->returnValue($pageMock));

        $this->fileFactory->expects($this->at(1))
            ->method('getFile')
            ->with($this->equalTo(
                'http://google.com/owncloud/core/img/favicon.png'),
                $this->equalTo(10),
                $this->equalTo(5),
                $this->equalTo(null),
                $this->equalTo('Mozilla/5.0 AppleWebKit'))
            ->will($this->returnValue($pngMock));

        $favicon = $this->fetcher->fetch($url);

        $this->assertEquals('http://google.com/owncloud/core/img/favicon.png',
            $favicon);
    }


    public function testFetchFaviconFaviconDotIcoHttp(){
        $url = ' sub.google.com ';
        $mock = $this->getFileMock($this->png);

        $callback = $this->getFileMockCallback(
            'http://sub.google.com/favicon.ico', $mock);

        $this->fileFactory->expects($this->any())
            ->method('getFile')
            ->will($this->returnCallback($callback));

        $favicon = $this->fetcher->fetch($url);

        $this->assertEquals('http://sub.google.com/favicon.ico', $favicon);
    }


    public function testFetchFaviconFaviconDotIcoHttpBaseUrl(){
        $url = 'https://google.com/sometetst/dfladsf';
        $mock = $this->getFileMock($this->png);

        $callback = $this->getFileMockCallback(
            'https://google.com/favicon.ico', $mock);

        $this->fileFactory->expects($this->any())
            ->method('getFile')
            ->will($this->returnCallback($callback));

        $favicon = $this->fetcher->fetch($url);

        $this->assertEquals('https://google.com/favicon.ico', $favicon);
    }


    private function getFaviconHTML($faviconPath) {
        return "<html>
            <head>
                <link rel=\"shortcut icon\" href=\"$faviconPath\" />
            </head>
            <body></body>
        </html>";
    }


    public function testIconAbspathHTTP() {
        $faviconPath = "/owncloud/core/img/favicon.png";
        $html = $this->getFaviconHTML($faviconPath);

        $url = 'http://google.com';
        $pageMock = $this->getFileMock($html);
        $pngMock = $this->getFileMock($this->png);

        $this->fileFactory->expects($this->at(0))
            ->method('getFile')
            ->with($this->equalTo('http://google.com'))
            ->will($this->returnValue($pageMock));

        $this->fileFactory->expects($this->at(1))
            ->method('getFile')
            ->with($this->equalTo(
                'http://google.com/owncloud/core/img/favicon.png'))
            ->will($this->returnValue($pngMock));

        $favicon = $this->fetcher->fetch($url);

        $this->assertEquals('http://google.com/owncloud/core/img/favicon.png',
            $favicon);
    }


    public function testEmptyFilePathDoesNotOpenFile() {
        $url = '';

        $this->fileFactory->expects($this->never())
            ->method('getFile');

        $this->fetcher->fetch($url);
    }

    public function testInvalidHostnameDoesNotOpenFile() {
        $url = "a.b_c.de";

        $this->fileFactory->expects($this->never())
            ->method('getFile');

        $this->fetcher->fetch($url);
    }


    public function testInvalidHostnameDoesNotOpenFileHttp() {
        $url = "http://a.b_c.de";

        $this->fileFactory->expects($this->never())
            ->method('getFile');

        $this->fetcher->fetch($url);
    }


}
