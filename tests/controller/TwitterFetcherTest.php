<?php

/**
* ownCloud - News
*
* @author Alessandro Cosentino
* @author Bernhard Posselt
* @copyright 2012 Alessandro Cosentino cosenal@gmail.com
* @copyright 2012 Bernhard Posselt nukeawhale@gmail.com
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

require_once(__DIR__ . "/../classloader.php");


class TwitterFetcherTest extends \OCA\AppFramework\Utility\TestUtility {

	private $fetcher;
	private $twitter;

	protected function setUp(){
		$this->fetcher = $this->getMockBuilder('\OCA\News\Utility\FeedFetcher')
			->disableOriginalConstructor()
			->getMock();
		$this->twitter = new TwitterFetcher($this->fetcher);
	}


	public function testCanHandle(){
		$urls = array(
			'https://twitter.com/GeorgeTakei',
			'https://www.twitter.com/GeorgeTakei',
			'http://twitter.com/GeorgeTakei',
			'http://www.twitter.com/GeorgeTakei',
			'www.twitter.com/GeorgeTakei',
			'twitter.com/GeorgeTakei'
		);
		foreach($urls as $url){
			$this->assertTrue($this->twitter->canHandle($url));
		}
	}


	public function testCanHandleDoesNotUseApiUrls(){
		$url = 'https://api.twitter.com/1/statuses/user_timeline.rss?screen_name=GeorgeTakei';
		$this->assertFalse($this->twitter->canHandle($url));
	}


	public function testFetch(){
		$inUrl = 'https://www.twitter.com/GeorgeTakei';
		$outUrl = 'https://api.twitter.com/1/statuses/user_timeline.rss?screen_name=GeorgeTakei';
		$out = 'hi';
		$this->fetcher->expects($this->once())
			->method('fetch')
			->with($this->equalTo($outUrl))
			->will($this->returnValue($out));

		$return = $this->twitter->fetch($inUrl);
		$this->assertEquals($out, $return);
	}
}