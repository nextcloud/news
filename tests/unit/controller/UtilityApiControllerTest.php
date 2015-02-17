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

namespace OCA\News\Controller;


class UtilityApiControllerTest extends \PHPUnit_Framework_TestCase {

    private $settings;
    private $request;
    private $newsAPI;
    private $updater;
    private $appName;
    private $status;

    protected function setUp() {
        $this->appName = 'news';
        $this->settings = $this->getMockBuilder(
            '\OCP\IConfig')
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockBuilder(
            '\OCP\IRequest')
            ->disableOriginalConstructor()
            ->getMock();
        $this->updater = $this->getMockBuilder(
            '\OCA\News\Utility\Updater')
            ->disableOriginalConstructor()
            ->getMock();
        $this->status = $this->getMockBuilder(
            '\OCA\News\Service\StatusService')
            ->disableOriginalConstructor()
            ->getMock();
        $this->newsAPI = new UtilityApiController(
            $this->appName, $this->request, $this->updater, $this->settings,
            $this->status
        );
    }


    public function testGetVersion(){
        $this->settings->expects($this->once())
            ->method('getAppValue')
            ->with($this->equalTo($this->appName),
                $this->equalTo('installed_version'))
            ->will($this->returnValue('1.0'));

        $response = $this->newsAPI->version();
        $version = $response['version'];

        $this->assertEquals('1.0', $version);
    }


    public function testBeforeUpdate(){
        $this->updater->expects($this->once())
            ->method('beforeUpdate');
        $this->newsAPI->beforeUpdate();
    }


    public function testAfterUpdate(){
        $this->updater->expects($this->once())
            ->method('afterUpdate');
        $this->newsAPI->afterUpdate();
    }


    public function testStatus(){
        $in = 'hi';
        $this->status->expects($this->once())
            ->method('getStatus')
            ->will($this->returnValue($in));
        $result = $this->newsAPI->status();

        $this->assertEquals($in, $result);
    }


}
