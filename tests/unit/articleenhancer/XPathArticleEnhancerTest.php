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
    private $fileFactory;
    private $timeout;
    private $redirects;
    private $headers;
    private $userAgent;
    private $proxyHost;
    private $proxyPort;
    private $proxyAuth;

    protected function setUp() {
        $this->timeout = 30;
        $this->fileFactory = $this
            ->getMockBuilder('\OCA\News\Utility\SimplePieAPIFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->proxyHost = 'test';
        $this->proxyPort = 3;
        $this->proxyAuth = 'hi';
        $this->config = $this->getMockBuilder(
            '\OCA\News\Config\Config')
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
        $this->config->expects($this->any())
            ->method('getFeedFetcherTimeout')
            ->will($this->returnValue($this->timeout));

        $this->testEnhancer = new XPathArticleEnhancer(
            $this->fileFactory,
            [
                '/explosm.net\/comics/' =>
                    '//*[@id=\'maincontent\']/div[2]/div/span',
                '/explosm.net\/shorts/' => '//*[@id=\'maincontent\']/div/div',
                '/explosm.net\/all/' => '//body/*',
                '/themerepublic.net/' => '//*[@class=\'post hentry\']'
            ],
            $this->config
        );
        $this->redirects = 5;
        $this->headers = null;
        $this->userAgent = 'Mozilla/5.0 AppleWebKit';
    }


    public function testXPathUsesNoProxy() {
        $file = new \stdClass;
        $file->headers = ["content-type"=>"text/html; charset=utf-8"];
        $file->body = '';
        $item = new Item();
        $item->setUrl('https://www.explosm.net/comics/312');
        $item->setBody('Hello thar');

        $this->fileFactory->expects($this->once())
            ->method('getFile')
            ->with($this->equalTo($item->getUrl()),
                $this->equalTo($this->timeout),
                $this->equalTo($this->redirects),
                $this->equalTo($this->headers),
                $this->equalTo($this->userAgent),
                $this->equalTo(false))
            ->will($this->returnValue($file));

        $result = $this->testEnhancer->enhance($item);
        $this->assertEquals('Hello thar', $result->getBody());
    }


    public function testDoesNotModifiyNotMatchingResults() {
        $item = new Item();
        $item->setUrl('http://explosm.net');
        $this->assertEquals($item, $this->testEnhancer->enhance($item));
    }


    public function testDoesModifiyArticlesThatMatch() {
        $file = new \stdClass;
        $file->headers = ["content-type"=>"text/html; charset=utf-8"];
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
                $this->equalTo($this->timeout),
                $this->equalTo($this->redirects),
                $this->equalTo($this->headers),
                $this->equalTo($this->userAgent))
            ->will($this->returnValue($file));

        $result = $this->testEnhancer->enhance($item);
        $this->assertEquals('<div><span>hiho</span></div>', $result->getBody());
    }


    public function testDoesModifiyAllArticlesThatMatch() {
        $file = new \stdClass;
        $file->headers = ["content-type"=>"text/html; charset=utf-8"];
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
                $this->equalTo($this->timeout),
                $this->equalTo($this->redirects),
                $this->equalTo($this->headers),
                $this->equalTo($this->userAgent))
            ->will($this->returnValue($file));

        $result = $this->testEnhancer->enhance($item);
        $this->assertEquals('<div><div>hiho</div><div>rawr</div></div>',
            $result->getBody());
    }


    public function testModificationHandlesEmptyResults() {
        $file = new \stdClass;
        $file->headers = ["content-type"=>"text/html; charset=utf-8"];
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
                $this->equalTo($this->timeout),
                $this->equalTo($this->redirects),
                $this->equalTo($this->headers),
                $this->equalTo($this->userAgent))
            ->will($this->returnValue($file));

        $result = $this->testEnhancer->enhance($item);
        $this->assertEquals('Hello thar', $result->getBody());
    }


    public function testModificationDoesNotBreakOnEmptyDom() {
        $file = new \stdClass;
        $file->headers = ["content-type"=>"text/html; charset=utf-8"];
        $file->body = '';
        $item = new Item();
        $item->setUrl('https://www.explosm.net/comics/312');
        $item->setBody('Hello thar');

        $this->fileFactory->expects($this->once())
            ->method('getFile')
            ->with($this->equalTo($item->getUrl()),
                $this->equalTo($this->timeout),
                $this->equalTo($this->redirects),
                $this->equalTo($this->headers),
                $this->equalTo($this->userAgent))
            ->will($this->returnValue($file));

        $result = $this->testEnhancer->enhance($item);
        $this->assertEquals('Hello thar', $result->getBody());
    }


    public function testModificationDoesNotBreakOnBrokenDom() {
        $file = new \stdClass;
        $file->headers = ["content-type"=>"text/html; charset=utf-8"];
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
                $this->equalTo($this->timeout),
                $this->equalTo($this->redirects),
                $this->equalTo($this->headers),
                $this->equalTo($this->userAgent))
            ->will($this->returnValue($file));

        $result = $this->testEnhancer->enhance($item);
        $this->assertEquals('Hello thar', $result->getBody());
    }


    public function testTransformRelativeUrls() {
        $file = new \stdClass;
        $file->headers = ["content-type"=>"text/html; charset=utf-8"];
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
                $this->equalTo($this->timeout),
                $this->equalTo($this->redirects),
                $this->equalTo($this->headers),
                $this->equalTo($this->userAgent))
            ->will($this->returnValue($file));

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
        $file = new \stdClass;
        $file->headers = ["content-type"=>"text/html; charset=utf-8"];
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
                $this->equalTo($this->timeout),
                $this->equalTo($this->redirects),
                $this->equalTo($this->headers),
                $this->equalTo($this->userAgent))
            ->will($this->returnValue($file));

        $result = $this->testEnhancer->enhance($item);
        $this->assertEquals(
            '<div><img src="' .
            'https://username:secret@www.explosm.net' .
            '/all/relative/url.png?a=1&amp;b=2"></div>',
            $result->getBody());
    }

    public function testDontTransformAbsoluteUrlsAndMails() {
        $file = new \stdClass;
        $file->headers = ["content-type"=>"text/html; charset=utf-8"];
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
                $this->equalTo($this->timeout),
                $this->equalTo($this->redirects),
                $this->equalTo($this->headers),
                $this->equalTo($this->userAgent))
            ->will($this->returnValue($file));

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
