<?php
/**
 * Created by IntelliJ IDEA.
 * User: bernhard
 * Date: 11/26/15
 * Time: 7:40 PM
 */

namespace OCA\News\Upgrade;

use OCP\IConfig;
use OCA\News\Service\ItemService;

class UpgradeTest extends \PHPUnit_Framework_TestCase {

    /** @var  Upgrade */
    private $upgrade;

    /** @var  ItemService */
    private $service;

    /** @var  IConfig */
    private $config;

    /** @var  IDBConnection */
    private $db;

    public function setUp() {
        $this->config = $this->getMockBuilder(
            '\OCP\IConfig')
            ->disableOriginalConstructor()
            ->getMock();

        $this->db = $this->getMockBuilder(
            '\OCP\IDBConnection')
            ->disableOriginalConstructor()
            ->getMock();

        $this->service = $this->getMockBuilder(
            '\OCA\News\Service\ItemService')
            ->disableOriginalConstructor()
            ->getMock();

        $this->upgrade = new Upgrade($this->config, $this->service,
            $this->db, 'news');
    }

    public function testUpgrade() {
        $this->config->expects($this->once())
            ->method('getAppValue')
            ->with($this->equalTo('news'), $this->equalTo('installed_version'))
            ->will($this->returnValue('6.9.9'));

        $this->service->expects($this->once())
            ->method('generateSearchIndices');

        $this->upgrade->upgrade();
    }

    public function testNoUpgrade() {
        $this->config->expects($this->once())
            ->method('getAppValue')
            ->with($this->equalTo('news'), $this->equalTo('installed_version'))
            ->will($this->returnValue('7.0.0'));

        $this->service->expects($this->never())
            ->method('generateSearchIndices');

        $this->upgrade->upgrade();
    }

}
