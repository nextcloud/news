<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Alessandro Cosentino <cosenal@gmail.com>
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Alessandro Cosentino 2012
 * @copyright Bernhard Posselt 2012, 2014
 */

namespace OCA\News\Tests\Unit\Config;

use OCA\News\Config\Config;
use PHPUnit_Framework_TestCase;


class ConfigTest extends PHPUnit_Framework_TestCase {

    private $fileSystem;
    private $config;
    private $configPath;
    private $loggerParams;

    public function setUp() {
        $this->logger = $this->getMockBuilder(
            'OCP\ILogger')
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileSystem = $this->getMockBuilder('OCP\Files\Folder')->getMock();
        $this->loggerParams = ['hi'];
        $this->config = new Config(
            $this->fileSystem, $this->logger, $this->loggerParams
        );
        $this->configPath = 'config.json';
    }


    public function testDefaults() {
        $this->assertEquals(60, $this->config->getAutoPurgeMinimumInterval());
        $this->assertEquals(200, $this->config->getAutoPurgeCount());
        $this->assertEquals(10, $this->config->getMaxRedirects());
        $this->assertEquals(60, $this->config->getFeedFetcherTimeout());
        $this->assertEquals(true, $this->config->getUseCronUpdates());
        $this->assertEquals('', $this->config->getExploreUrl());
        $this->assertEquals(1024*1024*100, $this->config->getMaxSize());
    }


    public function testRead () {
        $file = $this->getMockBuilder('OCP\Files\File')->getMock();
        $this->fileSystem->expects($this->once())
            ->method('get')
            ->with($this->equalTo($this->configPath))
            ->will($this->returnValue($file));
        $file->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue(
                'autoPurgeCount = 3' . "\n" . 'useCronUpdates = true'
            ));


        $this->config->read($this->configPath);

        $this->assertSame(3, $this->config->getAutoPurgeCount());
        $this->assertSame(true, $this->config->getUseCronUpdates());
    }


    public function testReadIgnoresVeryLowPurgeInterval () {
        $file = $this->getMockBuilder('OCP\Files\File')->getMock();
        $this->fileSystem->expects($this->once())
            ->method('get')
            ->with($this->equalTo($this->configPath))
            ->will($this->returnValue($file));
        $file->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue('autoPurgeMinimumInterval = 59'));

        $this->config->read($this->configPath);

        $this->assertSame(60, $this->config->getAutoPurgeMinimumInterval());
    }



    public function testReadBool () {
        $file = $this->getMockBuilder('OCP\Files\File')->getMock();
        $this->fileSystem->expects($this->once())
            ->method('get')
            ->with($this->equalTo($this->configPath))
            ->will($this->returnValue($file));
        $file->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue(
                'autoPurgeCount = 3' . "\n" . 'useCronUpdates = false')
            );

        $this->config->read($this->configPath);

        $this->assertSame(3, $this->config->getAutoPurgeCount());
        $this->assertSame(false, $this->config->getUseCronUpdates());
    }


    public function testReadLogsInvalidValue() {
        $file = $this->getMockBuilder('OCP\Files\File')->getMock();
        $this->fileSystem->expects($this->once())
            ->method('get')
            ->with($this->equalTo($this->configPath))
            ->will($this->returnValue($file));
        $file->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue('autoPurgeCounts = 3'));
        $this->logger->expects($this->once())
            ->method('warning')
            ->with($this->equalTo('Configuration value "autoPurgeCounts" ' .
                'does not exist. Ignored value.'),
                $this->equalTo($this->loggerParams));

        $this->config->read($this->configPath);
    }


    public function testReadLogsInvalidINI() {
        $file = $this->getMockBuilder('OCP\Files\File')->getMock();
        $this->fileSystem->expects($this->once())
            ->method('get')
            ->with($this->equalTo($this->configPath))
            ->will($this->returnValue($file));
        $file->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue(''));
        $this->logger->expects($this->once())
            ->method('warning')
            ->with($this->equalTo('Configuration invalid. Ignoring values.'),
                $this->equalTo($this->loggerParams));

        $this->config->read($this->configPath);
    }


    public function testWrite () {
        $json = 'autoPurgeMinimumInterval = 60' . "\n" .
            'autoPurgeCount = 3' . "\n" .
            'maxRedirects = 10' . "\n" .
            'maxSize = 399' . "\n" .
            'exploreUrl = http://google.de' . "\n" .
            'feedFetcherTimeout = 60' . "\n" .
            'useCronUpdates = true';
        $this->config->setAutoPurgeCount(3);
        $this->config->setMaxSize(399);
        $this->config->setExploreUrl('http://google.de');

        $file = $this->getMockBuilder('OCP\Files\File')->getMock();
        $this->fileSystem->expects($this->once())
            ->method('get')
            ->with($this->equalTo($this->configPath))
            ->will($this->returnValue($file));
        $file->expects($this->once())
            ->method('putContent')
            ->with($this->equalTo($json));

        $this->config->write($this->configPath);
    }



    public function testReadingNonExistentConfigWillWriteDefaults() {
        $this->fileSystem->expects($this->once())
            ->method('nodeExists')
            ->with($this->equalTo($this->configPath))
            ->will($this->returnValue(false));

        $this->config->setUseCronUpdates(false);

        $json = 'autoPurgeMinimumInterval = 60' . "\n" .
            'autoPurgeCount = 200' . "\n" .
            'maxRedirects = 10' . "\n" .
            'maxSize = 104857600' . "\n" .
            'exploreUrl = ' . "\n" .
            'feedFetcherTimeout = 60' . "\n" .
            'useCronUpdates = false';

        $this->fileSystem->expects($this->once())
            ->method('newFile')
            ->with($this->equalTo($this->configPath));
        $file = $this->getMockBuilder('OCP\Files\File')->getMock();
        $this->fileSystem->expects($this->once())
            ->method('get')
            ->with($this->equalTo($this->configPath))
            ->will($this->returnValue($file));
        $file->expects($this->once())
            ->method('putContent')
            ->with($this->equalTo($json));

        $this->config->read($this->configPath, true);
    }


    public function testNoLowMinimumAutoPurgeInterval() {
        $this->config->setAutoPurgeMinimumInterval(59);
        $interval = $this->config->getAutoPurgeMinimumInterval();

        $this->assertSame(60, $interval);
    }


    public function testMinimumAutoPurgeInterval() {
        $this->config->setAutoPurgeMinimumInterval(61);
        $interval = $this->config->getAutoPurgeMinimumInterval();

        $this->assertSame(61, $interval);
    }

    public function testMaxRedirects() {
        $this->config->setMaxRedirects(21);
        $redirects = $this->config->getMaxRedirects();

        $this->assertSame(21, $redirects);
    }

    public function testFeedFetcherTimeout() {
        $this->config->setFeedFetcherTimeout(2);
        $timout = $this->config->getFeedFetcherTimeout();

        $this->assertSame(2, $timout);
    }
}
