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

namespace OCA\News\Tests\Unit\Service;

use OCA\News\Service\StatusService;
use OCP\IConfig;
use OCP\IDBConnection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;


class StatusServiceTest extends TestCase
{
    /**
     * @var MockObject|IConfig
     */
    private $settings;

    /**
     * @var MockObject|IDBConnection
     */
    private $connection;

    /**
     * @var StatusService
     */
    private $service;

    public function setUp(): void
    {
        $this->settings = $this->getMockBuilder(IConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->connection = $this->getMockBuilder(IDBConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->service = new StatusService($this->settings, $this->connection);
    }

    public function testGetStatus()
    {
        $this->settings->expects($this->exactly(3))
            ->method('getAppValue')
            ->withConsecutive(
                ['news', 'installed_version'],
                ['core', 'backgroundjobs_mode'],
                ['news', 'useCronUpdates']
            )
            ->will($this->returnValueMap([
                ['news', 'installed_version', '', '1.0'],
                ['core', 'backgroundjobs_mode', '', 'cron'],
                ['news', 'useCronUpdates', (string)true, (string)true],
            ]));

        $this->connection->expects($this->exactly(1))
            ->method('supports4ByteText')
            ->will($this->returnValue(true));

        $expected = [
            'version'  => '1.0',
            'warnings' => [
                'improperlyConfiguredCron' => false,
                'incorrectDbCharset'       => false,
            ],
        ];
        $response = $this->service->getStatus();
        $this->assertEquals($expected, $response);
    }

    public function testGetStatusNoCorrectCronAjax()
    {
        $this->settings->expects($this->exactly(3))
            ->method('getAppValue')
            ->withConsecutive(
                ['news', 'installed_version'],
                ['core', 'backgroundjobs_mode'],
                ['news', 'useCronUpdates']
            )
            ->will($this->returnValueMap([
                ['news', 'installed_version', '', '1.0'],
                ['core', 'backgroundjobs_mode', '', 'ajax'],
                ['news', 'useCronUpdates', true, true],
            ]));

        $this->connection->expects($this->exactly(1))
            ->method('supports4ByteText')
            ->will($this->returnValue(true));

        $expected = [
            'version'  => '1.0',
            'warnings' => [
                'improperlyConfiguredCron' => true,
                'incorrectDbCharset'       => false,
            ],
        ];
        $response = $this->service->getStatus();
        $this->assertEquals($expected, $response);
    }

    public function testGetStatusNoCorrectCronTurnedOff()
    {
        $this->settings->expects($this->exactly(3))
            ->method('getAppValue')
            ->withConsecutive(
                ['news', 'installed_version'],
                ['core', 'backgroundjobs_mode'],
                ['news', 'useCronUpdates']
            )
            ->will($this->returnValueMap([
                ['news', 'installed_version', '', '1.0'],
                ['core', 'backgroundjobs_mode', '', 'ajax'],
                ['news', 'useCronUpdates', true, false],
            ]));

        $this->connection->expects($this->exactly(1))
            ->method('supports4ByteText')
            ->will($this->returnValue(true));

        $expected = [
            'version'  => '1.0',
            'warnings' => [
                'improperlyConfiguredCron' => false,
                'incorrectDbCharset'       => false,
            ],
        ];
        $response = $this->service->getStatus();
        $this->assertEquals($expected, $response);
    }

    public function testGetStatusReportsNon4ByteText()
    {
        $this->settings->expects($this->exactly(3))
            ->method('getAppValue')
            ->withConsecutive(
                ['news', 'installed_version'],
                ['core', 'backgroundjobs_mode'],
                ['news', 'useCronUpdates']
            )
            ->will($this->returnValueMap([
                ['news', 'installed_version', '', '1.0'],
                ['core', 'backgroundjobs_mode', '', 'ajax'],
                ['news', 'useCronUpdates', true, false],
            ]));

        $this->connection->expects($this->exactly(1))
            ->method('supports4ByteText')
            ->will($this->returnValue(false));

        $expected = [
            'version'  => '1.0',
            'warnings' => [
                'improperlyConfiguredCron' => false,
                'incorrectDbCharset'       => true,
            ],
        ];
        $response = $this->service->getStatus();
        $this->assertEquals($expected, $response);
    }

    public function testIsProperlyConfiguredNone()
    {
        $this->settings->expects($this->exactly(2))
            ->method('getAppValue')
            ->withConsecutive(
                ['core', 'backgroundjobs_mode'],
                ['news', 'useCronUpdates']
            )
            ->will($this->returnValueMap([
                ['core', 'backgroundjobs_mode', '', 'ajax'],
                ['news', 'useCronUpdates', true, true],
            ]));

        $response = $this->service->isCronProperlyConfigured();
        $this->assertFalse($response);
    }

    public function testIsProperlyConfiguredModeCronNoSystem()
    {
        $this->settings->expects($this->exactly(2))
            ->method('getAppValue')
            ->withConsecutive(
                ['core', 'backgroundjobs_mode'],
                ['news', 'useCronUpdates']
            )
            ->will($this->returnValueMap([
                ['core', 'backgroundjobs_mode', '', 'cron'],
                ['news', 'useCronUpdates', true, false],
            ]));

        $response = $this->service->isCronProperlyConfigured();
        $this->assertTrue($response);
    }

    public function testIsProperlyConfiguredModeCron()
    {
        $this->settings->expects($this->exactly(2))
            ->method('getAppValue')
            ->withConsecutive(
                ['core', 'backgroundjobs_mode'],
                ['news', 'useCronUpdates']
            )
            ->will($this->returnValueMap([
                ['core', 'backgroundjobs_mode', '', 'cron'],
                ['news', 'useCronUpdates', true, false],
            ]));

        $response = $this->service->isCronProperlyConfigured();
        $this->assertTrue($response);
    }

}