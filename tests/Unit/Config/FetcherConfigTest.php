<?php
/**
 * @author Sean Molenaar <sean@seanmolenaar.eu>
 *
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\News\Tests\Config;

use OCA\News\Config\FetcherConfig;
use OCA\News\Fetcher\Client\FeedIoClient;
use OCP\IAppConfig;
use OCP\IConfig;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class FetcherConfigTest
 *
 * TODO: Improve this
 *
 * @package OCA\News\Tests\Config
 */
class FetcherConfigTest extends TestCase
{
    /** @var MockObject|IAppConfig */
    protected $config;

    /** @var MockObject|IConfig */
    protected $sysconfig;

    /** @var FetcherConfig */
    protected $class;

    protected function setUp(): void
    {
        $this->config = $this->getMockBuilder(IAppConfig::class)
                              ->disableOriginalConstructor()
                              ->getMock();

        $this->sysconfig = $this->getMockBuilder(IConfig::class)
                                ->disableOriginalConstructor()
                                ->getMock();
    }

    /**
     * Test a valid call will work
     */
    public function testGetClient()
    {
        $this->class = new FetcherConfig($this->config, $this->sysconfig);

        $this->assertInstanceOf(FeedIoClient::class, $this->class->getClient());
    }

    public function testGetUserAgent()
    {
        $this->config->expects($this->exactly(3))
            ->method('getAppValue')
            ->willReturnMap([
                ['news', 'feedFetcherTimeout', 60, '60'],
                ['news', 'maxRedirects', 10, '10'],
                ['news', 'installed_version', '1.0', '123.45']
            ]);
        $this->class = new FetcherConfig($this->config);

        $expected = 'NextCloud-News/123.45';
        $response = $this->class->getUserAgent();
        $this->assertEquals($expected, $response);
    }

    public function testGetUserAgentUnknownVersion()
    {
        $this->config->expects($this->exactly(3))
            ->method('getAppValue')
            ->willReturnMap([
                ['news', 'feedFetcherTimeout', 60, '60'],
                ['news', 'maxRedirects', 10, '10']
            ]);
        $this->class = new FetcherConfig($this->config);

        $expected = 'NextCloud-News/1.0';
        $response = $this->class->getUserAgent();
        $this->assertEquals($expected, $response);
    }
}
