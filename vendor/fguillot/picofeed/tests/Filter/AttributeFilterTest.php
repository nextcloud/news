<?php
namespace PicoFeed\Filter;

use PHPUnit_Framework_TestCase;

use PicoFeed\Client\Url;
use PicoFeed\Config\Config;


class AttributeFilterTest extends PHPUnit_Framework_TestCase
{
    public function testFilterEmptyAttribute()
    {
        $filter = new Attribute(new Url('http://google.com'));

        $this->assertTrue($filter->filterEmptyAttribute('abbr', 'title', 'test'));
        $this->assertFalse($filter->filterEmptyAttribute('abbr', 'title', ''));
        $this->assertEquals(array('title' => 'test'), $filter->filter('abbr', array('title' => 'test')));
        $this->assertEquals(array(), $filter->filter('abbr', array('title' => '')));
    }

    public function testFilterAllowedAttribute()
    {
        $filter = new Attribute(new Url('http://google.com'));

        $this->assertTrue($filter->filterAllowedAttribute('abbr', 'title', 'test'));
        $this->assertFalse($filter->filterAllowedAttribute('script', 'type', 'text/javascript'));

        $this->assertEquals(array(), $filter->filter('script', array('type' => 'text/javascript')));
        $this->assertEquals(array(), $filter->filter('a', array('onclick' => 'javascript')));
        $this->assertEquals(array('href' => 'http://google.com/'), $filter->filter('a', array('href' => 'http://google.com')));
    }

    public function testFilterIntegerAttribute()
    {
        $filter = new Attribute(new Url('http://google.com'));

        $this->assertTrue($filter->filterIntegerAttribute('abbr', 'title', 'test'));
        $this->assertTrue($filter->filterIntegerAttribute('iframe', 'width', '0'));
        $this->assertTrue($filter->filterIntegerAttribute('iframe', 'width', '450'));
        $this->assertFalse($filter->filterIntegerAttribute('iframe', 'width', 'test'));

        $this->assertEquals(array('width' => '10', 'src' => 'https://www.youtube.com/test'), $filter->filter('iframe', array('width' => '10', 'src' => 'http://www.youtube.com/test')));
        $this->assertEquals(array('src' => 'https://www.youtube.com/test'), $filter->filter('iframe', array('width' => 'test', 'src' => 'http://www.youtube.com/test')));
    }

    public function testRewriteProxyImageUrl()
    {
        $filter = new Attribute(new Url('http://www.la-grange.net'));
        $url = '/2014/08/03/4668-noisettes';
        $this->assertTrue($filter->rewriteImageProxyUrl('a', 'href', $url));
        $this->assertEquals('/2014/08/03/4668-noisettes', $url);

        $filter = new Attribute(new Url('http://www.la-grange.net'));
        $url = '/2014/08/03/4668-noisettes';
        $this->assertTrue($filter->rewriteImageProxyUrl('img', 'alt', $url));
        $this->assertEquals('/2014/08/03/4668-noisettes', $url);

        $filter = new Attribute(new Url('http://www.la-grange.net'));
        $url = '/2014/08/03/4668-noisettes';
        $this->assertTrue($filter->rewriteImageProxyUrl('img', 'src', $url));
        $this->assertEquals('/2014/08/03/4668-noisettes', $url);

        $filter = new Attribute(new Url('http://www.la-grange.net'));
        $filter->setImageProxyUrl('https://myproxy/?u=%s');
        $url = 'http://example.net/image.png';
        $this->assertTrue($filter->rewriteImageProxyUrl('img', 'src', $url));
        $this->assertEquals('https://myproxy/?u='.rawurlencode('http://example.net/image.png'), $url);

        $filter = new Attribute(new Url('http://www.la-grange.net'));

        $filter->setImageProxyCallback(function ($image_url) {
            $key = hash_hmac('sha1', $image_url, 'secret');
            return 'https://mypublicproxy/'.$key.'/'.rawurlencode($image_url);
        });

        $url = 'http://example.net/image.png';
        $this->assertTrue($filter->rewriteImageProxyUrl('img', 'src', $url));
        $this->assertEquals('https://mypublicproxy/d9701029b054f6e178ef88fcd3c789365e52a26d/'.rawurlencode('http://example.net/image.png'), $url);
    }

    public function testRewriteAbsoluteUrl()
    {
        $filter = new Attribute(new Url('http://www.la-grange.net'));
        $url = '/2014/08/03/4668-noisettes';
        $this->assertTrue($filter->rewriteAbsoluteUrl('a', 'href', $url));
        $this->assertEquals('http://www.la-grange.net/2014/08/03/4668-noisettes', $url);

        $filter = new Attribute(new Url('http://google.com'));

        $url = 'test';
        $this->assertTrue($filter->rewriteAbsoluteUrl('a', 'href', $url));
        $this->assertEquals('http://google.com/test', $url);

        $url = 'http://127.0.0.1:8000/test';
        $this->assertTrue($filter->rewriteAbsoluteUrl('img', 'src', $url));
        $this->assertEquals('http://127.0.0.1:8000/test', $url);

        $url = '//example.com';
        $this->assertTrue($filter->rewriteAbsoluteUrl('a', 'href', $url));
        $this->assertEquals('http://example.com/', $url);

        $filter = new Attribute(new Url('https://google.com'));
        $url = '//example.com/?youpi';
        $this->assertTrue($filter->rewriteAbsoluteUrl('a', 'href', $url));
        $this->assertEquals('https://example.com/?youpi', $url);

        $filter = new Attribute(new Url('https://127.0.0.1:8000/here/'));
        $url = 'image.png?v=2';
        $this->assertTrue($filter->rewriteAbsoluteUrl('a', 'href', $url));
        $this->assertEquals('https://127.0.0.1:8000/here/image.png?v=2', $url);

        $filter = new Attribute(new Url('https://truc/'));
        $this->assertEquals(array('src' => 'https://www.youtube.com/test'), $filter->filter('iframe', array('width' => 'test', 'src' => '//www.youtube.com/test')));

        $filter = new Attribute(new Url('http://truc/'));
        $this->assertEquals(array('href' => 'http://google.fr/'), $filter->filter('a', array('href' => '//google.fr')));
    }

    public function testFilterIframeAttribute()
    {
        $filter = new Attribute(new Url('http://google.com'));

        $this->assertTrue($filter->filterIframeAttribute('iframe', 'src', 'http://www.youtube.com/test'));
        $this->assertTrue($filter->filterIframeAttribute('iframe', 'src', 'https://www.youtube.com/test'));
        $this->assertFalse($filter->filterIframeAttribute('iframe', 'src', '//www.youtube.com/test'));
        $this->assertFalse($filter->filterIframeAttribute('iframe', 'src', '//www.bidule.com/test'));

        $this->assertEquals(array('src' => 'https://www.youtube.com/test'), $filter->filter('iframe', array('src' => '//www.youtube.com/test')));
    }

    public function testRemoveYouTubeAutoplay()
    {
        $filter = new Attribute(new Url('http://google.com'));
        $urls = array(
            'https://www.youtube.com/something/?autoplay=1' => 'https://www.youtube.com/something/?autoplay=0',
            'https://www.youtube.com/something/?test=s&autoplay=1&a=2' => 'https://www.youtube.com/something/?test=s&autoplay=0&a=2',
            'https://www.youtube.com/something/?test=s' => 'https://www.youtube.com/something/?test=s',
            'https://youtube.com/something/?autoplay=1' => 'https://youtube.com/something/?autoplay=0',
            'https://youtube.com/something/?test=s&autoplay=1&a=2' => 'https://youtube.com/something/?test=s&autoplay=0&a=2',
            'https://youtube.com/something/?test=s' => 'https://youtube.com/something/?test=s',
        );

        foreach ($urls as $before => $after) {
            $filter->removeYouTubeAutoplay('iframe', 'src', $before);
            $this->assertEquals($after, $before);
        }
    }

    public function testFilterBlacklistAttribute()
    {
        $filter = new Attribute(new Url('http://google.com'));

        $this->assertTrue($filter->filterBlacklistResourceAttribute('a', 'href', 'http://google.fr/'));
        $this->assertFalse($filter->filterBlacklistResourceAttribute('a', 'href', 'http://res3.feedsportal.com/truc'));

        $this->assertEquals(array('href' => 'http://google.fr/'), $filter->filter('a', array('href' => 'http://google.fr/')));
        $this->assertEquals(array(), $filter->filter('a', array('href' => 'http://res3.feedsportal.com/')));
    }

    public function testFilterProtocolAttribute()
    {
        $filter = new Attribute(new Url('http://google.com'));

        $this->assertTrue($filter->filterProtocolUrlAttribute('a', 'href', 'http://google.fr/'));
        $this->assertFalse($filter->filterProtocolUrlAttribute('a', 'href', 'bla://google.fr/'));
        $this->assertFalse($filter->filterProtocolUrlAttribute('a', 'href', 'javascript:alert("test")'));

        $this->assertEquals(array('href' => 'http://google.fr/'), $filter->filter('a', array('href' => 'http://google.fr/')));
        $this->assertEquals(array(), $filter->filter('a', array('href' => 'bla://google.fr/')));
    }

    public function testRequiredAttribute()
    {
        $filter = new Attribute(new Url('http://google.com'));

        $this->assertTrue($filter->hasRequiredAttributes('a', array('href' => 'bla')));
        $this->assertTrue($filter->hasRequiredAttributes('img', array('src' => 'bla')));
        $this->assertTrue($filter->hasRequiredAttributes('source', array('src' => 'bla')));
        $this->assertTrue($filter->hasRequiredAttributes('audio', array('src' => 'bla')));
        $this->assertTrue($filter->hasRequiredAttributes('iframe', array('src' => 'bla')));
        $this->assertTrue($filter->hasRequiredAttributes('p', array('class' => 'bla')));
        $this->assertFalse($filter->hasRequiredAttributes('a', array('title' => 'bla')));
    }

    public function testHtml()
    {
        $filter = new Attribute(new Url('http://google.com'));

        $this->assertEquals('title="A &amp; B"', $filter->toHtml(array('title' => 'A & B')));
        $this->assertEquals('title="&quot;a&quot;"', $filter->toHtml(array('title' => '"a"')));
        $this->assertEquals('title="ç" alt="b"', $filter->toHtml(array('title' => 'ç', 'alt' => 'b')));
    }

    public function testNoImageProxySet()
    {
        $f = Filter::html('<p>Image <img src="/image.png" alt="My Image"/></p>', 'http://foo');

        $this->assertEquals(
            '<p>Image <img src="http://foo/image.png" alt="My Image"/></p>',
            $f->execute()
        );
    }

    public function testImageProxyWithHTTPLink()
    {
        $config = new Config;
        $config->setFilterImageProxyUrl('http://myproxy/?url=%s');

        $f = Filter::html('<p>Image <img src="http://localhost/image.png" alt="My Image"/></p>', 'http://foo');
        $f->setConfig($config);

        $this->assertEquals(
            '<p>Image <img src="http://myproxy/?url='.rawurlencode('http://localhost/image.png').'" alt="My Image"/></p>',
            $f->execute()
        );
    }

    public function testImageProxyWithHTTPSLink()
    {
        $config = new Config;
        $config->setFilterImageProxyUrl('http://myproxy/?url=%s');

        $f = Filter::html('<p>Image <img src="https://localhost/image.png" alt="My Image"/></p>', 'http://foo');
        $f->setConfig($config);

        $this->assertEquals(
            '<p>Image <img src="http://myproxy/?url='.rawurlencode('https://localhost/image.png').'" alt="My Image"/></p>',
            $f->execute()
        );
    }

    public function testImageProxyLimitedToUnknownProtocol()
    {
        $config = new Config;
        $config->setFilterImageProxyUrl('http://myproxy/?url=%s');
        $config->setFilterImageProxyProtocol('tripleX');

        $f = Filter::html('<p>Image <img src="http://localhost/image.png" alt="My Image"/></p>', 'http://foo');
        $f->setConfig($config);

        $this->assertEquals(
            '<p>Image <img src="http://localhost/image.png" alt="My Image"/></p>',
            $f->execute()
        );
    }

    public function testImageProxyLimitedToHTTPwithHTTPLink()
    {
        $config = new Config;
        $config->setFilterImageProxyUrl('http://myproxy/?url=%s');
        $config->setFilterImageProxyProtocol('http');

        $f = Filter::html('<p>Image <img src="http://localhost/image.png" alt="My Image"/></p>', 'http://foo');
        $f->setConfig($config);

        $this->assertEquals(
            '<p>Image <img src="http://myproxy/?url='.rawurlencode('http://localhost/image.png').'" alt="My Image"/></p>',
            $f->execute()
        );
    }

    public function testImageProxyLimitedToHTTPwithHTTPSLink()
    {
        $config = new Config;
        $config->setFilterImageProxyUrl('http://myproxy/?url=%s');
        $config->setFilterImageProxyProtocol('http');

        $f = Filter::html('<p>Image <img src="https://localhost/image.png" alt="My Image"/></p>', 'http://foo');
        $f->setConfig($config);

        $this->assertEquals(
            '<p>Image <img src="https://localhost/image.png" alt="My Image"/></p>',
            $f->execute()
        );
    }

    public function testImageProxyLimitedToHTTPSwithHTTPLink()
    {
        $config = new Config;
        $config->setFilterImageProxyUrl('http://myproxy/?url=%s');
        $config->setFilterImageProxyProtocol('https');

        $f = Filter::html('<p>Image <img src="http://localhost/image.png" alt="My Image"/></p>', 'http://foo');
        $f->setConfig($config);

        $this->assertEquals(
            '<p>Image <img src="http://localhost/image.png" alt="My Image"/></p>',
            $f->execute()
        );
    }

    public function testImageProxyLimitedToHTTPSwithHTTPSLink()
    {
        $config = new Config;
        $config->setFilterImageProxyUrl('http://myproxy/?url=%s');
        $config->setFilterImageProxyProtocol('https');

        $f = Filter::html('<p>Image <img src="https://localhost/image.png" alt="My Image"/></p>', 'http://foo');
        $f->setConfig($config);

        $this->assertEquals(
            '<p>Image <img src="http://myproxy/?url='.rawurlencode('https://localhost/image.png').'" alt="My Image"/></p>',
            $f->execute()
        );
    }

    public function testsetFilterImageProxyCallback()
    {
        $config = new Config;
        $config->setFilterImageProxyCallback(function ($image_url) {
            $key = hash_hmac('sha1', $image_url, 'secret');
            return 'https://mypublicproxy/'.$key.'/'.rawurlencode($image_url);
        });

        $f = Filter::html('<p>Image <img src="/image.png" alt="My Image"/></p>', 'http://foo');
        $f->setConfig($config);

        $this->assertEquals(
            '<p>Image <img src="https://mypublicproxy/4924964043f3119b3cf2b07b1922d491bcc20092/'.rawurlencode('http://foo/image.png').'" alt="My Image"/></p>',
            $f->execute()
        );
    }
}
