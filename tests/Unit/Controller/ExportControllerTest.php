<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Alessandro Cosentino <cosenal@gmail.com>
 * @author    Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright 2012 Alessandro Cosentino
 * @copyright 2012-2014 Bernhard Posselt
 */

namespace OCA\News\Tests\Unit\Controller;

use OCA\News\Controller\ExportController;
use OCA\News\Service\FeedService;
use OCA\News\Service\FolderService;
use OCA\News\Service\ItemService;

use \OCA\News\Http\TextDownloadResponse;
use \OCA\News\Utility\OPMLExporter;
use \OCA\News\Db\Item;
use \OCA\News\Db\Feed;
use OCP\IRequest;

use PHPUnit\Framework\TestCase;

class ExportControllerTest extends TestCase
{

    private $appName;
    private $request;
    private $controller;
    private $user;
    private $feedService;
    private $folderService;
    private $itemService;
    private $opmlExporter;

    /**
     * Gets run before each test
     */
    public function setUp()
    {
        $this->appName = 'news';
        $this->user = 'john';
        $this->itemService = $this->getMockBuilder(ItemService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->feedService = $this->getMockBuilder(FeedService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->folderService = $this->getMockBuilder(FolderService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockBuilder(IRequest::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->opmlExporter = new OPMLExporter();
        $this->controller = new ExportController(
            $this->appName, $this->request,
            $this->folderService, $this->feedService,
            $this->itemService, $this->opmlExporter, $this->user
        );
    }


    public function testOpmlExportNoFeeds()
    {
        $opml =
        "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" .
        "<opml version=\"2.0\">\n" .
        "  <head>\n" .
        "    <title>Subscriptions</title>\n" .
        "  </head>\n" .
        "  <body/>\n" .
        "</opml>\n";

        $this->feedService->expects($this->once())
            ->method('findAll')
            ->with($this->equalTo($this->user))
            ->will($this->returnValue([]));
        $this->folderService->expects($this->once())
            ->method('findAll')
            ->with($this->equalTo($this->user))
            ->will($this->returnValue([]));

        $return = $this->controller->opml();
        $this->assertTrue($return instanceof TextDownloadResponse);
        $this->assertEquals($opml, $return->render());
    }


    public function testGetAllArticles()
    {
        $item1 = new Item();
        $item1->setFeedId(3);
        $item1->setGuid('guid');
        $item2 = new Item();
        $item2->setFeedId(5);
        $item2->setGuid('guid');

        $feed1 = new Feed();
        $feed1->setId(3);
        $feed1->setLink('http://goo');
        $feed2 = new Feed();
        $feed2->setId(5);
        $feed2->setLink('http://gee');
        $feeds = [$feed1, $feed2];

        $articles = [$item1, $item2];

        $this->feedService->expects($this->once())
            ->method('findAll')
            ->with($this->equalTo($this->user))
            ->will($this->returnValue($feeds));
        $this->itemService->expects($this->once())
            ->method('getUnreadOrStarred')
            ->with($this->equalTo($this->user))
            ->will($this->returnValue($articles));


        $return = $this->controller->articles();
        $headers = $return->getHeaders();
        $this->assertEquals(
            'attachment; filename="articles.json"',
            $headers ['Content-Disposition']
        );

        $this->assertEquals(
            '[{"guid":"guid","url":null,"title":null,' .
            '"author":null,"pubDate":null,"updatedDate":null,"body":null,"enclosureMime":null,' .
            '"enclosureLink":null,"mediaThumbnail":null,"mediaDescription":null,'.
            '"unread":false,"starred":false,' .
            '"feedLink":"http:\/\/goo","rtl":false},{"guid":"guid","url":null,' .
            '"title":null,"author":null,"pubDate":null,"updatedDate":null,"body":null,' .
            '"enclosureMime":null,"enclosureLink":null,"mediaThumbnail":null,'.
            '"mediaDescription":null,"unread":false,' .
            '"starred":false,"feedLink":"http:\/\/gee","rtl":false}]',
            $return->render()
        );
    }

}
