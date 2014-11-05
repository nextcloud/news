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


class PageControllerTest extends \PHPUnit_Framework_TestCase {

    private $settings;
    private $appName;
    private $request;
    private $controller;
    private $user;
    private $l10n;
    private $urlGenerator;
    private $appConfig;
    private $configData;

    /**
     * Gets run before each test
     */
    public function setUp(){
        $this->appName = 'news';
        $this->user = 'becka';
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
        $this->l10n = $this->request = $this->getMockBuilder(
            '\OCP\IL10n')
            ->disableOriginalConstructor()
            ->getMock();
        $this->settings = $this->getMockBuilder(
            '\OCP\IConfig')
            ->disableOriginalConstructor()
            ->getMock();
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
        $this->controller = new PageController($this->appName, $this->request,
            $this->settings, $this->urlGenerator, $this->appConfig, $this->l10n,
            $this->user);
    }


    public function testIndex(){
        $response = $this->controller->index();
        $this->assertEquals('index', $response->getTemplateName());
    }


    public function testSettings() {
        $result = [
            'settings' => [
                'showAll' => true,
                'compact' => true,
                'preventReadOnScroll' => true,
                'oldestFirst' => true,
                'language' => 'de',
            ]
        ];

        $this->l10n->expects($this->once())
            ->method('getLanguageCode')
            ->will($this->returnValue('de'));
        $this->settings->expects($this->at(0))
            ->method('getUserValue')
            ->with($this->equalTo($this->user),
                $this->equalTo($this->appName),
                $this->equalTo('showAll'))
            ->will($this->returnValue('1'));
        $this->settings->expects($this->at(1))
            ->method('getUserValue')
            ->with($this->equalTo($this->user),
                $this->equalTo($this->appName),
                $this->equalTo('compact'))
            ->will($this->returnValue('1'));
        $this->settings->expects($this->at(2))
            ->method('getUserValue')
            ->with($this->equalTo($this->user),
                $this->equalTo($this->appName),
                $this->equalTo('preventReadOnScroll'))
            ->will($this->returnValue('1'));
        $this->settings->expects($this->at(3))
            ->method('getUserValue')
            ->with($this->equalTo($this->user),
                $this->equalTo($this->appName),
                $this->equalTo('oldestFirst'))
            ->will($this->returnValue('1'));

        $response = $this->controller->settings();
        $this->assertEquals($result, $response);
    }


    public function testUpdateSettings() {
        $this->settings->expects($this->at(0))
            ->method('setUserValue')
            ->with($this->equalTo($this->user),
                $this->equalTo($this->appName),
                $this->equalTo('showAll'),
                $this->equalTo(true));
        $this->settings->expects($this->at(1))
            ->method('setUserValue')
            ->with($this->equalTo($this->user),
                $this->equalTo($this->appName),
                $this->equalTo('compact'),
                $this->equalTo(true));
        $this->settings->expects($this->at(2))
            ->method('setUserValue')
            ->with($this->equalTo($this->user),
                $this->equalTo($this->appName),
                $this->equalTo('preventReadOnScroll'),
                $this->equalTo(false));
        $this->settings->expects($this->at(3))
            ->method('setUserValue')
            ->with($this->equalTo($this->user),
                $this->equalTo($this->appName),
                $this->equalTo('oldestFirst'),
                $this->equalTo(true));
        $this->controller->updateSettings(true, true, false, true);

    }


    public function testManifest(){
        $this->appConfig->expects($this->once())
            ->method('getConfig')
            ->will($this->returnValue($this->configData));
        $this->l10n->expects($this->once())
            ->method('getLanguageCode')
            ->will($this->returnValue('de_DE'));

        $result = $this->controller->manifest()->getData();
        $this->assertEquals($this->configData['name'], $result['name']);
        $this->assertEquals('web', $result['type']);
        $this->assertEquals(
            $this->configData['description'], $result['description']
        );
        $this->assertEquals('de-DE', $result['default_locale']);
        $this->assertEquals(
            $this->configData['homepage'], $result['developer']['url']
        );
        $this->assertEquals('john, test', $result['developer']['name']);
    }


}