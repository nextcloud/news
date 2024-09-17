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
use OCA\News\Service\FeedServiceV2;
use OCA\News\Service\FolderServiceV2;

use OCA\News\Service\ItemServiceV2;
use OCA\News\Service\OpmlService;
use \OCA\News\Utility\OPMLExporter;
use \OCA\News\Db\Item;
use \OCA\News\Db\Feed;
use OCP\AppFramework\Http\DataDownloadResponse;
use OCP\IRequest;

use OCP\IUser;
use OCP\IUserSession;
use PHPUnit\Framework\TestCase;

class ExportControllerTest extends TestCase
{

    private $controller;
    private $user;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|IUserSession
     */
    private $userSession;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|FeedServiceV2
     */
    private $feedService;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|FolderServiceV2
     */
    private $folderService;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ItemServiceV2
     */
    private $itemService;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|OpmlService
     */
    private $opmlService;

    /**
     * Gets run before each test
     */
    public function setUp(): void
    {
        $appName = 'news';
        $this->itemService = $this->getMockBuilder(ItemServiceV2::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->feedService = $this->getMockBuilder(FeedServiceV2::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->folderService = $this->getMockBuilder(FolderServiceV2::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->opmlService = $this->getMockBuilder(OpmlService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->user = $this->getMockBuilder(IUser::class)->getMock();
        $this->user->expects($this->any())
                   ->method('getUID')
                   ->will($this->returnValue('user'));
        $this->userSession = $this->getMockBuilder(IUserSession::class)
                                  ->getMock();
        $this->userSession->expects($this->any())
                          ->method('getUser')
                          ->will($this->returnValue($this->user));
        $request = $this->getMockBuilder(IRequest::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->controller = new ExportController(
            $request,
            $this->folderService,
            $this->feedService,
            $this->itemService,
            $this->opmlService,
            $this->userSession
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

        $this->opmlService->expects($this->once())
            ->method('export')
            ->with('user')
            ->will($this->returnValue($opml));

        $return = $this->controller->opml();
        $this->assertTrue($return instanceof DataDownloadResponse);
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
            ->method('findAllForUser')
            ->with('user')
            ->will($this->returnValue($feeds));
        $this->itemService->expects($this->exactly(2))
            ->method('findAllForUser')
            ->withConsecutive(['user', ['unread' => false, 'starred' => true]], ['user', ['unread' => true]])
            ->willReturnOnConsecutiveCalls($articles, []);


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
