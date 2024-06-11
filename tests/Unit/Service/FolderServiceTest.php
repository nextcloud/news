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

namespace OCA\News\Tests\Unit\Service;

use OC\AppFramework\Utility\TimeFactory;
use OCA\News\Db\Feed;
use \OCA\News\Db\Folder;
use OCA\News\Db\FolderMapperV2;
use OCA\News\Service\Exceptions\ServiceNotFoundException;
use OCA\News\Service\FeedServiceV2;
use OCA\News\Service\Exceptions\ServiceConflictException;
use OCA\News\Service\FolderServiceV2;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class FolderServiceTest extends TestCase
{

    /**
     * @var MockObject|FolderMapperV2
     */
    private $mapper;

    /**
     * @var MockObject|FeedServiceV2
     */
    private $feedService;

    /**
     * @var FolderServiceV2
     */
    private $class;

    /**
     * @var int
     */
    private $time;

    /**
     * @var string
     */
    private $user;

    /**
     * @var MockObject|LoggerInterface
     */
    private $logger;

    protected function setUp(): void
    {
        $this->time = 222;

        $timeFactory = $this->getMockBuilder(TimeFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockDateTime = $this->getMockBuilder(\DateTimeImmutable::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockDateTime->expects($this->any())
            ->method('getTimestamp')
            ->will($this->returnValue($this->time));

        $timeFactory->expects($this->any())
            ->method('now')
            ->will($this->returnValue($mockDateTime));

        $this->feedService = $this->getMockBuilder(FeedServiceV2::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mapper = $this->getMockBuilder(FolderMapperV2::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->class = new FolderServiceV2($this->mapper, $this->logger, $this->feedService, $timeFactory);
    }

    public function testFindAll()
    {
        $return = [];
        $this->mapper->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue($return));

        $result = $this->class->findAll();

        $this->assertEquals($return, $result);
    }

    public function testFindAllForUser()
    {
        $return = [];
        $this->mapper->expects($this->once())
            ->method('findAllFromUser')
            ->with('jack')
            ->will($this->returnValue($return));

        $result = $this->class->findAllForUser('jack');

        $this->assertEquals($return, $result);
    }

    public function testFindAllForUserRecursive()
    {
        $folder = new Folder();
        $folder->setId(1);
        $this->mapper->expects($this->once())
            ->method('findAllFromUser')
            ->with('jack', [])
            ->will($this->returnValue([$folder]));

        $feeds = [new Feed(), new Feed()];
        $this->feedService->expects($this->once())
                          ->method('findAllFromFolder')
                          ->with(1)
                          ->will($this->returnValue($feeds));

        $result = $this->class->findAllForUserRecursive('jack');

        $folder->feeds = $feeds;
        $expected = [$folder];
        $this->assertEquals($expected, $result);
        $this->assertEquals($result[0]->feeds, $feeds);
    }


    public function testCreate()
    {
        $folder = new Folder();
        $folder->setName('hey');
        $folder->setParentId(5);
        $folder->setUserId('john');
        $folder->setOpened(true);

        $this->mapper->expects($this->once())
            ->method('insert')
            ->with($folder)
            ->will($this->returnValue($folder));

        $result = $this->class->create('john', 'hey', 5);

        $this->assertEquals($folder, $result);
    }

    public function testOpen()
    {
        $folder = new Folder();

        $this->mapper->expects($this->once())
            ->method('findFromUser')
            ->with('jack', 3)
            ->will($this->returnValue($folder));

        $this->mapper->expects($this->once())
            ->method('update')
            ->with($folder);

        $this->class->open('jack', 3, false);

        $this->assertFalse($folder->getOpened());
    }

    public function testRename()
    {
        $folder = new Folder();
        $folder->setName('jooohn');

        $this->mapper->expects($this->once())
            ->method('findFromUser')
            ->with('jack', 3)
            ->will($this->returnValue($folder));

        $this->mapper->expects($this->once())
            ->method('update')
            ->with($folder);

        $this->class->rename('jack', 3, 'newName');

        $this->assertEquals('newName', $folder->getName());
    }

    public function testMarkDeleted()
    {
        $folder = new Folder();
        $folder2 = new Folder();
        $folder2->setDeletedAt($this->time);

        $this->mapper->expects($this->once())
            ->method('findFromUser')
            ->with('jack', 3)
            ->will($this->returnValue($folder));
        $this->mapper->expects($this->once())
            ->method('update')
            ->with($folder2);

        $this->class->markDelete('jack', 3, true);
    }

    public function testUnmarkDeleted()
    {
        $folder = new Folder();
        $folder2 = new Folder();
        $folder2->setDeletedAt(0);

        $this->mapper->expects($this->once())
            ->method('findFromUser')
            ->with('jack', 3)
            ->will($this->returnValue($folder));
        $this->mapper->expects($this->once())
            ->method('update')
            ->with($folder2);

        $this->class->markDelete('jack', 3, false);
    }

    public function testPurgeDeleted()
    {
        $this->mapper->expects($this->exactly(1))
            ->method('purgeDeleted')
            ->with('jack', null);

        $this->class->purgeDeleted('jack', null);
    }

    public function testDelete()
    {
        $folder = new Folder();
        $this->mapper->expects($this->once())
            ->method('findFromUser')
            ->with('jack', 1)
            ->will($this->returnValue($folder));
        $this->mapper->expects($this->once())
            ->method('delete')
            ->with($folder)
            ->will($this->returnValue($folder));

        $this->class->delete('jack', 1);
    }

    public function testDeleteUser()
    {
        $folder = new Folder();
        $this->mapper->expects($this->once())
            ->method('findAllFromUser')
            ->with('jack')
            ->will($this->returnValue([$folder]));
        $this->mapper->expects($this->once())
            ->method('delete')
            ->with($folder)
            ->will($this->returnValue($folder));

        $this->class->deleteUser('jack');
    }


    public function testRead()
    {
        $folder = new Folder();
        $folder->setId(1);

        $this->mapper->expects($this->once())
            ->method('findFromUser')
            ->with('jack', 1)
            ->will($this->returnValue($folder));

        $this->mapper->expects($this->exactly(1))
            ->method('read')
            ->withConsecutive(['jack', 1, null]);

        $this->class->read('jack', 1);
    }
}
