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


class GlobalArticleEnhancerTest extends \PHPUnit_Framework_TestCase {

    private $enhancer;

    protected function setUp() {
        $this->enhancer = new GlobalArticleEnhancer();
    }


    public function testNoReplaceYoutubeAutoplay() {
        $body = '<iframe width="728" height="410" ' .
            'src="//www.youtube.com/embed/autoplay=1/AWE6UpXQoGU" ' .
            'frameborder="0" allowfullscreen=""></iframe>';
        $expected = '<div><iframe width="728" height="410" ' .
            'src="//www.youtube.com/embed/autoplay=1/AWE6UpXQoGU" ' .
            'frameborder="0" allowfullscreen=""></iframe></div>';
        $item = new Item();
        $item->setBody($body);

        $result = $this->enhancer->enhance($item);
        $this->assertEquals($expected, $result->getBody());
    }


    public function testReplaceYoutubeAutoplay() {
        $body = 'test <iframe width="728" height="410" ' .
            'src="//www.youtube.com/embed' .
            '/AWE6UpXQoGU?tst=1&autoplay=1&abc=1" frameborder="0" ' .
            'allowfullscreen=""></iframe>';
        $expected = '<div>test <iframe width="728" height="410" ' .
            'src="//www.youtube.com/embed' .
            '/AWE6UpXQoGU?tst=1&amp;autoplay=0&amp;abc=1" frameborder="0" ' .
            'allowfullscreen=""></iframe></div>';
        $item = new Item();
        $item->setBody($body);

        $result = $this->enhancer->enhance($item);
        $this->assertEquals($expected, $result->getBody());
    }


    public function testMultipleParagraphs() {
        $body = '<p>paragraph 1</p><p>paragraph 2</p>';
        $expected = '<div>' . $body . '</div>';
        $item = new Item();
        $item->setBody($body);

        $result = $this->enhancer->enhance($item);
        $this->assertEquals($expected, $result->getBody());
    }


    public function testMultipleParagraphsInDiv() {
        $body = '<p>paragraph 1</p><p>paragraph 2</p>';
        $expected = '<div>' . $body . '</div>';
        $item = new Item();
        $item->setBody($body);

        $result = $this->enhancer->enhance($item);
        $this->assertEquals($expected, $result->getBody());
    }

}
