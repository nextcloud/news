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

namespace OCA\News\Config;


class ConfigTest extends \PHPUnit_Framework_TestCase {

    private $fileSystem;
    private $config;
    private $configPath;
    private $loggerParams;

    public function setUp() {
        $this->logger = $this->getMockBuilder(
            '\OCP\ILogger')
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileSystem = $this->getMock('FileSystem', [
            'file_get_contents',
            'file_put_contents',
            'file_exists'
        ]);
        $this->loggerParams = ['hi'];
        $this->config = new Config(
            $this->fileSystem, $this->logger, $this->loggerParams
        );
        $this->configPath = 'config.json';
    }


    public function testDefaults() {
        $this->assertEquals(60, $this->config->getAutoPurgeMinimumInterval());
        $this->assertEquals(200, $this->config->getAutoPurgeCount());
        $this->assertEquals(30*60, $this->config->getCacheDuration());
        $this->assertEquals(60, $this->config->getFeedFetcherTimeout());
        $this->assertEquals(true, $this->config->getUseCronUpdates());
        $this->assertEquals(8080, $this->config->getProxyPort());
        $this->assertEquals('', $this->config->getProxyHost());
        $this->assertEquals(null, $this->config->getProxyAuth());
        $this->assertEquals('', $this->config->getProxyUser());
        $this->assertEquals('', $this->config->getProxyPassword());
    }


    public function testRead () {
        $this->fileSystem->expects($this->once())
            ->method('file_get_contents')
            ->with($this->equalTo($this->configPath))
            ->will($this->returnValue(
                'autoPurgeCount = 3' . "\n" . 'useCronUpdates = true')
            );

        $this->config->read($this->configPath);

        $this->assertSame(3, $this->config->getAutoPurgeCount());
        $this->assertSame(true, $this->config->getUseCronUpdates());
    }


    public function testReadIgnoresVeryLowPurgeInterval () {
        $this->fileSystem->expects($this->once())
            ->method('file_get_contents')
            ->with($this->equalTo($this->configPath))
            ->will($this->returnValue('autoPurgeMinimumInterval = 59'));

        $this->config->read($this->configPath);

        $this->assertSame(60, $this->config->getAutoPurgeMinimumInterval());
    }



    public function testReadBool () {
        $this->fileSystem->expects($this->once())
            ->method('file_get_contents')
            ->with($this->equalTo($this->configPath))
            ->will($this->returnValue(
                'autoPurgeCount = 3' . "\n" . 'useCronUpdates = false')
            );

        $this->config->read($this->configPath);

        $this->assertSame(3, $this->config->getAutoPurgeCount());
        $this->assertSame(false, $this->config->getUseCronUpdates());
    }


    public function testReadLogsInvalidValue() {
        $this->fileSystem->expects($this->once())
            ->method('file_get_contents')
            ->with($this->equalTo($this->configPath))
            ->will($this->returnValue('autoPurgeCounts = 3'));
        $this->logger->expects($this->once())
            ->method('warning')
            ->with($this->equalTo('Configuration value "autoPurgeCounts" ' .
                'does not exist. Ignored value.'),
                $this->equalTo($this->loggerParams));

        $this->config->read($this->configPath);
    }


    public function testReadLogsInvalidINI() {
        $this->fileSystem->expects($this->once())
            ->method('file_get_contents')
            ->with($this->equalTo($this->configPath))
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
            'cacheDuration = 1800' . "\n" .
            'feedFetcherTimeout = 60' . "\n" .
            'useCronUpdates = true' . "\n" .
            'proxyHost = yo man' . "\n" .
            'proxyPort = 12' . "\n" .
            'proxyUser = this is a test' . "\n".
            'proxyPassword = se';
        $this->config->setAutoPurgeCount(3);
        $this->config->setProxyHost('yo man');
        $this->config->setProxyPort(12);
        $this->config->setProxyUser('this is a test');
        $this->config->setProxyPassword('se');

        $this->fileSystem->expects($this->once())
            ->method('file_put_contents')
            ->with($this->equalTo($this->configPath),
                $this->equalTo($json));

        $this->config->write($this->configPath);
    }


    public function testNoProxyAuthReturnsNull() {
        $this->assertNull($this->config->getProxyAuth());
    }


    public function testReadingNonExistentConfigWillWriteDefaults() {
        $this->fileSystem->expects($this->once())
            ->method('file_exists')
            ->with($this->equalTo($this->configPath))
            ->will($this->returnValue(false));

        $this->config->setUseCronUpdates(false);

        $json = 'autoPurgeMinimumInterval = 60' . "\n" .
            'autoPurgeCount = 200' . "\n" .
            'cacheDuration = 1800' . "\n" .
            'feedFetcherTimeout = 60' . "\n" .
            'useCronUpdates = false' . "\n" .
            'proxyHost = ' . "\n" .
            'proxyPort = 8080' . "\n" .
            'proxyUser = ' . "\n" .
            'proxyPassword = ';

        $this->fileSystem->expects($this->once())
            ->method('file_put_contents')
            ->with($this->equalTo($this->configPath),
                $this->equalTo($json));

        $this->config->read($this->configPath, true);
    }


    public function testEncodesUserAndPasswordInHTTPBasicAuth() {
        $this->config->setProxyUser('this is a test');
        $this->config->setProxyPassword('se');

        $this->assertEquals('this is a test:se', $this->config->getProxyAuth());
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

    public function testCacheDuration() {
        $this->config->setCacheDuration(21);
        $duration = $this->config->getCacheDuration();

        $this->assertSame(21, $duration);
    }

    public function testFeedFetcherTimeout() {
        $this->config->setFeedFetcherTimeout(2);
        $timout = $this->config->getFeedFetcherTimeout();

        $this->assertSame(2, $timout);
    }
}