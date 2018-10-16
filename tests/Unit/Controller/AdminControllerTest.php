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
use OCA\News\Controller\AdminController;
use OCA\News\Service\ItemService;
use OCP\IRequest;
use PHPUnit\Framework\TestCase;

class AdminControllerTest extends TestCase
{

    private $appName;
    private $request;
    private $controller;
    private $config;
    private $configPath;
    private $itemService;

    /**
     * Gets run before each test
     */
    public function setUp()
    {
        $this->appName = 'news';
        $this->request = $this->getMockBuilder(IRequest::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemService = $this->getMockBuilder(ItemService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configPath = 'my.ini';
        $this->controller = new AdminController(
            $this->appName, $this->request,
            $this->config, $this->itemService, $this->configPath
        );
    }


    public function testIndex() 
    {
        $expected = [
            'autoPurgeMinimumInterval' => 1,
            'autoPurgeCount' => 2,
            'maxRedirects' => 3,
            'feedFetcherTimeout' => 4,
            'useCronUpdates' => 5,
            'maxSize' => 7,
            'exploreUrl' => 'test'
        ];
        $this->config->expects($this->once())
            ->method('getAutoPurgeMinimumInterval')
            ->will($this->returnValue($expected['autoPurgeMinimumInterval']));
        $this->config->expects($this->once())
            ->method('getAutoPurgeCount')
            ->will($this->returnValue($expected['autoPurgeCount']));
        $this->config->expects($this->once())
            ->method('getMaxRedirects')
            ->will($this->returnValue($expected['maxRedirects']));
        $this->config->expects($this->once())
            ->method('getFeedFetcherTimeout')
            ->will($this->returnValue($expected['feedFetcherTimeout']));
        $this->config->expects($this->once())
            ->method('getUseCronUpdates')
            ->will($this->returnValue($expected['useCronUpdates']));
        $this->config->expects($this->once())
            ->method('getMaxSize')
            ->will($this->returnValue($expected['maxSize']));
        $this->config->expects($this->once())
            ->method('getExploreUrl')
            ->will($this->returnValue($expected['exploreUrl']));

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
            'useCronUpdates' => 5,
            'maxSize' => 7,
            'exploreUrl' => 'test'
        ];

        $this->config->expects($this->once())
            ->method('setAutoPurgeMinimumInterval')
            ->with($this->equalTo($expected['autoPurgeMinimumInterval']));
        $this->config->expects($this->once())
            ->method('setAutoPurgeCount')
            ->with($this->equalTo($expected['autoPurgeCount']));
        $this->config->expects($this->once())
            ->method('setMaxRedirects')
            ->with($this->equalTo($expected['maxRedirects']));
        $this->config->expects($this->once())
            ->method('setFeedFetcherTimeout')
            ->with($this->equalTo($expected['feedFetcherTimeout']));
        $this->config->expects($this->once())
            ->method('setUseCronUpdates')
            ->with($this->equalTo($expected['useCronUpdates']));
        $this->config->expects($this->once())
            ->method('setExploreUrl')
            ->with($this->equalTo($expected['exploreUrl']));
        $this->config->expects($this->once())
            ->method('write')
            ->with($this->equalTo($this->configPath));

        $this->config->expects($this->once())
            ->method('getAutoPurgeMinimumInterval')
            ->will($this->returnValue($expected['autoPurgeMinimumInterval']));
        $this->config->expects($this->once())
            ->method('getAutoPurgeCount')
            ->will($this->returnValue($expected['autoPurgeCount']));
        $this->config->expects($this->once())
            ->method('getMaxRedirects')
            ->will($this->returnValue($expected['maxRedirects']));
        $this->config->expects($this->once())
            ->method('getFeedFetcherTimeout')
            ->will($this->returnValue($expected['feedFetcherTimeout']));
        $this->config->expects($this->once())
            ->method('getUseCronUpdates')
            ->will($this->returnValue($expected['useCronUpdates']));
        $this->config->expects($this->once())
            ->method('getMaxSize')
            ->will($this->returnValue($expected['maxSize']));
        $this->config->expects($this->once())
            ->method('getExploreUrl')
            ->will($this->returnValue($expected['exploreUrl']));

        $response = $this->controller->update(
            $expected['autoPurgeMinimumInterval'],
            $expected['autoPurgeCount'],
            $expected['maxRedirects'],
            $expected['feedFetcherTimeout'],
            $expected['maxSize'],
            $expected['useCronUpdates'],
            $expected['exploreUrl']
        );

        $this->assertEquals($expected, $response);
    }

}
