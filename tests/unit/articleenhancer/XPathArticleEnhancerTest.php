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


class XPathArticleEnhancerTest extends \PHPUnit_Framework_TestCase {

    private $testEnhancer;
    private $client;
    private $clientFactory;

    protected function setUp() {
        $this->timeout = 30;
        $this->clientFactory = $this
            ->getMockBuilder('\OCA\News\Utility\PicoFeedClientFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->client = $this
            ->getMockBuilder('\PicoFeed\Client\Client')
            ->disableOriginalConstructor()
            ->getMock();

        $this->testEnhancer = new XPathArticleEnhancer(
            $this->clientFactory,
            [
                '/explosm.net\/comics/' =>
                    '//*[@id=\'maincontent\']/div[2]/div/span',
                '/explosm.net\/shorts/' => '//*[@id=\'maincontent\']/div/div',
                '/explosm.net\/all/' => '//body/*',
                '/themerepublic.net/' => '//*[@class=\'post hentry\']'
            ]
        );
        $this->userAgent = 'Mozilla/5.0 AppleWebKit';
    }

    private function setUpFile($body, $encoding, $url) {
        $this->clientFactory->expects($this->once())
            ->method('build')
            ->will($this->returnValue($this->client));
        $this->client->expects($this->once())
            ->method('execute')
            ->with($this->equalTo($url));
        $this->client->expects($this->once())
            ->method('setUserAgent')
            ->with($this->equalTo($this->userAgent));
        $this->client->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue($body));
        $this->client->expects($this->once())
            ->method('getEncoding')
            ->will($this->returnValue($encoding));
    }


    public function testDoesNotModifiyNotMatchingResults() {
        $item = new Item();
        $item->setUrl('http://explosm.net');
        $this->assertEquals($item, $this->testEnhancer->enhance($item));
    }


    public function testDoesModifiyArticlesThatMatch() {
        $encoding = 'utf-8';
        $body = '<html>
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

        $this->setUpFile($body, $encoding, $item->getUrl());

        $result = $this->testEnhancer->enhance($item);
        $this->assertEquals('<div><span>hiho</span></div>', $result->getBody());
    }


    public function testDoesModifiyAllArticlesThatMatch() {
        $encoding = 'utf-8';
        $body = '<html>
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

        $this->setUpFile($body, $encoding, $item->getUrl());

        $result = $this->testEnhancer->enhance($item);
        $this->assertEquals('<div><div>hiho</div><div>rawr</div></div>',
            $result->getBody());
    }


    public function testModificationHandlesEmptyResults() {
        $encoding = 'utf-8';
        $body = '<html>
            <body>
                <div id="maincontent">
                </div>
            </body>
        </html>';
        $item = new Item();
        $item->setUrl('https://www.explosm.net/comics/312');
        $item->setBody('Hello thar');

        $this->setUpFile($body, $encoding, $item->getUrl());

        $result = $this->testEnhancer->enhance($item);
        $this->assertEquals('Hello thar', $result->getBody());
    }


    public function testModificationDoesNotBreakOnEmptyDom() {
        $encoding = 'utf-8';
        $body = '';
        $item = new Item();
        $item->setUrl('https://www.explosm.net/comics/312');
        $item->setBody('Hello thar');

        $this->setUpFile($body, $encoding, $item->getUrl());

        $result = $this->testEnhancer->enhance($item);
        $this->assertEquals('Hello thar', $result->getBody());
    }


    public function testModificationDoesNotBreakOnBrokenDom() {
        $encoding = 'utf-8';
        $body = '<html/><p>
            <body>
                <div id="maincontent">
                </div>
            </body>
        </html>';
        $item = new Item();
        $item->setUrl('https://www.explosm.net/comics/312');
        $item->setBody('Hello thar');

        $this->setUpFile($body, $encoding, $item->getUrl());

        $result = $this->testEnhancer->enhance($item);
        $this->assertEquals('Hello thar', $result->getBody());
    }


    public function testTransformRelativeUrls() {
        $encoding = 'utf-8';
        $body = '<html>
            <body>
                <a href="../a/relative/url.html?a=1#b">link</a>
                <a href="b/relative/url.html">link2</a>
                <img src="/another/relative/link.jpg"></img>
            </body>
        </html>';
        $item = new Item();
        $item->setUrl('https://www.explosm.net/all/312');
        $item->setBody('Hello thar');

        $this->setUpFile($body, $encoding, $item->getUrl());

        $result = $this->testEnhancer->enhance($item);
        $this->assertEquals('<div>' .
            '<a target="_blank" ' .
                'href="https://www.explosm.net/a/relative/url.html?a=1#b">' .
                'link</a>' .
            '<a target="_blank" ' .
                'href="https://www.explosm.net/all/b/relative/url.html">' .
                'link2</a>' .
            '<img src="https://www.explosm.net/another/relative/link.jpg">' .
            '</div>', $result->getBody());
    }

    public function testTransformRelativeUrlSpecials() {
        $encoding = 'utf-8';
        $body = '<html>
            <body>
                <img src="relative/url.png?a=1&b=2">
            </body>
        </html>';
        $item = new Item();
        $item->setUrl('https://username:secret@www.explosm.net/all/312');
        $item->setBody('Hello thar');

        $this->setUpFile($body, $encoding, $item->getUrl());

        $result = $this->testEnhancer->enhance($item);
        $this->assertEquals(
            '<div><img src="' .
            'https://username:secret@www.explosm.net' .
            '/all/relative/url.png?a=1&amp;b=2"></div>',
            $result->getBody());
    }

    public function testDontTransformAbsoluteUrlsAndMails() {
        $encoding = 'utf-8';
        $body = '<html>
            <body>
                <img src="http://www.url.com/absolute/url.png">
                <a href="mailto:test@testsite.com">mail</a>
            </body>
        </html>';
        $item = new Item();
        $item->setUrl('https://www.explosm.net/all/312');
        $item->setBody('Hello thar');

        $this->setUpFile($body, $encoding, $item->getUrl());

        $result = $this->testEnhancer->enhance($item);
        $this->assertEquals(
            '<div>' .
            '<img src="http://www.url.com/absolute/url.png">' .
            '<a target="_blank" href="mailto:test@testsite.com">mail</a>' .
            '</div>',
            $result->getBody()
        );
    }

}
