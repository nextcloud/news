<?php

/**
 * ownCloud - News
 *
 * @author Bernhard Posselt
 * @copyright 2012 Bernhard Posselt dev@bernhard-posselt.com
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */


namespace OCA\News\Utility;


require_once(__DIR__ . "/../../classloader.php");


class FaviconFetcherTest extends \PHPUnit_Framework_TestCase {

	private $fetcher;
	private $fileFactory;
	private $png;
	private $proxyHost;
	private $proxyPort;
	private $proxyAuth;

	protected function setUp(){
		$this->png = "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A";
		$this->proxyHost = 'test';
		$this->proxyPort = 3;
		$this->proxyAuth = 'hi';
		$this->fileFactory = $this->getMockBuilder(
			'\OCA\News\Utility\SimplePieAPIFactory')
			->disableOriginalConstructor()
			->getMock();
		$this->config = $this->getMockBuilder(
			'\OCA\News\Utility\Config')
			->disableOriginalConstructor()
			->getMock();
		$this->config->expects($this->any())
			->method('getProxyHost')
			->will($this->returnValue(''));
		$this->config->expects($this->any())
			->method('getProxyAuth')
			->will($this->returnValue($this->proxyAuth));
		$this->config->expects($this->any())
			->method('getProxyPort')
			->will($this->returnValue($this->proxyPort));
		$this->fetcher = new FaviconFetcher($this->fileFactory, $this->config);
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


	public function testProxySettingsAreUsed() {
		$this->config = $this->getMockBuilder(
			'\OCA\News\Utility\Config')
			->disableOriginalConstructor()
			->getMock();
		$this->config->expects($this->any())
			->method('getProxyHost')
			->will($this->returnValue($this->proxyHost));
		$this->config->expects($this->any())
			->method('getProxyAuth')
			->will($this->returnValue($this->proxyAuth));
		$this->config->expects($this->any())
			->method('getProxyPort')
			->will($this->returnValue($this->proxyPort));
		$this->fetcher = new FaviconFetcher($this->fileFactory, $this->config);

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
				$this->equalTo(null),
				$this->equalTo(false),
				$this->equalTo($this->proxyHost),
				$this->equalTo($this->proxyPort),
				$this->equalTo($this->proxyAuth))
			->will($this->returnValue($pngMock));

		$favicon = $this->fetcher->fetch($url);

		$this->assertEquals('http://google.com/owncloud/core/img/favicon.png', $favicon);
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
				$this->equalTo(null),
				$this->equalTo(false),
				$this->equalTo(null),
				$this->equalTo(null),
				$this->equalTo(null))
			->will($this->returnValue($pngMock));

		$favicon = $this->fetcher->fetch($url);

		$this->assertEquals('http://google.com/owncloud/core/img/favicon.png', $favicon);
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

		$this->assertEquals('http://google.com/owncloud/core/img/favicon.png', $favicon);
	}


	public function testEmptyFilePathDoesNotOpenFile() {
		$faviconPath = "owncloud/core/img/favicon.png";
		$html = $this->getFaviconHTML($faviconPath);

		$url = '';
		$pageMock = $this->getFileMock($html);
		$pngMock = $this->getFileMock($this->png);

		$this->fileFactory->expects($this->never())
			->method('getFile');

		$favicon = $this->fetcher->fetch($url);
	}

	public function testInvalidHostnameDoesNotOpenFile() {
		$faviconPath = "owncloud/core/img/favicon.png";
		$html = $this->getFaviconHTML($faviconPath);

		$url = "a.b_c.de";
		$pageMock = $this->getFileMock($html);
		$pngMock = $this->getFileMock($this->png);

		$this->fileFactory->expects($this->never())
			->method('getFile');

		$favicon = $this->fetcher->fetch($url);
	}


	public function testInvalidHostnameDoesNotOpenFileHttp() {
		$faviconPath = "owncloud/core/img/favicon.png";
		$html = $this->getFaviconHTML($faviconPath);

		$url = "http://a.b_c.de";
		$pageMock = $this->getFileMock($html);
		$pngMock = $this->getFileMock($this->png);

		$this->fileFactory->expects($this->never())
			->method('getFile');

		$favicon = $this->fetcher->fetch($url);
	}


}
