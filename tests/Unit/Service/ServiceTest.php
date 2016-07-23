<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Alessandro Cosentino <cosenal@gmail.com>
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Alessandro Cosentino 2012
 * @copyright Bernhard Posselt 2012, 2014
 */

namespace OCA\News\Service;

use \OCP\AppFramework\Db\DoesNotExistException;
use \OCP\AppFramework\Db\MultipleObjectsReturnedException;

use \OCA\News\Db\Folder;


class TestService extends Service {
    public function __construct($mapper){
        parent::__construct($mapper);
    }
}

class ServiceTest extends \PHPUnit_Framework_TestCase {

    protected $mapper;
    protected $newsService;

    protected function setUp(){
        $this->mapper = $this->getMockBuilder('\OCA\News\Db\ItemMapper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->newsService = new TestService($this->mapper);
    }


    public function testDelete(){
        $id = 5;
        $user = 'ken';
        $folder = new Folder();
        $folder->setId($id);

        $this->mapper->expects($this->once())
            ->method('delete')
            ->with($this->equalTo($folder));
        $this->mapper->expects($this->once())
            ->method('find')
            ->with($this->equalTo($id), $this->equalTo($user))
            ->will($this->returnValue($folder));

        $this->newsService->delete($id, $user);
    }


    public function testFind(){
        $id = 3;
        $user = 'ken';

        $this->mapper->expects($this->once())
            ->method('find')
            ->with($this->equalTo($id), $this->equalTo($user));

        $this->newsService->find($id, $user);
    }


    public function testFindDoesNotExist(){
        $ex = new DoesNotExistException('hi');

        $this->mapper->expects($this->once())
            ->method('find')
            ->will($this->throwException($ex));

        $this->setExpectedException(
            '\OCA\News\Service\ServiceNotFoundException'
        );
        $this->newsService->find(1, '');
    }


    public function testFindMultiple(){
        $ex = new MultipleObjectsReturnedException('hi');

        $this->mapper->expects($this->once())
            ->method('find')
            ->will($this->throwException($ex));

        $this->setExpectedException(
            '\OCA\News\Service\ServiceNotFoundException'
        );
        $this->newsService->find(1, '');
    }

}
