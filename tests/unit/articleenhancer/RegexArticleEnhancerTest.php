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


class RegexArticleEnhancerTest extends \PHPUnit_Framework_TestCase {


	public function testRegexEnhancer() {
		$item = new Item();
		$item->setBody('atests is a nice thing');
		$item->setUrl('http://john.com');
		$regex = ["%tes(ts)%" => "heho$1tests"];
		
		$regexEnhancer = new RegexArticleEnhancer('%john.com%', $regex);
		$item = $regexEnhancer->enhance($item);

		$this->assertEquals('ahehotstests is a nice thing', $item->getBody());
	}


}