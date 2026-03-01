<?php

namespace OCA\News\Tests\Unit\Utility;

use OCA\News\Utility\OPMLImporter;

use PHPUnit\Framework\TestCase;

class OPMLImporterTest extends TestCase
{
    private OPMLImporter $importer;

    protected function setUp(): void
    {
        $this->importer = new OPMLImporter();
    }

    public function testImportWithLeadingBlankLine(): void
    {
        $userId = 'test-user';

        // OPML content with a leading blank line (from tt-rss)
        $opmlWithBlankLine = '
<?xml version="1.0" encoding="UTF-8"?>
<opml version="1.0">
  <head>
    <title>Subscriptions</title>
  </head>
  <body>
    <outline text="Test Folder" title="Test Folder">
      <outline type="rss" text="Test Feed" title="Test Feed" htmlUrl="http://example.com/feed" xmlUrl="http://example.com/rss"/>
    </outline>
  </body>
</opml>
';

        $result = $this->importer->import($userId, $opmlWithBlankLine);

        // Should successfully parse the OPML despite leading whitespace
        $this->assertNotNull($result);
        $this->assertCount(1, $result[0], 'Should have 1 folder');
        $this->assertCount(1, $result[1], 'Should have 1 feed');

        $folder = $result[0][0];
        $feed = $result[1][0];

        $this->assertEquals('Test Folder', $folder['name']);
        $this->assertEquals('Test Feed', $feed['title']);
        $this->assertEquals('http://example.com/rss', $feed['url']);
        $this->assertEquals('http://example.com/feed', $feed['link']);
    }

    public function testImportWithoutLeadingBlankLine(): void
    {
        $userId = 'test-user';

        // Normal OPML content without leading whitespace
        $opmlNormal = '<?xml version="1.0" encoding="UTF-8"?>
<opml version="1.0">
  <head>
    <title>Subscriptions</title>
  </head>
  <body>
    <outline type="rss" text="Normal Feed" title="Normal Feed" htmlUrl="http://example.org/feed" xmlUrl="http://example.org/rss"/>
  </body>
</opml>
';

        $result = $this->importer->import($userId, $opmlNormal);

        $this->assertNotNull($result);
        $this->assertCount(1, $result[1], 'Should have 1 feed');

        $feed = $result[1][0];
        $this->assertEquals('Normal Feed', $feed['title']);
    }

    public function testImportWithMultipleLeadingBlankLines(): void
    {
        $userId = 'test-user';

        // OPML content with multiple leading blank lines and spaces
        $opmlMultipleBlanks = '


<?xml version="1.0" encoding="UTF-8"?>
<opml version="1.0">
  <head>
    <title>Subscriptions</title>
  </head>
  <body>
    <outline type="rss" text="Test Feed" title="Test Feed" htmlUrl="http://example.com/feed" xmlUrl="http://example.com/rss"/>
  </body>
</opml>
';

        $result = $this->importer->import($userId, $opmlMultipleBlanks);

        $this->assertNotNull($result, 'Should handle multiple leading blank lines');
        $this->assertCount(1, $result[1]);
    }
}
