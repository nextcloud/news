<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 */

namespace OCA\News\Tests\Unit\Utility;

use OCA\News\Utility\OPMLImporter;
use PHPUnit\Framework\TestCase;

class OPMLImporterTest extends TestCase
{
    /** @var OPMLImporter */
    private $importer;

    protected function setUp(): void
    {
        $this->importer = new OPMLImporter();
    }

    /**
     * Test importing a simple OPML with one feed
     */
    public function testImportSingleFeed(): void
    {
        $opml = '<?xml version="1.0" encoding="UTF-8"?>
        <opml version="2.0">
          <body>
            <outline type="rss" text="Test Feed" title="Test Feed"
                     xmlUrl="https://example.com/feed.xml"
                     htmlUrl="https://example.com" />
          </body>
        </opml>';

        $result = $this->importer->import('testuser', $opml);

        $this->assertNotNull($result);
        [$folders, $feeds] = $result;

        $this->assertEmpty($folders);
        $this->assertCount(1, $feeds);
        $this->assertEquals('https://example.com/feed.xml', $feeds[0]['url']);
        $this->assertEquals('Test Feed', $feeds[0]['title']);
        $this->assertEquals('https://example.com', $feeds[0]['link']);
        $this->assertNull($feeds[0]['folder']);
    }

    /**
     * Test importing OPML with feeds inside folders
     */
    public function testImportFeedInFolder(): void
    {
        $opml = '<?xml version="1.0" encoding="UTF-8"?>
        <opml version="2.0">
          <body>
            <outline text="Tech">
              <outline type="rss" text="Ars Technica" title="Ars Technica"
                       xmlUrl="https://arstechnica.com/feed/"
                       htmlUrl="https://arstechnica.com" />
            </outline>
          </body>
        </opml>';

        $result = $this->importer->import('testuser', $opml);

        $this->assertNotNull($result);
        [$folders, $feeds] = $result;

        $this->assertCount(1, $folders);
        $this->assertEquals('Tech', $folders[0]['name']);
        $this->assertNull($folders[0]['parentName']);

        $this->assertCount(1, $feeds);
        $this->assertEquals('Tech', $feeds[0]['folder']);
    }

    /**
     * Test importing OPML with nested folders
     */
    public function testImportNestedFolders(): void
    {
        $opml = '<?xml version="1.0" encoding="UTF-8"?>
        <opml version="2.0">
          <body>
            <outline text="News">
              <outline text="Local">
                <outline type="rss" text="Local News"
                         xmlUrl="https://local.example.com/feed"
                         htmlUrl="https://local.example.com" />
              </outline>
            </outline>
          </body>
        </opml>';

        $result = $this->importer->import('testuser', $opml);

        [$folders, $feeds] = $result;

        $this->assertCount(2, $folders);
        $this->assertEquals('News', $folders[0]['name']);
        $this->assertNull($folders[0]['parentName']);
        $this->assertEquals('Local', $folders[1]['name']);
        $this->assertEquals('News', $folders[1]['parentName']);

        $this->assertCount(1, $feeds);
        $this->assertEquals('Local', $feeds[0]['folder']);
    }

    /**
     * Test importing invalid XML returns null
     */
    public function testImportInvalidXml(): void
    {
        $result = $this->importer->import('testuser', 'not xml at all');

        $this->assertNull($result);
    }

    /**
     * Test importing XML without body element returns null
     */
    public function testImportNoBody(): void
    {
        $opml = '<?xml version="1.0" encoding="UTF-8"?>
        <opml version="2.0">
          <head><title>Test</title></head>
        </opml>';

        $result = $this->importer->import('testuser', $opml);

        $this->assertNull($result);
    }

    /**
     * Test importing empty body returns empty arrays
     */
    public function testImportEmptyBody(): void
    {
        $opml = '<?xml version="1.0" encoding="UTF-8"?>
        <opml version="2.0">
          <body></body>
        </opml>';

        $result = $this->importer->import('testuser', $opml);

        $this->assertNotNull($result);
        [$folders, $feeds] = $result;

        $this->assertEmpty($folders);
        $this->assertEmpty($feeds);
    }

    /**
     * Test importing multiple feeds at root level
     */
    public function testImportMultipleFeedsRootLevel(): void
    {
        $opml = '<?xml version="1.0" encoding="UTF-8"?>
        <opml version="2.0">
          <body>
            <outline type="rss" text="Feed 1"
                     xmlUrl="https://example.com/feed1.xml"
                     htmlUrl="https://example.com/1" />
            <outline type="rss" text="Feed 2"
                     xmlUrl="https://example.com/feed2.xml"
                     htmlUrl="https://example.com/2" />
          </body>
        </opml>';

        $result = $this->importer->import('testuser', $opml);

        [$folders, $feeds] = $result;

        $this->assertEmpty($folders);
        $this->assertCount(2, $feeds);
        $this->assertEquals('https://example.com/feed1.xml', $feeds[0]['url']);
        $this->assertEquals('https://example.com/feed2.xml', $feeds[1]['url']);
    }

    /**
     * Test that empty folder (no feeds inside) is still imported
     */
    public function testImportEmptyFolder(): void
    {
        $opml = '<?xml version="1.0" encoding="UTF-8"?>
        <opml version="2.0">
          <body>
            <outline text="Empty Folder" />
          </body>
        </opml>';

        $result = $this->importer->import('testuser', $opml);

        [$folders, $feeds] = $result;

        $this->assertCount(1, $folders);
        $this->assertEquals('Empty Folder', $folders[0]['name']);
        $this->assertEmpty($feeds);
    }

    /**
     * Test that calling import twice resets internal state
     */
    public function testImportResetsState(): void
    {
        $opml1 = '<?xml version="1.0" encoding="UTF-8"?>
        <opml version="2.0">
          <body>
            <outline type="rss" text="Feed A" xmlUrl="https://a.com/feed" htmlUrl="https://a.com" />
          </body>
        </opml>';

        $opml2 = '<?xml version="1.0" encoding="UTF-8"?>
        <opml version="2.0">
          <body>
            <outline type="rss" text="Feed B" xmlUrl="https://b.com/feed" htmlUrl="https://b.com" />
          </body>
        </opml>';

        $this->importer->import('user1', $opml1);
        $result = $this->importer->import('user2', $opml2);

        [$folders, $feeds] = $result;

        $this->assertCount(1, $feeds);
        $this->assertEquals('https://b.com/feed', $feeds[0]['url']);
    }
}
