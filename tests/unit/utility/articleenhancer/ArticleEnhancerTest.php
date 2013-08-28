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


class TestEnhancer extends ArticleEnhancer {
	public function __construct($purifier, $fileFactory, $articleRegex,
	                            $articleXPATH, $timeout){
		parent::__construct($purifier, $fileFactory, $articleRegex,
		                    $articleXPATH, $timeout);
	}
}


class ArticleEnhancerTest extends \OCA\AppFramework\Utility\TestUtility {

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

		$this->testEnhancer = new TestEnhancer(
			$this->purifier,
			$this->fileFactory,
			'/explosm.net\/comics/', 
			'//*[@id=\'maincontent\']/div[2]/img',
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
		$file->body = '<html>
			<body>
				<div id="maincontent">
					<div>nooo</div>
					<div><img src="hiho"></div>
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
			->with($this->equalTo('<img src="hiho">'))
			->will($this->returnValue('<img src="hiho">'));

		$result = $this->testEnhancer->enhance($item);
		$this->assertEquals('<img src="hiho">', $result->getBody());
	}


	public function testModificationHandlesEmptyResults() {
		$file = new \stdClass;
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


}