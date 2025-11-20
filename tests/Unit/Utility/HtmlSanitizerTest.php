<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @copyright 2025 Nextcloud GmbH and Nextcloud contributors
 */

namespace OCA\News\Tests\Unit\Utility;

use OCA\News\Utility\HtmlSanitizer;
use PHPUnit\Framework\TestCase;

class HtmlSanitizerTest extends TestCase
{
    private HtmlSanitizer $sanitizer;

    protected function setUp(): void
    {
        $this->sanitizer = new HtmlSanitizer(HtmlSanitizer::createSanitizer());
    }

    public function testPurifyBasicHtml(): void
    {
        $input = '<p>Hello <strong>World</strong></p>';
        $output = $this->sanitizer->purify($input);
        $this->assertStringContainsString('Hello', $output);
        $this->assertStringContainsString('World', $output);
        $this->assertStringContainsString('<p>', $output);
        $this->assertStringContainsString('<strong>', $output);
    }

    public function testPurifyRemovesClassAttribute(): void
    {
        $input = '<p class="test-class">Hello World</p>';
        $output = $this->sanitizer->purify($input);
        $this->assertStringNotContainsString('class=', $output);
        $this->assertStringNotContainsString('test-class', $output);
        $this->assertStringContainsString('Hello World', $output);
    }

    public function testPurifyRemovesScriptTags(): void
    {
        $input = '<p>Safe content</p><script>alert("XSS")</script>';
        $output = $this->sanitizer->purify($input);
        $this->assertStringNotContainsString('script', $output);
        $this->assertStringNotContainsString('alert', $output);
        $this->assertStringContainsString('Safe content', $output);
    }

    public function testPurifyAllowsHttpLinks(): void
    {
        $input = '<a href="http://example.com">Link</a>';
        $output = $this->sanitizer->purify($input);
        $this->assertStringContainsString('href=', $output);
        $this->assertStringContainsString('example.com', $output);
    }

    public function testPurifyAllowsHttpsLinks(): void
    {
        $input = '<a href="https://example.com">Link</a>';
        $output = $this->sanitizer->purify($input);
        $this->assertStringContainsString('href=', $output);
        $this->assertStringContainsString('example.com', $output);
    }

    public function testPurifyAllowsMailtoLinks(): void
    {
        $input = '<a href="mailto:test@example.com">Email</a>';
        $output = $this->sanitizer->purify($input);
        $this->assertStringContainsString('mailto:', $output);
        // The @ symbol may be encoded as &#64; which is valid HTML
        $this->assertMatchesRegularExpression('/(test@example\.com|test&#64;example\.com)/', $output);
    }

    public function testPurifyAllowsTelLinks(): void
    {
        $input = '<a href="tel:+1234567890">Call</a>';
        $output = $this->sanitizer->purify($input);
        $this->assertStringContainsString('tel:', $output);
        $this->assertStringContainsString('1234567890', $output);
    }

    public function testPurifyAllowsFtpLinks(): void
    {
        $input = '<a href="ftp://ftp.example.com">FTP</a>';
        $output = $this->sanitizer->purify($input);
        $this->assertStringContainsString('ftp:', $output);
        $this->assertStringContainsString('ftp.example.com', $output);
    }

    public function testPurifyAllowsDataUriInImages(): void
    {
        $input = '<img src="data:image/png;base64,iVBORw0KGgo=" alt="Test">';
        $output = $this->sanitizer->purify($input);
        $this->assertStringContainsString('data:image/png', $output);
        $this->assertStringContainsString('base64', $output);
    }

    public function testPurifyAllowsYoutubeIframe(): void
    {
        $input = '<iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ" allowfullscreen></iframe>';
        $output = $this->sanitizer->purify($input);
        $this->assertStringContainsString('<iframe', $output);
        $this->assertStringContainsString('youtube.com/embed', $output);
        $this->assertStringContainsString('allowfullscreen', $output);
    }

    public function testPurifyAllowsVimeoIframe(): void
    {
        $input = '<iframe src="https://player.vimeo.com/video/123456" allowfullscreen></iframe>';
        $output = $this->sanitizer->purify($input);
        $this->assertStringContainsString('<iframe', $output);
        $this->assertStringContainsString('vimeo.com/video', $output);
        $this->assertStringContainsString('allowfullscreen', $output);
    }

    public function testPurifyAllowsVkIframe(): void
    {
        $input = '<iframe src="https://vk.com/video_ext.php?id=123"></iframe>';
        $output = $this->sanitizer->purify($input);
        $this->assertStringContainsString('<iframe', $output);
        $this->assertStringContainsString('vk.com', $output);
    }

    public function testPurifyBlocksUntrustedIframe(): void
    {
        $input = '<iframe src="https://evil.com/malware"></iframe>';
        $output = $this->sanitizer->purify($input);
        // Should have iframe element but no src attribute
        $this->assertStringContainsString('<iframe', $output);
        $this->assertStringNotContainsString('evil.com', $output);
    }

    public function testPurifyBlocksHttpIframe(): void
    {
        $input = '<iframe src="http://www.youtube.com/embed/test"></iframe>';
        $output = $this->sanitizer->purify($input);
        // Should have iframe element but no src attribute (http not allowed, only https)
        $this->assertStringContainsString('<iframe', $output);
        $this->assertStringNotContainsString('http:', $output);
    }

    public function testPurifyPreservesCommonTags(): void
    {
        $input = '<div><p>Text</p><ul><li>Item 1</li><li>Item 2</li></ul></div>';
        $output = $this->sanitizer->purify($input);
        $this->assertStringContainsString('<div>', $output);
        $this->assertStringContainsString('<p>', $output);
        $this->assertStringContainsString('<ul>', $output);
        $this->assertStringContainsString('<li>', $output);
        $this->assertStringContainsString('Item 1', $output);
        $this->assertStringContainsString('Item 2', $output);
    }

    public function testPurifyRemovesOnclickAttribute(): void
    {
        $input = '<button onclick="alert(\'XSS\')">Click</button>';
        $output = $this->sanitizer->purify($input);
        $this->assertStringNotContainsString('onclick', $output);
        $this->assertStringNotContainsString('alert', $output);
    }

    public function testPurifyRemovesStyleTag(): void
    {
        $input = '<style>body { background: red; }</style><p>Content</p>';
        $output = $this->sanitizer->purify($input);
        $this->assertStringNotContainsString('<style>', $output);
        $this->assertStringContainsString('Content', $output);
    }

    public function testPurifyHandlesEmptyString(): void
    {
        $input = '';
        $output = $this->sanitizer->purify($input);
        $this->assertEquals('', $output);
    }

    public function testPurifyHandlesComplexNestedStructure(): void
    {
        $input = '<div><article><h1>Title</h1><p>Paragraph with <em>emphasis</em> and <strong>strong</strong> text.</p></article></div>';
        $output = $this->sanitizer->purify($input);
        $this->assertStringContainsString('Title', $output);
        $this->assertStringContainsString('emphasis', $output);
        $this->assertStringContainsString('strong', $output);
        $this->assertStringContainsString('<h1>', $output);
        $this->assertStringContainsString('<em>', $output);
        $this->assertStringContainsString('<strong>', $output);
    }

    public function testPurifyAllowsRelativeLinks(): void
    {
        $input = '<a href="/relative/path">Relative Link</a>';
        $output = $this->sanitizer->purify($input);
        $this->assertStringContainsString('href=', $output);
        $this->assertStringContainsString('/relative/path', $output);
    }

    public function testPurifyAllowsRelativeImages(): void
    {
        $input = '<img src="/images/photo.jpg" alt="Photo">';
        $output = $this->sanitizer->purify($input);
        $this->assertStringContainsString('<img', $output);
        $this->assertStringContainsString('/images/photo.jpg', $output);
        $this->assertStringContainsString('alt', $output);
    }

    public function testPurifyPreservesTableStructure(): void
    {
        $input = '<table><thead><tr><th>Header</th></tr></thead><tbody><tr><td>Data</td></tr></tbody></table>';
        $output = $this->sanitizer->purify($input);
        $this->assertStringContainsString('<table>', $output);
        $this->assertStringContainsString('<thead>', $output);
        $this->assertStringContainsString('<tbody>', $output);
        $this->assertStringContainsString('<tr>', $output);
        $this->assertStringContainsString('<th>', $output);
        $this->assertStringContainsString('<td>', $output);
        $this->assertStringContainsString('Header', $output);
        $this->assertStringContainsString('Data', $output);
    }

    public function testPurifyHandlesBlockquote(): void
    {
        $input = '<blockquote><p>Quote text</p></blockquote>';
        $output = $this->sanitizer->purify($input);
        $this->assertStringContainsString('<blockquote>', $output);
        $this->assertStringContainsString('Quote text', $output);
    }

    public function testPurifyHandlesCode(): void
    {
        $input = '<p>This is <code>inline code</code></p><pre><code>block code</code></pre>';
        $output = $this->sanitizer->purify($input);
        $this->assertStringContainsString('<code>', $output);
        $this->assertStringContainsString('inline code', $output);
        $this->assertStringContainsString('<pre>', $output);
        $this->assertStringContainsString('block code', $output);
    }
}
