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

namespace OCA\News\ArticleEnhancer;

use \OCA\News\Db\Item;

require_once(__DIR__ . "/../../classloader.php");


class EnhancerTest extends \OCA\News\Utility\TestUtility {

	private $enhancer;
	private $articleEnhancer;
	private $articleEnhancer2;

	protected function setUp(){
		$this->enhancer = new Enhancer();
		$this->articleEnhancer = $this->getMockBuilder(
			'\OCA\News\ArticleEnhancer\ArticleEnhancer')
			->disableOriginalConstructor()
			->getMock();
		$this->enhancer->registerEnhancer('test.com', $this->articleEnhancer);
	}


	public function testEnhanceSetsCorrectHash(){
		$item = new Item();
		$item->setUrl('hi');
		$urls = array(
			'https://test.com',
			'https://www.test.com',
			'https://test.com/',
			'http://test.com',
			'http://test.com/',
			'http://www.test.com'
		);
		for ($i=0; $i < count($urls); $i++) { 
			$url = $urls[$i];
			$this->articleEnhancer->expects($this->at($i))
				->method('enhance')
				->with($this->equalTo($item))
				->will($this->returnValue($item));
		}

		for ($i=0; $i < count($urls); $i++) { 
			$url = $urls[$i];
			$result = $this->enhancer->enhance($item, $url);
			$this->assertEquals($item, $result);
		}
		
	}


	public function testNotMatchShouldJustReturnItem() {
		$item = new Item();
		$item->setUrl('hi');

		$url = 'https://tests.com';
		$this->articleEnhancer->expects($this->never())
				->method('enhance');

		$result = $this->enhancer->enhance($item, $url);	
		$this->assertEquals($item, $result);
		
	}


}