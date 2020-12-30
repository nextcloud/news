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

namespace OCA\News\Tests\Unit\Command;

use OCA\News\Command\Updater\UpdateFeed;
use OCA\News\Db\Feed;
use OCA\News\Service\Exceptions\ServiceNotFoundException;
use OCA\News\Service\FeedServiceV2;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateFeedTest extends TestCase
{
    /** @var MockObject|FeedServiceV2 */
    protected $service;
    /** @var MockObject|InputInterface */
    protected $consoleInput;
    /** @var MockObject|OutputInterface */
    protected $consoleOutput;

    /** @var UpdateFeed */
    protected $command;

    protected function setUp(): void
    {
        $this->service = $this->getMockBuilder(FeedServiceV2::class)
                              ->disableOriginalConstructor()
                              ->getMock();

        $this->consoleInput = $this->getMockBuilder(InputInterface::class)->getMock();
        $this->consoleOutput = $this->getMockBuilder(OutputInterface::class)->getMock();

        $this->command = new UpdateFeed($this->service);
    }

    /**
     * Test a valid call will work
     */
    public function testValid()
    {
        $this->consoleInput->expects($this->exactly(2))
                           ->method('getArgument')
                           ->will($this->returnValueMap([
                               ['feed-id', '1'],
                               ['user-id', 'admin'],
                           ]));

        $feed = $this->createMock(Feed::class);

        $feed->expects($this->exactly(1))
            ->method('getUpdateErrorCount')
            ->willReturn(0);
        $feed->expects($this->exactly(0))
            ->method('getLastUpdateError');

        $this->service->expects($this->exactly(1))
                           ->method('find')
                           ->with('admin', '1')
                           ->willReturn($feed);

        $this->service->expects($this->exactly(1))
                           ->method('fetch')
                           ->with($feed)
                           ->willReturn($feed);

        $result = $this->command->run($this->consoleInput, $this->consoleOutput);
        $this->assertSame(0, $result);
    }

    /**
     * Test a valid call will work
     */
    public function testValidFeedError()
    {
        $this->consoleInput->expects($this->exactly(2))
                           ->method('getArgument')
                           ->will($this->returnValueMap([
                               ['feed-id', '1'],
                               ['user-id', 'admin'],
                           ]));

        $feed = $this->createMock(Feed::class);
        $feed->expects($this->exactly(1))
             ->method('getUpdateErrorCount')
             ->willReturn(10);
        $feed->expects($this->exactly(1))
             ->method('getLastUpdateError')
             ->willReturn('Problem');

        $this->service->expects($this->exactly(1))
                           ->method('find')
                           ->with('admin', '1')
                           ->willReturn($feed);

        $this->service->expects($this->exactly(1))
                           ->method('fetch')
                           ->with($feed)
                           ->willReturn($feed);

        $this->consoleOutput->expects($this->exactly(1))
                           ->method('writeln')
                           ->with('Problem');

        $result = $this->command->run($this->consoleInput, $this->consoleOutput);
        $this->assertSame(255, $result);
    }

    /**
     * Test a valid call will work
     */
    public function testInValid()
    {
        $this->consoleInput->expects($this->exactly(2))
            ->method('getArgument')
            ->will($this->returnValueMap([
                ['feed-id', '1'],
                ['user-id', 'admin'],
            ]));

        $feed = $this->createMock(Feed::class);

        $this->service->expects($this->exactly(1))
            ->method('find')
            ->with('admin', '1')
            ->willReturn($feed);

        $this->service->expects($this->exactly(1))
            ->method('fetch')
            ->with($feed)
            ->will($this->throwException(new ServiceNotFoundException('')));

        $result = $this->command->run($this->consoleInput, $this->consoleOutput);
        $this->assertSame(1, $result);
    }
}
