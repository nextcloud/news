<?php

/**
* ownCloud - News
*
* @author Alessandro Cosentino
* @author Bernhard Posselt
* @copyright 2012 Alessandro Cosentino cosenal@gmail.com
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

namespace OCA\News\Utility\ArticleEnhancer;

use \OCA\News\Db\Item;

require_once(__DIR__ . "/../../../classloader.php");


class XPathArticleEnhancerTest extends \OCA\AppFramework\Utility\TestUtility {

	private $purifier;
	private $testEnhancer;
	private $fileFactory;
	private $timeout;

	protected function setUp() {
		$timeout = 30;
		$this->fileFactory = $this->getMockBuilder('\OCA\News\Utility\SimplePieFileFactory')
			->disableOriginalConstructor()
			->getMock();
		$this->purifier = $this->getMock('purifier', array('purify'));

		$this->testEnhancer = new XPathArticleEnhancer(
			$this->purifier,
			$this->fileFactory,
			array(
				'/explosm.net\/comics/' => '//*[@id=\'maincontent\']/div[2]/div/span',
				'/explosm.net\/shorts/' => '//*[@id=\'maincontent\']/div/div',
				'/explosm.net\/all/' => '//body/*',
				'/themerepublic.net/' => '//*[@class=\'post hentry\']'
			), 
			$this->timeout
		);
	}


	public function testDoesNotModifiyNotMatchingResults() {
		$item = new Item();
		$item->setUrl('http://explosm.net');
		$this->assertEquals($item, $this->testEnhancer->enhance($item));
	}

	
	public function testDoesModifiyArticlesThatMatch() {
		$file = new \stdClass;
		$file->headers = array("content-type"=>"text/html; charset=utf-8");
		$file->body = '<html>
			<body>
				<div id="maincontent">
					<div>nooo</div>
					<div><div><span>hiho</span></div></div>
				</div>
			</body>
		</html>';
		$item = new Item();
		$item->setUrl('https://www.explosm.net/comics/312');
		$item->setBody('Hello thar');

		$this->fileFactory->expects($this->once())
			->method('getFile')
			->with($this->equalTo($item->getUrl()),
				$this->equalTo($this->timeout))
			->will($this->returnValue($file));
		$this->purifier->expects($this->once())
			->method('purify')
			->with($this->equalTo('<span>hiho</span>'))
			->will($this->returnValue('<span>hiho</span>'));

		$result = $this->testEnhancer->enhance($item);
		$this->assertEquals('<span>hiho</span>', $result->getBody());
	}


	public function testDoesModifiyAllArticlesThatMatch() {
		$file = new \stdClass;
		$file->headers = array("content-type"=>"text/html; charset=utf-8");
		$file->body = '<html>
			<body>
				<div id="maincontent">
					<div>nooo<div>hiho</div></div>
					<div><div>rawr</div></div>
				</div>
			</body>
		</html>';
		$item = new Item();
		$item->setUrl('https://www.explosm.net/shorts/312');
		$item->setBody('Hello thar');

		$this->fileFactory->expects($this->once())
			->method('getFile')
			->with($this->equalTo($item->getUrl()),
				$this->equalTo($this->timeout))
			->will($this->returnValue($file));
		$this->purifier->expects($this->once())
			->method('purify')
			->with($this->equalTo('<div>hiho</div><div>rawr</div>'))
			->will($this->returnValue('<div>hiho</div><div>rawr</div>'));

		$result = $this->testEnhancer->enhance($item);
		$this->assertEquals('<div>hiho</div><div>rawr</div>', $result->getBody());
	}


	public function testModificationHandlesEmptyResults() {
		$file = new \stdClass;
		$file->headers = array("content-type"=>"text/html; charset=utf-8");
		$file->body = '<html>
			<body>
				<div id="maincontent">
				</div>
			</body>
		</html>';
		$item = new Item();
		$item->setUrl('https://www.explosm.net/comics/312');
		$item->setBody('Hello thar');

		$this->fileFactory->expects($this->once())
			->method('getFile')
			->with($this->equalTo($item->getUrl()),
				$this->equalTo($this->timeout))
			->will($this->returnValue($file));
		$this->purifier->expects($this->once())
			->method('purify')
			->with($this->equalTo(null))
			->will($this->returnValue(null));

		$result = $this->testEnhancer->enhance($item);
		$this->assertEquals(null, $result->getBody());
	}


	public function testModificationDoesNotBreakOnEmptyDom() {
		$file = new \stdClass;
		$file->headers = array("content-type"=>"text/html; charset=utf-8");
		$file->body = '';
		$item = new Item();
		$item->setUrl('https://www.explosm.net/comics/312');
		$item->setBody('Hello thar');

		$this->fileFactory->expects($this->once())
			->method('getFile')
			->with($this->equalTo($item->getUrl()),
				$this->equalTo($this->timeout))
			->will($this->returnValue($file));
		$this->purifier->expects($this->once())
			->method('purify')
			->with($this->equalTo(null))
			->will($this->returnValue(null));

		$result = $this->testEnhancer->enhance($item);
		$this->assertEquals(null, $result->getBody());
	}


	public function testModificationDoesNotBreakOnBrokenDom() {
		$file = new \stdClass;
		$file->headers = array("content-type"=>"text/html; charset=utf-8");
		$file->body = '<html/><p>
			<body>
				<div id="maincontent">
				</div>
			</body>
		</html>';
		$item = new Item();
		$item->setUrl('https://www.explosm.net/comics/312');
		$item->setBody('Hello thar');

		$this->fileFactory->expects($this->once())
			->method('getFile')
			->with($this->equalTo($item->getUrl()),
				$this->equalTo($this->timeout))
			->will($this->returnValue($file));
		$this->purifier->expects($this->once())
			->method('purify')
			->with($this->equalTo(null))
			->will($this->returnValue(null));

		$result = $this->testEnhancer->enhance($item);
		$this->assertEquals(null, $result->getBody());
	}


	public function testTransformRelativeUrls() {
		$file = new \stdClass;
		$file->headers = array("content-type"=>"text/html; charset=utf-8");
		$file->body = '<html>
			<body>
				<a href="../a/relative/url.html?a=1#b">link</a>
				<a href="b/relative/url.html">link2</a>
				<img src="/another/relative/link.jpg"></img>
			</body>
		</html>';
		$item = new Item();
		$item->setUrl('https://www.explosm.net/all/312');
		$item->setBody('Hello thar');

		$this->fileFactory->expects($this->once())
			->method('getFile')
			->with($this->equalTo($item->getUrl()),
				$this->equalTo($this->timeout))
			->will($this->returnValue($file));
		$this->purifier->expects($this->once())
			->method('purify')
			->with($this->equalTo('<a href="https://www.explosm.net/a/relative/url.html?a=1#b">link</a><a href="https://www.explosm.net/all/b/relative/url.html">link2</a><img src="https://www.explosm.net/another/relative/link.jpg">'))
			->will($this->returnValue('<a href="https://www.explosm.net/a/relative/url.html?a=1#b">link</a><a href="https://www.explosm.net/all/b/relative/url.html">link2</a><img src="https://www.explosm.net/another/relative/link.jpg">'));

		$result = $this->testEnhancer->enhance($item);
		$this->assertEquals('<a href="https://www.explosm.net/a/relative/url.html?a=1#b">link</a><a href="https://www.explosm.net/all/b/relative/url.html">link2</a><img src="https://www.explosm.net/another/relative/link.jpg">', $result->getBody());
	}

	public function testTransformRelativeUrlSpecials() {
		$file = new \stdClass;
		$file->headers = array("content-type"=>"text/html; charset=utf-8");
		$file->body = '<html>
			<body>
				<img src="relative/url.png?a=1&b=2">
			</body>
		</html>';
		$item = new Item();
		$item->setUrl('https://username:secret@www.explosm.net/all/312');
		$item->setBody('Hello thar');

		$this->fileFactory->expects($this->once())
			->method('getFile')
			->with($this->equalTo($item->getUrl()),
				$this->equalTo($this->timeout))
			->will($this->returnValue($file));
		$this->purifier->expects($this->once())
			->method('purify')
			->with($this->equalTo('<img src="https://username:secret@www.explosm.net/all/relative/url.png?a=1&amp;b=2">'))
			->will($this->returnValue('<img src="https://username:secret@www.explosm.net/all/relative/url.png?a=1&amp;b=2">'));

		$result = $this->testEnhancer->enhance($item);
		$this->assertEquals('<img src="https://username:secret@www.explosm.net/all/relative/url.png?a=1&amp;b=2">', $result->getBody());
	}

	public function testDontTransformAbsoluteUrlsAndMails() {
		$file = new \stdClass;
		$file->headers = array("content-type"=>"text/html; charset=utf-8");
		$file->body = '<html>
			<body>
				<img src="http://www.url.com/absolute/url.png">
				<a href="mailto:test@testsite.com">mail</a>
			</body>
		</html>';
		$item = new Item();
		$item->setUrl('https://www.explosm.net/all/312');
		$item->setBody('Hello thar');

		$this->fileFactory->expects($this->once())
			->method('getFile')
			->with($this->equalTo($item->getUrl()),
				$this->equalTo($this->timeout))
			->will($this->returnValue($file));
		$this->purifier->expects($this->once())
			->method('purify')
			->with($this->equalTo('<img src="http://www.url.com/absolute/url.png"><a href="mailto:test@testsite.com">mail</a>'))
			->will($this->returnValue('<img src="http://www.url.com/absolute/url.png"><a href="mailto:test@testsite.com">mail</a>'));

		$result = $this->testEnhancer->enhance($item);
		$this->assertEquals('<img src="http://www.url.com/absolute/url.png"><a href="mailto:test@testsite.com">mail</a>', $result->getBody());
	}

}