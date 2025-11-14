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
use OCP\IL10N;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\IUserSession;
use OCP\AppFramework\Services\IInitialState;
use OCP\Config\IUserConfig;
use PHPUnit\Framework\TestCase;

class PageControllerTest extends TestCase
{

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|IAppConfig
     */
    private $settings;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|IUserConfig
     */
    private $userConfig;

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
     * @var \PHPUnit\Framework\MockObject\MockObject|IInitialState
     */
    private $initialState;

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
        $this->userConfig = $this->getMockBuilder(IUserConfig::class)
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
        $this->initialState = $this->getMockBuilder(IInitialState::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->controller = new PageController(
            $this->request,
            $this->userSession,
            $this->settings,
            $this->userConfig,
            $this->urlGenerator,
            $this->l10n,
            $this->recommended,
            $this->status,
            $this->initialState
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

    public function testExplore()
    {
        $in = ['test'];
        $setUserValueCalls = [
            ['becka', 'news', 'lastViewedFeedId', 0, false, 0],
            ['becka', 'news', 'lastViewedFeedType', ListType::EXPLORE, false, 0]
        ];
        $setUserValueIndex = 0;

        $this->userConfig->expects($this->exactly(2))
                    ->method('setValueInt')
                    ->willReturnCallback(function (...$args) use (&$setUserValueCalls, &$setUserValueIndex) {
                        $this->assertEquals($setUserValueCalls[$setUserValueIndex], $args);
                        $setUserValueIndex++;
                        return true;
                    });

        $this->recommended->expects($this->once())
                        ->method('forLanguage')
                        ->with('en')
                        ->will($this->returnValue($in));

        $out = $this->controller->explore('en');

        $this->assertEquals($in, $out);
    }

    public function testExploreError()
    {
        $setUserValueCalls = [
            ['becka', 'news', 'lastViewedFeedId', 0, false, 0],
            ['becka', 'news', 'lastViewedFeedType', ListType::EXPLORE, false, 0]
        ];
        $setUserValueIndex = 0;

        $this->userConfig->expects($this->exactly(2))
                    ->method('setValueInt')
                    ->willReturnCallback(function (...$args) use (&$setUserValueCalls, &$setUserValueIndex) {
                        $this->assertEquals($setUserValueCalls[$setUserValueIndex], $args);
                        $setUserValueIndex++;
                        return true;
                    });

        $this->recommended->expects($this->once())
                        ->method('forLanguage')
                        ->with('nl')
                        ->will($this->throwException(new RecommendedSiteNotFoundException('error')));

        $out = $this->controller->explore('nl');

        $this->assertEquals(404, $out->getStatus());
    }
}
