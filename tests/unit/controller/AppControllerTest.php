<?php
/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Alessandro Cosentino <cosenal@gmail.com>
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Alessandro Cosentino 2014
 * @copyright Bernhard Posselt 2014
 */

namespace OCA\News\Controller;


class AppControllerTest extends \PHPUnit_Framework_TestCase {

    private $appName;
    private $request;
    private $urlGenerator;
    private $appConfig;
    private $controller;
    private $configData;

    /**
     * Gets run before each test
     */
    public function setUp(){
        $this->appName = 'news';
        $this->request = $this->getMockBuilder(
            '\OCP\IRequest')
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlGenerator = $this->getMockBuilder(
            '\OCP\IURLGenerator')
            ->disableOriginalConstructor()
            ->getMock();
        $this->appConfig = $this->getMockBuilder(
            '\OCA\News\Config\AppConfig')
            ->disableOriginalConstructor()
            ->getMock();

        $this->configData = [
            'name' => 'AppTest',
            'id' => 'apptest',
            'authors' => [
                ['name' => 'john'],
                ['name' => 'test']
            ],
            'description' => 'This is a test app',
            'homepage' => 'https://github.com/owncloud/test'
        ];

        $this->controller = new AppController($this->appName, $this->request,
            $this->urlGenerator, $this->appConfig);
    }

    public function testManifest(){
        $this->appConfig->expects($this->once())
            ->method('getConfig')
            ->will($this->returnValue($this->configData));
        $result = $this->controller->manifest();
        $this->assertEquals($this->configData['name'], $result['name']);
        $this->assertEquals($this->configData['description'], $result['description']);
        $this->assertEquals($this->configData['homepage'], $result['developer']['url']);
        $this->assertEquals('john, test', $result['developer']['name']);
    }

}