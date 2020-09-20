<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Alessandro Cosentino <cosenal@gmail.com>
 * @author    Bernhard Posselt <dev@bernhard-posselt.com>
 * @author    David Guillot <david@guillot.me>
 * @copyright 2012 Alessandro Cosentino
 * @copyright 2012-2014 Bernhard Posselt
 * @copyright 2018 David Guillot
 */

namespace OCA\News\Tests\Unit\Controller;

use OCA\News\Controller\UtilityApiController;
use OCA\News\Service\StatusService;
use OCA\News\Utility\Updater;
use OCP\IConfig;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;

use PHPUnit\Framework\TestCase;

class UtilityApiControllerTest extends TestCase
{

    private $settings;
    private $request;
    private $userSession;
    private $user;
    private $newsAPI;
    private $updater;
    private $appName;
    private $status;

    protected function setUp(): void
    {
        $this->appName = 'news';
        $this->settings = $this->getMockBuilder(IConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockBuilder(IRequest::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->userSession = $this->getMockBuilder(IUserSession::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->user = $this->getMockBuilder(IUser::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->userSession->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($this->user));
        $this->updater = $this->getMockBuilder(Updater::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->status = $this->getMockBuilder(StatusService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->newsAPI = new UtilityApiController(
            $this->appName, $this->request, $this->userSession,
            $this->updater, $this->settings, $this->status
        );
    }


    public function testGetVersion()
    {
        $this->settings->expects($this->once())
            ->method('getAppValue')
            ->with(
                $this->equalTo($this->appName),
                $this->equalTo('installed_version')
            )
            ->will($this->returnValue('1.0'));

        $response = $this->newsAPI->version();
        $version = $response['version'];

        $this->assertEquals('1.0', $version);
    }


    public function testBeforeUpdate()
    {
        $this->updater->expects($this->once())
            ->method('beforeUpdate');
        $this->newsAPI->beforeUpdate();
    }


    public function testAfterUpdate()
    {
        $this->updater->expects($this->once())
            ->method('afterUpdate');
        $this->newsAPI->afterUpdate();
    }


    public function testStatus()
    {
        $in = 'hi';
        $this->status->expects($this->once())
            ->method('getStatus')
            ->will($this->returnValue($in));
        $result = $this->newsAPI->status();

        $this->assertEquals($in, $result);
    }


}
