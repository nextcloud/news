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
use OCA\News\Db\ItemMapper;
use OCA\News\Service\Exceptions\ServiceNotFoundException;
use OCA\News\Service\Service;
use \OCP\AppFramework\Db\DoesNotExistException;
use \OCP\AppFramework\Db\MultipleObjectsReturnedException;

use \OCA\News\Db\Folder;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;


class TestLegacyService extends Service
{
    public function __construct($mapper, $logger)
    {
        parent::__construct($mapper, $logger);
    }

    public function findAllForUser(string $userId, array $params = []): array
    {
        // TODO: Implement findAllForUser() method.
    }

    public function findAll(): array
    {
        // TODO: Implement findAll() method.
    }
}

class ServiceTest extends TestCase
{

    protected $mapper;
    protected $logger;
    protected $newsService;

    protected function setUp(): void
    {
        $this->mapper = $this->getMockBuilder(ItemMapper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->newsService = new TestLegacyService($this->mapper, $this->logger);
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

        $this->newsService->delete($user, $id);
    }


    public function testFind()
    {
        $id = 3;
        $user = 'ken';

        $this->mapper->expects($this->once())
            ->method('findFromUser')
            ->with($this->equalTo($user), $this->equalTo($id))
            ->will($this->returnValue(new Feed()));

        $this->newsService->find($user, $id);
    }


    public function testFindDoesNotExist()
    {
        $ex = new DoesNotExistException('hi');

        $this->mapper->expects($this->once())
            ->method('findFromUser')
            ->will($this->throwException($ex));

        $this->expectException(ServiceNotFoundException::class);
        $this->newsService->find('', 1);
    }


    public function testFindMultiple()
    {
        $ex = new MultipleObjectsReturnedException('hi');

        $this->mapper->expects($this->once())
            ->method('findFromUser')
            ->will($this->throwException($ex));

        $this->expectException(ServiceNotFoundException::class);
        $this->newsService->find('', 1);
    }

}
