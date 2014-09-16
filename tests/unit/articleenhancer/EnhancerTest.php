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

namespace OCA\News\ArticleEnhancer;

use \OCA\News\Db\Item;


class EnhancerTest extends \PHPUnit_Framework_TestCase {

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
		$urls = [
			'https://test.com',
			'https://www.test.com',
			'https://test.com/',
			'http://test.com',
			'http://test.com/',
			'http://www.test.com'
		];
		for ($i=0; $i < count($urls); $i++) { 
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