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

use OCA\News\Controller\AdminController;
use OCA\News\Service\ItemService;
use OCP\IConfig;
use OCP\IRequest;
use PHPUnit\Framework\TestCase;

class AdminControllerTest extends TestCase
{
    /**
     * @var string
     */
    private $appName;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|IRequest
     */
    private $request;

    /**
     * @var AdminController
     */
    private $controller;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|IConfig
     */
    private $config;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ItemService
     */
    private $itemService;

    /**
     * Gets run before each test
     */
    public function setUp(): void
    {
        $this->appName = 'news';
        $this->request = $this->getMockBuilder(IRequest::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->config = $this->getMockBuilder(IConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemService = $this->getMockBuilder(ItemService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = new AdminController($this->appName, $this->request, $this->config, $this->itemService);
    }

    /**
     * Test \OCA\News\Controller\AdminController::index
     */
    public function testIndex()
    {
        $expected = [
            'autoPurgeMinimumInterval' => 1,
            'autoPurgeCount' => 2,
            'maxRedirects' => 3,
            'feedFetcherTimeout' => 4,
            'useCronUpdates' => false,
            'exploreUrl' => 'test',
            'updateInterval' => 3601
        ];
        $map = [
            ['news','autoPurgeMinimumInterval', 60, 1],
            ['news','autoPurgeCount', 200, 2],
            ['news','maxRedirects', 10, 3],
            ['news','feedFetcherTimeout', 60, 4],
            ['news','useCronUpdates', true, false,],
            ['news','exploreUrl', '', 'test'],
            ['news','updateInterval', 3600, 3601]
        ];
        $this->config->expects($this->exactly(count($map)))
            ->method('getAppValue')
            ->will($this->returnValueMap($map));

        $response = $this->controller->index();
        $data = $response->getParams();
        $name = $response->getTemplateName();
        $type = $response->getRenderAs();

        $this->assertEquals($type, 'blank');
        $this->assertEquals($name, 'admin');
        $this->assertEquals($expected, $data);
    }


    public function testUpdate()
    {
        $expected = [
            'autoPurgeMinimumInterval' => 1,
            'autoPurgeCount' => 2,
            'maxRedirects' => 3,
            'feedFetcherTimeout' => 4,
            'useCronUpdates' => false,
            'exploreUrl' => 'test',
            'updateInterval' => 3601
        ];

        $this->config->expects($this->exactly(count($expected)))
            ->method('setAppValue')
            ->withConsecutive(
                ['news','autoPurgeMinimumInterval', 1],
                ['news','autoPurgeCount', 2],
                ['news','maxRedirects', 3],
                ['news','feedFetcherTimeout', 4],
                ['news','useCronUpdates', false],
                ['news','exploreUrl', 'test'],
                ['news','updateInterval', 3601]
            );

        $map = [
            ['news','autoPurgeMinimumInterval', 60, 1],
            ['news','autoPurgeCount', 200, 2],
            ['news','maxRedirects', 10, 3],
            ['news','feedFetcherTimeout', 60, 4],
            ['news','useCronUpdates', true, false,],
            ['news','exploreUrl', '', 'test'],
            ['news','updateInterval', 3600, 3601]
        ];
        $this->config->expects($this->exactly(count($map)))
            ->method('getAppValue')
            ->will($this->returnValueMap($map));

        $response = $this->controller->update(
            $expected['autoPurgeMinimumInterval'],
            $expected['autoPurgeCount'],
            $expected['maxRedirects'],
            $expected['feedFetcherTimeout'],
            $expected['useCronUpdates'],
            $expected['exploreUrl'],
            $expected['updateInterval']
        );

        $this->assertEquals($expected, $response);
    }

}
