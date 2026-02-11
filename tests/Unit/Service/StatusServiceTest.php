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
use OCP\IAppConfig;
use OCP\IDBConnection;
use OCP\BackgroundJob\IJobList;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StatusServiceTest extends TestCase
{
    /**
     * @var MockObject|IAppConfig
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

    /**
     * @var IJobList
     */
    private $jobList;


    public function setUp(): void
    {
        $this->settings = $this->getMockBuilder(IAppConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->connection = $this->getMockBuilder(IDBConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->jobList = $this->getMockBuilder(IJobList::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->service = new StatusService($this->settings, $this->connection, $this->jobList);
    }

    public function testGetStatus()
    {
        $this->settings->expects($this->exactly(2))
            ->method('getValueString')
            ->will($this->returnValueMap([
                ['news', 'installed_version', '', false, '1.0'],
                ['core', 'backgroundjobs_mode', '', false, 'cron'],
            ]));

        $this->settings->expects($this->exactly(1))
             ->method('getValueBool')
             ->will($this->returnValueMap([
                ['news', 'useCronUpdates', true, false, true],
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
        $getValueStringCalls = [
            ['news', 'installed_version', '', false],
            ['core', 'backgroundjobs_mode', '', false],
        ];
        $getValueStringReturns = ['1.0', 'ajax'];
        $getValueStringIndex = 0;

        $this->settings->expects($this->exactly(2))
            ->method('getValueString')
            ->willReturnCallback(function (...$args) use (&$getValueStringCalls, &$getValueStringReturns, &$getValueStringIndex) {
                $this->assertEquals($getValueStringCalls[$getValueStringIndex], $args);
                return $getValueStringReturns[$getValueStringIndex++];
            });

        $this->settings->expects($this->exactly(1))
             ->method('getValueBool')
             ->with('news', 'useCronUpdates', true, false)
             ->willReturn(true);

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
        $this->settings->expects($this->exactly(2))
            ->method('getValueString')
            ->will($this->returnValueMap([
                ['news', 'installed_version', '', false, '1.0'],
                ['core', 'backgroundjobs_mode', '', false, 'cron'],
            ]));


        $this->settings->expects($this->exactly(1))
                       ->method('getValueBool')
                       ->will($this->returnValueMap([['news', 'useCronUpdates', true, false, true],]));

        $this->connection->expects($this->exactly(1))
                         ->method('supports4ByteText')
                         ->willReturn(true);

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
        $getValueStringCalls = [
            ['news', 'installed_version', '', false],
            ['core', 'backgroundjobs_mode', '', false],
        ];
        $getValueStringReturns = ['1.0', 'cron'];
        $getValueStringIndex = 0;

        $this->settings->expects($this->exactly(2))
            ->method('getValueString')
            ->willReturnCallback(function (...$args) use (&$getValueStringCalls, &$getValueStringReturns, &$getValueStringIndex) {
                $this->assertEquals($getValueStringCalls[$getValueStringIndex], $args);
                return $getValueStringReturns[$getValueStringIndex++];
            });

        $this->settings->expects($this->exactly(1))
             ->method('getValueBool')
             ->with('news', 'useCronUpdates', true, false)
             ->willReturn(true);

        $this->connection->expects($this->exactly(1))
                         ->method('supports4ByteText')
                         ->willReturn(false);

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
        $this->settings->expects($this->exactly(1))
            ->method('getValueString')
            ->with('core', 'backgroundjobs_mode', '', false)
            ->willReturn('ajax');

        $this->settings->expects($this->exactly(1))
             ->method('getValueBool')
             ->with('news', 'useCronUpdates', true, false)
             ->willReturn(true);

        $response = $this->service->isCronProperlyConfigured();
        $this->assertFalse($response);
    }

    public function testIsProperlyConfiguredModeCronNoSystem()
    {
        $this->settings->expects($this->exactly(1))
            ->method('getValueString')
            ->with('core', 'backgroundjobs_mode', '', false)
            ->willReturn('cron');

        $this->settings->expects($this->exactly(1))
             ->method('getValueBool')
             ->with('news', 'useCronUpdates', true, false)
             ->willReturn(true);

        $response = $this->service->isCronProperlyConfigured();
        $this->assertTrue($response);
    }

    public function testIsProperlyConfiguredModeCron()
    {
        $this->settings->expects($this->exactly(1))
            ->method('getValueString')
            ->with('core', 'backgroundjobs_mode', '', false)
            ->willReturn('cron');

        $this->settings->expects($this->exactly(1))
             ->method('getValueBool')
             ->with('news', 'useCronUpdates', true, false)
             ->willReturn(true);

        $response = $this->service->isCronProperlyConfigured();
        $this->assertTrue($response);
    }
}
