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

use OCA\News\Db\Feed;
use OCA\News\Db\ItemMapperV2;
use OCA\News\Service\Exceptions\ServiceConflictException;
use OCA\News\Service\Exceptions\ServiceNotFoundException;
use OCA\News\Service\Service;
use \OCP\AppFramework\Db\DoesNotExistException;
use \OCP\AppFramework\Db\MultipleObjectsReturnedException;

use \OCA\News\Db\Folder;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ServiceTest extends TestCase
{

    protected $mapper;
    protected $logger;
    protected $class;

    protected function setUp(): void
    {
        $this->mapper = $this->getMockBuilder(ItemMapperV2::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->class = $this->getMockBuilder(Service::class)
                            ->setConstructorArgs([$this->mapper, $this->logger])
                            ->getMockForAbstractClass();
    }


    public function testDelete()
    {
        $id = 5;
        $user = 'ken';
        $folder = new Folder();
        $folder->setId($id);

        $this->mapper->expects($this->once())
            ->method('delete')
            ->with($this->equalTo($folder));
        $this->mapper->expects($this->once())
            ->method('findFromUser')
            ->with($this->equalTo($user), $this->equalTo($id))
            ->will($this->returnValue($folder));

        $this->class->delete($user, $id);
    }


    public function testInsert()
    {
        $folder = new Folder();

        $this->mapper->expects($this->once())
            ->method('insert')
            ->with($this->equalTo($folder));

        $this->class->insert($folder);
    }


    public function testFind()
    {
        $id = 3;
        $user = 'ken';

        $this->mapper->expects($this->once())
            ->method('findFromUser')
            ->with($this->equalTo($user), $this->equalTo($id))
            ->will($this->returnValue(new Feed()));

        $this->class->find($user, $id);
    }


    public function testFindDoesNotExist()
    {
        $ex = new DoesNotExistException('hi');

        $this->mapper->expects($this->once())
            ->method('findFromUser')
            ->will($this->throwException($ex));

        $this->expectException(ServiceNotFoundException::class);
        $this->class->find('', 1);
    }


    public function testFindMultiple()
    {
        $ex = new MultipleObjectsReturnedException('hi');

        $this->mapper->expects($this->once())
            ->method('findFromUser')
            ->will($this->throwException($ex));

        $this->expectException(ServiceConflictException::class);
        $this->class->find('', 1);
    }


    public function testDeleteUser()
    {
        $feed1 = Feed::fromParams(['id' => 1]);
        $feed2 = Feed::fromParams(['id' => 2]);

        $this->class->expects($this->once())
                    ->method('findAllForUser')
                    ->with('')
                    ->willReturn([$feed1, $feed2]);

        $this->mapper->expects($this->exactly(2))
            ->method('delete')
            ->withConsecutive([$feed1], [$feed2]);

        $this->class->deleteUser('');
    }
}
