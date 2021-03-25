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

use OCA\News\Command\Debug\ItemList;
use OCA\News\Command\Updater\UpdateFeed;
use OCA\News\Config\FetcherConfig;
use OCA\News\Fetcher\Client\FeedIoClient;
use OCA\News\Service\ItemServiceV2;
use OCP\IConfig;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class FetcherConfigTest
 *
 * TODO: Improve this
 *
 * @package OCA\News\Tests\Config
 */
class FetcherConfigTest extends TestCase
{
    /** @var MockObject|IConfig */
    protected $config;

    /** @var FetcherConfig */
    protected $class;

    protected function setUp(): void
    {
        $this->config = $this->getMockBuilder(IConfig::class)
                              ->disableOriginalConstructor()
                              ->getMock();
    }

    /**
     * Test a valid call will work
     */
    public function testGetClient()
    {
        $this->class = new FetcherConfig($this->config);

        $this->assertInstanceOf(FeedIoClient::class, $this->class->getClient());
    }
}
