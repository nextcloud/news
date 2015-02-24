<?php

namespace PicoFeed\Filter;

use PHPUnit_Framework_TestCase;

class HtmlFilterTest extends PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $html = '<!DOCTYPE html><html><head>
                <meta name="title" content="test">
                </head><body><p>boo<br/><strong>foo</strong>.</p></body></html>';

        $filter = new Html($html, 'http://www.google.ca/');

        $this->assertEquals('<p>boo<br/><strong>foo</strong>.</p>', $filter->execute());
    }

    public function testIframe()
    {
        $data = '<iframe src="http://www.kickstarter.com/projects/lefnire/habitrpg-mobile/widget/video.html" height="480" width="640" frameborder="0"></iframe>';

        $f = new Html($data, 'http://blabla');
        $this->assertEmpty($f->execute());

        $data = '<iframe src="http://www.youtube.com/bla" height="480" width="640" frameborder="0"></iframe>';
        $expected = '<iframe src="https://www.youtube.com/bla" height="480" width="640" frameborder="0"></iframe>';

        $f = new Html($data, 'http://blabla');
        $this->assertEquals($expected, $f->execute());
    }

    public function testClearScriptAttributes()
    {
        $data = '<div><script>this is the content</script><script>blubb content</script><p>something</p></div><p>hi</p>';

        $f = new Html($data, 'http://blabla');
        $expected = '<p>something</p><p>hi</p>';
        $this->assertEquals($expected, $f->execute());
    }

    public function testClearStyleAttributes()
    {
        $data = '<div><style>this is the content</style><style>blubb content</style><p>something</p></div><p>hi</p>';

        $f = new Html($data, 'http://blabla');
        $expected = '<p>something</p><p>hi</p>';
        $this->assertEquals($expected, $f->execute());
    }

    public function testEmptyTags()
    {
        $data = <<<EOD
<div>
<a href="file://path/to/a/file">
    <img src="http://example.com/image.png" />
</a>
</div>
EOD;
        $f = new Html($data, 'http://blabla');
        $output = $f->execute();

        $this->assertEquals('<img src="http://example.com/image.png"/>', $output);
    }

    public function testBadAttributes()
    {
        $data = '<iframe src="http://www.youtube.com/bla" height="480px" width="100%" frameborder="0"></iframe>';

        $f = new Html($data, 'http://blabla');
        $this->assertEquals('<iframe src="https://www.youtube.com/bla" frameborder="0"></iframe>', $f->execute());
    }

    public function testRelativeScheme()
    {
        $f = new Html('<a href="//linuxfr.org">link</a>', 'http://blabla');
        $this->assertEquals('<a href="http://linuxfr.org/" rel="noreferrer" target="_blank">link</a>', $f->execute());
    }

    public function testAttributes()
    {
        $f = new Html('<img src="foo" title="\'quote" alt="\'quote" data-src="bar" data-truc="boo"/>', 'http://blabla');
        $this->assertEquals('<img src="http://blabla/foo" title="&#039;quote" alt="&#039;quote"/>', $f->execute());

        $f = new Html('<img src="foo&bar=\'quote"/>', 'http://blabla');
        $this->assertEquals('<img src="http://blabla/foo&amp;bar=&#039;quote"/>', $f->execute());

        $f = new Html("<time datetime='quote\"here'>bla</time>", 'http://blabla');
        $this->assertEquals('<time datetime="quote&quot;here">bla</time>', $f->execute());
    }

    public function testCode()
    {
        $data = '<pre><code>HEAD / HTTP/1.1
Accept: text/html
Accept-Encoding: gzip, deflate, compress
Host: www.amazon.com
User-Agent: HTTPie/0.6.0



<strong>HTTP/1.1 405 MethodNotAllowed</strong>
Content-Encoding: gzip
Content-Type: text/html; charset=ISO-8859-1
Date: Mon, 15 Jul 2013 02:05:59 GMT
Server: Server
Set-Cookie: skin=noskin; path=/; domain=.amazon.com; expires=Mon, 15-Jul-2013 02:05:59 GMT
Vary: Accept-Encoding,User-Agent
<strong>allow: POST, GET</strong>
x-amz-id-1: 11WD3K15FC268R5GBJY5
x-amz-id-2: DDjqfqz2ZJufzqRAcj1mh+9XvSogrPohKHwXlo8IlkzH67G6w4wnjn9HYgbs4uI0
</code></pre>';

        $f = new Html($data, 'http://blabla');
        $this->assertEquals($data, $f->execute());
    }

    public function testRemoveNoBreakingSpace()
    {
        $f = new Html('<p>&nbsp;&nbsp;truc</p>', 'http://blabla');
        $this->assertEquals('<p>  truc</p>', $f->execute());
    }

    public function testRemoveEmptyTags()
    {
        $f = new Html('<p>toto</p><p></p><br/>', 'http://blabla');
        $this->assertEquals('<p>toto</p><br/>', $f->execute());

        $f = new Html('<p> </p>', 'http://blabla');
        $this->assertEquals('', $f->execute());

        $f = new Html('<p>&nbsp;</p>', 'http://blabla');
        $this->assertEquals('', $f->execute());
    }

    public function testRemoveEmptyTable()
    {
        $f = new Html('<table><tr><td>    </td></tr></table>', 'http://blabla');
        $this->assertEquals('', $f->execute());

        $f = new Html('<table><tr></tr></table>', 'http://blabla');
        $this->assertEquals('', $f->execute());
    }
}
