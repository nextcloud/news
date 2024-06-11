<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Marco Nassabain <marco.nassabain@hotmail.com>
 */


namespace OCA\News\Tests\Unit\Service;

use OCA\News\Service\Exceptions\ServiceNotFoundException;
use OCA\News\Service\FeedServiceV2;
use OCA\News\Service\ItemServiceV2;
use OCA\News\Service\ShareService;

use OCA\News\Db\Feed;
use OCA\News\Db\Item;

use OCP\IURLGenerator;
use OCP\IUserManager;
use OCP\IL10N;
use OCP\IUser;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ShareServiceTest extends TestCase
{
    /**
     * @var MockObject|ItemServiceV2
     */
    private $itemService;

    /**
     * @var MockObject|FeedServiceV2
     */
    private $feedService;

    /**
     * @var MockObject|IURLGenerator
     */
    private $urlGenerator;

    /**
     * @var MockObject|IUserManager
     */
    private $userManager;

    /**
     * @var MockObject|IL10N
     */
    private $l10n;

    /**
     * @var MockObject|LoggerInterface
     */
    private $logger;

    /** @var ShareService */
    private $class;

    /**
     * @var string
     */
    private $uid;

    /**
     * @var string
     */
    private $time;

    /**
     * @var string
     */
    private $recipient;

    protected function setUp(): void
    {
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemService = $this
            ->getMockBuilder(ItemServiceV2::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->feedService = $this
            ->getMockBuilder(FeedServiceV2::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlGenerator = $this
            ->getMockBuilder(IURLGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->userManager = $this
            ->getMockBuilder(IUserManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->l10n = $this->getMockBuilder(IL10N::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->time = 333333;

        $this->class = new ShareService(
            $this->feedService,
            $this->itemService,
            $this->urlGenerator,
            $this->userManager,
            $this->l10n,
            $this->logger
        );

        $this->uid = 'sender';
        $this->recipient = 'recipient';
    }

    public function testShareItemWithUser()
    {
        $feedUrl = 'http://serverurl/news/sharedwithme';
        $itemId = 3;

        // Item to be shared
        $item = new Item();
        $item->setGuid('_guid_')
            ->setGuidHash(md5('_guid_'))
            ->setUrl('_url_')
            ->setTitle('_title_')
            ->setAuthor('_author_')
            ->setUnread(0)
            ->setStarred(1)
            ->setFeedId(10);

        // Shared item
        $sharedItem = clone $item;
        $sharedItem->setUnread(1)       // A newly shared item is unread, ...
            ->setStarred(0)             // ... not starred, ...
            ->setFeedId(100)            // ... placed in the 'Shared with me' feed, ...
            ->setSharedBy($this->uid);  // ... and contains the senders user ID

        // Dummy feed 'Shared with me'
        $feed = new Feed();
        $feed->setId(100);
        $feed->setUserId($this->recipient)
            ->setUrl($feedUrl)
            ->setLink($feedUrl)
            ->setTitle('Shared with me')
            ->setAdded($this->time)
            ->setFolderId(null)
            ->setPreventUpdate(true);


        $this->itemService->expects($this->once())
            ->method('find')
            ->with($this->uid, $itemId)
            ->will($this->returnValue($item));

        $this->urlGenerator->expects($this->once())
            ->method('getBaseUrl')
            ->will($this->returnValue('http://serverurl'));

        $this->feedService->expects($this->once())
            ->method('findByUrl')
            ->with($this->recipient, $feedUrl)
            ->will($this->returnValue($feed));

        // Here we test if the setters worked properly using 'with()'
        $this->itemService->expects($this->once())
            ->method('insertOrUpdate')
            ->with($sharedItem)
            ->will($this->returnValue($sharedItem));


        $this->class->shareItemWithUser($this->uid, $itemId, $this->recipient);
    }

    public function testShareItemWithUserCreatesOwnFeedWhenNotFound()
    {
        $feedUrl = 'http://serverurl/news/sharedwithme';
        $itemId = 3;

        // Item to be shared
        $item = new Item();
        $item->setGuid('_guid_')
            ->setGuidHash(md5('_guid_'))
            ->setUrl('_url_')
            ->setTitle('_title_')
            ->setAuthor('_author_')
            ->setUnread(0)
            ->setStarred(1)
            ->setFeedId(10);

        // Shared item
        $sharedItem = clone $item;
        $sharedItem->setUnread(1)       // A newly shared item is unread, ...
            ->setStarred(0)             // ... not starred, ...
            ->setFeedId(100)            // ... placed in the 'Shared with me' feed, ...
            ->setSharedBy($this->uid);  // ... and contains the senders user ID

        // Dummy feed 'Shared with me'
        $feed = new Feed();
        $feed->setId(100);
        $feed->setUserId($this->recipient)
            ->setUrl($feedUrl)
            ->setLink($feedUrl)
            ->setTitle('Shared with me')
            ->setAdded($this->time)
            ->setFolderId(null)
            ->setPreventUpdate(true);


        $this->itemService->expects($this->once())
            ->method('find')
            ->with($this->uid, $itemId)
            ->will($this->returnValue($item));

        $this->urlGenerator->expects($this->once())
            ->method('getBaseUrl')
            ->will($this->returnValue('http://serverurl'));

        $this->feedService->expects($this->once())
            ->method('findByUrl')
            ->with($this->recipient, $feedUrl)
            ->will($this->returnValue(null));

        $this->l10n->expects($this->once())
            ->method('t')
            ->with('Shared with me')
            ->will($this->returnValue('Shared with me'));

        $this->feedService->expects($this->once())
            ->method('insert')
            ->will($this->returnValue($feed));

        // Here we test if the setters worked properly using 'with()'
        $this->itemService->expects($this->once())
            ->method('insertOrUpdate')
            ->with($sharedItem)
            ->will($this->returnValue($sharedItem));


        $this->class->shareItemWithUser($this->uid, $itemId, $this->recipient);
    }


    public function testShareItemWithUserItemDoesNotExist()
    {
        $this->expectException(ServiceNotFoundException::class);
        $this->itemService->expects($this->once())
            ->method('find')
            ->will($this->throwException(new ServiceNotFoundException('')));

        $this->class->shareItemWithUser('sender', 1, 'recipient');
    }


    public function testMapSharedByDisplayNames()
    {
        $item1 = new Item();
        $item1->setTitle('Item 1')
              ->setSharedBy('sender');
        $item2 = new Item();
        $item2->setTitle('Item 2')
              ->setSharedBy(null);

        $items = [$item1, $item2];
        $user = $this->getMockBuilder(IUser::class)
                     ->getMock();

        $this->userManager->expects($this->once())
            ->method('get')
            ->with('sender')
            ->will($this->returnValue($user));

        $user->expects($this->once())
            ->method('getDisplayName')
            ->will($this->returnValue('Mr. Sender'));

        $result = $this->class->mapSharedByDisplayNames($items);

        $this->assertEquals('Mr. Sender', $result[0]->getSharedByDisplayName());
        $this->assertEquals(null, $result[1]->getSharedByDisplayName());
    }
}
