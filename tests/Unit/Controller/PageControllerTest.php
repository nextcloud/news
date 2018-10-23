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

namespace OCA\News\Tests\Unit\Controller;

use OCA\News\Config\Config;
use OCA\News\Controller\PageController;
use \OCA\News\Db\FeedType;
use OCA\News\Explore\RecommendedSites;
use OCA\News\Service\StatusService;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IRequest;
use OCP\IURLGenerator;
use PHPUnit\Framework\TestCase;


class PageControllerTest extends TestCase
{

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
    public function setUp()
    {
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
        $this->l10n = $this->request = $this->getMockBuilder(IL10N::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->settings = $this->getMockBuilder(IConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockBuilder(IRequest::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlGenerator = $this->getMockBuilder(IURLGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->appConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->recommended = $this->getMockBuilder(RecommendedSites::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->status = $this->getMockBuilder(StatusService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->controller = new PageController(
            $this->appName, $this->request,
            $this->settings, $this->urlGenerator, $this->config,
            $this->l10n, $this->recommended, $this->status,
            $this->user
        );
    }


    public function testIndex()
    {
        $this->status->expects($this->once())
            ->method('getStatus')
            ->will(
                $this->returnValue(
                    [
                    'warnings' => [
                    'improperlyConfiguredCron' => false
                    ]
                    ]
                )
            );

        $response = $this->controller->index();
        $this->assertEquals('index', $response->getTemplateName());
        $this->assertSame(false, $response->getParams()['warnings']['improperlyConfiguredCron']);
    }


    public function testIndexNoCorrectCronAjax()
    {
        $this->status->expects($this->once())
            ->method('getStatus')
            ->will(
                $this->returnValue(
                    [
                    'warnings' => [
                    'improperlyConfiguredCron' => true
                    ]
                    ]
                )
            );


        $response = $this->controller->index();
        $this->assertEquals(true, $response->getParams()['warnings']['improperlyConfiguredCron']);
    }


    public function testSettings() 
    {
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
            ->with(
                $this->equalTo($this->user),
                $this->equalTo($this->appName),
                $this->equalTo('showAll')
            )
            ->will($this->returnValue('1'));
        $this->settings->expects($this->at(1))
            ->method('getUserValue')
            ->with(
                $this->equalTo($this->user),
                $this->equalTo($this->appName),
                $this->equalTo('compact')
            )
            ->will($this->returnValue('1'));
        $this->settings->expects($this->at(2))
            ->method('getUserValue')
            ->with(
                $this->equalTo($this->user),
                $this->equalTo($this->appName),
                $this->equalTo('preventReadOnScroll')
            )
            ->will($this->returnValue('1'));
        $this->settings->expects($this->at(3))
            ->method('getUserValue')
            ->with(
                $this->equalTo($this->user),
                $this->equalTo($this->appName),
                $this->equalTo('oldestFirst')
            )
            ->will($this->returnValue('1'));
        $this->settings->expects($this->at(4))
            ->method('getUserValue')
            ->with(
                $this->equalTo($this->user),
                $this->equalTo($this->appName),
                $this->equalTo('compactExpand')
            )
            ->will($this->returnValue('1'));
        $this->config->expects($this->once())
            ->method('getExploreUrl')
            ->will($this->returnValue(' '));
        $this->urlGenerator->expects($this->once())
            ->method('linkToRoute')
            ->with(
                $this->equalTo('news.page.explore'),
                $this->equalTo(['lang' => 'en'])
            )
            ->will($this->returnValue('test'));


        $response = $this->controller->settings();
        $this->assertEquals($result, $response);
    }


    public function testSettingsExploreUrlSet() 
    {
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
            ->with(
                $this->equalTo($this->user),
                $this->equalTo($this->appName),
                $this->equalTo('showAll')
            )
            ->will($this->returnValue('1'));
        $this->settings->expects($this->at(1))
            ->method('getUserValue')
            ->with(
                $this->equalTo($this->user),
                $this->equalTo($this->appName),
                $this->equalTo('compact')
            )
            ->will($this->returnValue('1'));
        $this->settings->expects($this->at(2))
            ->method('getUserValue')
            ->with(
                $this->equalTo($this->user),
                $this->equalTo($this->appName),
                $this->equalTo('preventReadOnScroll')
            )
            ->will($this->returnValue('1'));
        $this->settings->expects($this->at(3))
            ->method('getUserValue')
            ->with(
                $this->equalTo($this->user),
                $this->equalTo($this->appName),
                $this->equalTo('oldestFirst')
            )
            ->will($this->returnValue('1'));
        $this->settings->expects($this->at(4))
            ->method('getUserValue')
            ->with(
                $this->equalTo($this->user),
                $this->equalTo($this->appName),
                $this->equalTo('compactExpand')
            )
            ->will($this->returnValue('1'));
        $this->config->expects($this->once())
            ->method('getExploreUrl')
            ->will($this->returnValue('abc'));
        $this->urlGenerator->expects($this->never())
            ->method('getAbsoluteURL');


        $response = $this->controller->settings();
        $this->assertEquals($result, $response);
    }

    public function testUpdateSettings() 
    {
        $this->settings->expects($this->at(0))
            ->method('setUserValue')
            ->with(
                $this->equalTo($this->user),
                $this->equalTo($this->appName),
                $this->equalTo('showAll'),
                $this->equalTo('1')
            );
        $this->settings->expects($this->at(1))
            ->method('setUserValue')
            ->with(
                $this->equalTo($this->user),
                $this->equalTo($this->appName),
                $this->equalTo('compact'),
                $this->equalTo('1')
            );
        $this->settings->expects($this->at(2))
            ->method('setUserValue')
            ->with(
                $this->equalTo($this->user),
                $this->equalTo($this->appName),
                $this->equalTo('preventReadOnScroll'),
                $this->equalTo('0')
            );
        $this->settings->expects($this->at(3))
            ->method('setUserValue')
            ->with(
                $this->equalTo($this->user),
                $this->equalTo($this->appName),
                $this->equalTo('oldestFirst'),
                $this->equalTo('1')
            );
        $this->settings->expects($this->at(4))
            ->method('setUserValue')
            ->with(
                $this->equalTo($this->user),
                $this->equalTo($this->appName),
                $this->equalTo('compactExpand'),
                $this->equalTo('1')
            );
        $this->controller->updateSettings(true, true, false, true, true);

    }


    public function testExplore()
    {
        $in = 'test';
        $this->settings->expects($this->at(0))
            ->method('setUserValue')
            ->with(
                $this->equalTo($this->user),
                $this->equalTo($this->appName),
                $this->equalTo('lastViewedFeedId'),
                $this->equalTo(0)
            );

        $this->settings->expects($this->at(1))
            ->method('setUserValue')
            ->with(
                $this->equalTo($this->user),
                $this->equalTo($this->appName),
                $this->equalTo('lastViewedFeedType'),
                $this->equalTo(FeedType::EXPLORE)
            );

        $this->recommended->expects($this->once())
            ->method('forLanguage')
            ->with($this->equalTo('en'))
            ->will($this->returnValue($in));


        $out = $this->controller->explore('en');

        $this->assertEquals($in, $out);
    }

}
