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

use OC\L10N\L10N;
use OCA\News\Controller\PageController;
use \OCA\News\Db\ListType;
use OCA\News\Explore\Exceptions\RecommendedSiteNotFoundException;
use OCA\News\Explore\RecommendedSites;
use OCA\News\Service\StatusService;
use OCP\IAppConfig;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\IUserSession;
use PHPUnit\Framework\TestCase;

class PageControllerTest extends TestCase
{

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|IAppConfig
     */
    private $settings;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|IConfig
     */
    private $config;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|IRequest
     */
    private $request;

    /**
     * @var PageController
     */
    private $controller;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|L10N
     */
    private $l10n;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|IURLGenerator
     */
    private $urlGenerator;

    /**
     * @var array
     */
    private $configData;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|RecommendedSites
     */
    private $recommended;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|StatusService
     */
    private $status;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|IUser
     */
    private $user;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|IUserSession
     */
    private $userSession;

    /**
     * Gets run before each test
     */
    public function setUp(): void
    {
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
        $this->settings = $this->getMockBuilder(IAppConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->config = $this->getMockBuilder(IConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockBuilder(IRequest::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlGenerator = $this->getMockBuilder(IURLGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->recommended = $this->getMockBuilder(RecommendedSites::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->status = $this->getMockBuilder(StatusService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->user = $this->getMockBuilder(IUser::class)->getMock();
        $this->user->expects($this->any())
            ->method('getUID')
            ->will($this->returnValue('becka'));
        $this->userSession = $this->getMockBuilder(IUserSession::class)
            ->getMock();
        $this->userSession->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($this->user));
        $this->controller = new PageController(
            $this->request,
            $this->userSession,
            $this->settings,
            $this->config,
            $this->urlGenerator,
            $this->l10n,
            $this->recommended,
            $this->status
        );
    }


    public function testIndex()
    {
        $this->status->expects($this->once())
            ->method('getStatus')
            ->will($this->returnValue(['warnings' => ['improperlyConfiguredCron' => false]]));

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

    /**
     * @covers \OCA\News\Controller\PageController::settings
     */
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

        $this->config->expects($this->exactly(5))
                     ->method('getUserValue')
                     ->withConsecutive(
                        ['becka', 'news', 'showAll'],
                        ['becka', 'news', 'compact'],
                        ['becka', 'news', 'preventReadOnScroll'],
                        ['becka', 'news', 'oldestFirst'],
                        ['becka', 'news', 'compactExpand']
                     )
                     ->will($this->returnValue('1'));
        $this->settings->expects($this->once())
                       ->method('getValueString')
                       ->with('news', 'exploreUrl')
                       ->will($this->returnValue(' '));
        $this->urlGenerator->expects($this->once())
                           ->method('linkToRoute')
                           ->with('news.page.explore', ['lang' => 'en'])
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

        $this->config->expects($this->exactly(5))
                    ->method('getUserValue')
                    ->withConsecutive(
                        ['becka', 'news', 'showAll'],
                        ['becka', 'news', 'compact'],
                        ['becka', 'news', 'preventReadOnScroll'],
                        ['becka', 'news', 'oldestFirst'],
                        ['becka', 'news', 'compactExpand']
                    )
                    ->will($this->returnValue('1'));
        $this->settings->expects($this->once())
                        ->method('getValueString')
                        ->with('news', 'exploreUrl')
                        ->will($this->returnValue('abc'));
        $this->urlGenerator->expects($this->never())
            ->method('getAbsoluteURL');


        $response = $this->controller->settings();
        $this->assertEquals($result, $response);
    }

    /**
     * @covers \OCA\News\Controller\PageController::updateSettings
     */
    public function testUpdateSettings()
    {
        $this->config->expects($this->exactly(5))
                    ->method('setUserValue')
                    ->withConsecutive(
                        ['becka', 'news', 'showAll', '1'],
                        ['becka', 'news', 'compact', '1'],
                        ['becka', 'news', 'preventReadOnScroll', '0'],
                        ['becka', 'news', 'oldestFirst', '1'],
                        ['becka', 'news', 'compactExpand', '1']
                    );

        $this->controller->updateSettings(true, true, false, true, true);
    }

    public function testExplore()
    {
        $in = ['test'];
        $this->config->expects($this->exactly(2))
                    ->method('setUserValue')
                    ->withConsecutive(
                        ['becka', 'news', 'lastViewedFeedId', 0],
                        ['becka', 'news', 'lastViewedFeedType', ListType::EXPLORE]
                    );

        $this->recommended->expects($this->once())
                        ->method('forLanguage')
                        ->with('en')
                        ->will($this->returnValue($in));

        $out = $this->controller->explore('en');

        $this->assertEquals($in, $out);
    }

    public function testExploreError()
    {
        $this->config->expects($this->exactly(2))
                    ->method('setUserValue')
                    ->withConsecutive(
                        ['becka', 'news', 'lastViewedFeedId', 0],
                        ['becka', 'news', 'lastViewedFeedType', ListType::EXPLORE]
                    );

        $this->recommended->expects($this->once())
                        ->method('forLanguage')
                        ->with('nl')
                        ->will($this->throwException(new RecommendedSiteNotFoundException('error')));

        $out = $this->controller->explore('nl');

        $this->assertEquals(404, $out->getStatus());
    }
}
