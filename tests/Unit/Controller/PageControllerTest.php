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

use \OCA\News\Db\FeedType;


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
    private $config;
    private $recommended;
    private $status;

    /**
     * Gets run before each test
     */
    public function setUp(){
        $this->appName = 'news';
        $this->user = 'becka';
        $this->configData = [
            'name' => 'AppTest',
            'id' => 'apptest',
            'navigation' => [
                'route' => 'apptest.index.php'
            ],
            'author' => 'john, test',
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
        $this->config = $this->getMockBuilder(
            '\OCA\News\Config\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $this->recommended = $this->getMockBuilder(
            '\OCA\News\Explore\RecommendedSites')
            ->disableOriginalConstructor()
            ->getMock();
        $this->status = $this->getMockBuilder(
            '\OCA\News\Service\StatusService')
            ->disableOriginalConstructor()
            ->getMock();
        $this->controller = new PageController($this->appName, $this->request,
            $this->settings, $this->urlGenerator, $this->appConfig,
            $this->config, $this->l10n, $this->recommended, $this->status,
            $this->user);
    }


    public function testIndex(){
        $this->status->expects($this->once())
            ->method('getStatus')
            ->will($this->returnValue([
                'warnings' => [
                    'improperlyConfiguredCron' => false
                ]
            ]));

        $response = $this->controller->index();
        $this->assertEquals('index', $response->getTemplateName());
        $this->assertSame(false, $response->getParams()['cronWarning']);
    }


    public function testIndexNoCorrectCronAjax(){
        $this->status->expects($this->once())
            ->method('getStatus')
            ->will($this->returnValue([
                'warnings' => [
                    'improperlyConfiguredCron' => true
                ]
            ]));


        $response = $this->controller->index();
        $this->assertEquals(true, $response->getParams()['cronWarning']);
    }


    public function testSettings() {
        $result = [
            'settings' => [
                'showAll' => true,
                'compact' => true,
                'preventReadOnScroll' => true,
                'oldestFirst' => true,
                'compactExpand' => true,
                'language' => 'de',
                'exploreUrl' => 'test'
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
        $this->settings->expects($this->at(4))
            ->method('getUserValue')
            ->with($this->equalTo($this->user),
                $this->equalTo($this->appName),
                $this->equalTo('compactExpand'))
            ->will($this->returnValue('1'));
        $this->config->expects($this->once())
            ->method('getExploreUrl')
            ->will($this->returnValue(' '));
        $this->urlGenerator->expects($this->once())
            ->method('linkToRoute')
            ->with($this->equalTo('news.page.explore'),
                    $this->equalTo(['lang' => 'en']))
            ->will($this->returnValue('test'));


        $response = $this->controller->settings();
        $this->assertEquals($result, $response);
    }


    public function testSettingsExploreUrlSet() {
        $result = [
            'settings' => [
                'showAll' => true,
                'compact' => true,
                'preventReadOnScroll' => true,
                'oldestFirst' => true,
                'language' => 'de',
                'compactExpand' => true,
                'exploreUrl' => 'abc'
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
        $this->settings->expects($this->at(4))
            ->method('getUserValue')
            ->with($this->equalTo($this->user),
                $this->equalTo($this->appName),
                $this->equalTo('compactExpand'))
            ->will($this->returnValue('1'));
        $this->config->expects($this->once())
            ->method('getExploreUrl')
            ->will($this->returnValue('abc'));
        $this->urlGenerator->expects($this->never())
            ->method('getAbsoluteURL');


        $response = $this->controller->settings();
        $this->assertEquals($result, $response);
    }

    public function testUpdateSettings() {
        $this->settings->expects($this->at(0))
            ->method('setUserValue')
            ->with($this->equalTo($this->user),
                $this->equalTo($this->appName),
                $this->equalTo('showAll'),
                $this->equalTo('1'));
        $this->settings->expects($this->at(1))
            ->method('setUserValue')
            ->with($this->equalTo($this->user),
                $this->equalTo($this->appName),
                $this->equalTo('compact'),
                $this->equalTo('1'));
        $this->settings->expects($this->at(2))
            ->method('setUserValue')
            ->with($this->equalTo($this->user),
                $this->equalTo($this->appName),
                $this->equalTo('preventReadOnScroll'),
                $this->equalTo('0'));
        $this->settings->expects($this->at(3))
            ->method('setUserValue')
            ->with($this->equalTo($this->user),
                $this->equalTo($this->appName),
                $this->equalTo('oldestFirst'),
                $this->equalTo('1'));
        $this->settings->expects($this->at(4))
            ->method('setUserValue')
            ->with($this->equalTo($this->user),
                $this->equalTo($this->appName),
                $this->equalTo('compactExpand'),
                $this->equalTo('1'));
        $this->controller->updateSettings(true, true, false, true, true);

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


    public function testExplore(){
        $in = 'test';
        $this->settings->expects($this->at(0))
            ->method('setUserValue')
            ->with($this->equalTo($this->user),
                $this->equalTo($this->appName),
                $this->equalTo('lastViewedFeedId'),
                $this->equalTo(0));

        $this->settings->expects($this->at(1))
            ->method('setUserValue')
            ->with($this->equalTo($this->user),
                $this->equalTo($this->appName),
                $this->equalTo('lastViewedFeedType'),
                $this->equalTo(FeedType::EXPLORE));

        $this->recommended->expects($this->once())
            ->method('forLanguage')
            ->with($this->equalTo('en'))
            ->will($this->returnValue($in));


        $out = $this->controller->explore('en');

        $this->assertEquals($in, $out);
    }

}
