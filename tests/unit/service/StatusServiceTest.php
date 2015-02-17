<?php
/**
 * ownCloud - News
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

use \OCA\News\Db\FeedType;


class StatusServiceTest extends \PHPUnit_Framework_TestCase {

    private $settings;
    private $config;
    private $service;
    private $appName;

    public function setUp(){
        $this->appName = 'news';
        $this->settings = $this->getMockBuilder(
            '\OCP\IConfig')
            ->disableOriginalConstructor()
            ->getMock();
        $this->config = $this->getMockBuilder(
            '\OCA\News\Config\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $this->service = new StatusService($this->settings, $this->config,
            $this->appName);
    }

    private function beforeStatus($cronMode='webcron', $cronEnabled=true,
                                  $version='1.0') {
        $this->settings->expects($this->at(0))
            ->method('getAppValue')
            ->with($this->equalTo($this->appName),
                $this->equalTo('installed_version'))
            ->will($this->returnValue($version));

        $this->settings->expects($this->at(1))
            ->method('getAppValue')
            ->with(
                $this->equalTo('core'),
                $this->equalTo('backgroundjobs_mode')
            )
            ->will($this->returnValue($cronMode));

        $this->config->expects($this->once())
            ->method('getUseCronUpdates')
            ->will($this->returnValue($cronEnabled));

    }


    public function testGetStatus(){
        $this->beforeStatus();

        $response = $this->service->getStatus();
        $this->assertEquals('1.0', $response['version']);
        $this->assertFalse($response['warnings']['improperlyConfiguredCron']);
    }


    public function testGetStatusNoCorrectCronAjax(){
        $this->beforeStatus('ajax');

        $response = $this->service->getStatus();
        $this->assertTrue($response['warnings']['improperlyConfiguredCron']);
    }



    public function testGetStatusNoCorrectCronTurnedOff(){
        $this->beforeStatus('ajax', false);

        $response = $this->service->getStatus();
        $this->assertFalse($response['warnings']['improperlyConfiguredCron']);
    }


}