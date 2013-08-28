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


class EnhancerTest extends \OCA\AppFramework\Utility\TestUtility {

	private $enhancer;
	private $articleEnhancer;
	private $articleEnhancer2;

	protected function setUp(){
		$this->enhancer = new Enhancer();
		$this->articleEnhancer = $this->getMockBuilder(
			'\OCA\News\Utility\ArticleEnhancer\ArticleEnhancer')
			->disableOriginalConstructor()
			->getMock();
		$this->articleEnhancer2 = $this->getMockBuilder(
			'\OCA\News\Utility\ArticleEnhancer\ArticleEnhancer')
			->disableOriginalConstructor()
			->getMock();
	}


	public function testFetch(){
		$item = new Item();
		$item->setUrl('hi');
		
		$this->articleEnhancer->expects($this->once())
			->method('canHandle')
			->with($this->equalTo($item))
			->will($this->returnValue(true));
		$this->enhancer->registerEnhancer($this->articleEnhancer);

		$this->enhancer->enhance($item);
	}


	public function testMultipleFetchers(){
		$item = new Item();
		$item->setUrl('hi');
		$this->articleEnhancer->expects($this->once())
			->method('canHandle')
			->with($this->equalTo($item))
			->will($this->returnValue(false));
		$this->articleEnhancer2->expects($this->once())
			->method('canHandle')
			->with($this->equalTo($item))
			->will($this->returnValue(true));

		$this->enhancer->registerEnhancer($this->articleEnhancer);
		$this->enhancer->registerEnhancer($this->articleEnhancer2);

		$this->enhancer->enhance($item);
	}


	public function testMultipleFetchersOnlyOneShouldHandle(){
		$item = new Item();
		$item->setUrl('hi');
		$return = 'zeas';
		$this->articleEnhancer->expects($this->once())
			->method('canHandle')
			->with($this->equalTo($item))
			->will($this->returnValue(true));
		$this->articleEnhancer->expects($this->once())
			->method('enhance')
			->with($this->equalTo($item))
			->will($this->returnValue($return));
		$this->articleEnhancer2->expects($this->never())
			->method('canHandle');

		$this->enhancer->registerEnhancer($this->articleEnhancer);
		$this->enhancer->registerEnhancer($this->articleEnhancer2);

		$result = $this->enhancer->enhance($item);

		$this->assertEquals($return, $result);
	}


}