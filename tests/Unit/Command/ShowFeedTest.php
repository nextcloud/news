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

use OCA\News\Command\ShowFeed;
use OCA\News\Fetcher\Fetcher;
use OCA\News\Service\Exceptions\ServiceNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShowFeedTest extends TestCase
{
    /** @var MockObject|Fetcher */
    protected $fetcher;
    /** @var MockObject|InputInterface */
    protected $consoleInput;
    /** @var MockObject|OutputInterface */
    protected $consoleOutput;

    /** @var ShowFeed */
    protected $command;

    protected function setUp(): void
    {
        $this->fetcher = $this->getMockBuilder(Fetcher::class)
                              ->disableOriginalConstructor()
                              ->getMock();

        $this->consoleInput = $this->getMockBuilder(InputInterface::class)->getMock();
        $this->consoleOutput = $this->getMockBuilder(OutputInterface::class)->getMock();

        $this->command = new ShowFeed($this->fetcher);
    }

    /**
     * Test a valid call will work
     */
    public function testValid()
    {
        $this->consoleInput->expects($this->once())
                           ->method('getArgument')
                           ->with('feed')
                           ->willReturn('feed');

        $this->consoleInput->expects($this->exactly(3))
                           ->method('getOption')
                           ->will($this->returnValueMap([
                               ['user', 'user'],
                               ['password', 'user'],
                               ['full-text', '1'],
                           ]));

        $this->fetcher->expects($this->exactly(1))
                           ->method('fetch')
                           ->with('feed', true, 'user', 'user')
                           ->willReturn([['feed'], [['items']]]);

        $this->consoleOutput->expects($this->exactly(2))
                            ->method('writeln')
                            ->withConsecutive(
                                ["Feed: [\n    \"feed\"\n]"],
                                ["Items: [\n    [\n        \"items\"\n    ]\n]"]
                            );

        $result = $this->command->run($this->consoleInput, $this->consoleOutput);
        $this->assertSame(0, $result);
    }

    /**
     * Test a valid call will work
     */
    public function testInValid()
    {
        $this->consoleInput->expects($this->once())
                           ->method('getArgument')
                           ->with('feed')
                           ->willReturn('feed');

        $this->consoleInput->expects($this->exactly(3))
                           ->method('getOption')
                           ->will($this->returnValueMap([
                               ['user', 'user'],
                               ['password', 'user'],
                               ['full-text', '1'],
                           ]));

        $this->fetcher->expects($this->exactly(1))
                           ->method('fetch')
                           ->with('feed', true, 'user', 'user')
                           ->will($this->throwException(new ServiceNotFoundException('test')));

        $this->consoleOutput->expects($this->exactly(2))
                            ->method('writeln')
                            ->withConsecutive(
                                ['<error>Failed to fetch feed info:</error>'],
                                ['test']
                            );

        $result = $this->command->run($this->consoleInput, $this->consoleOutput);
        $this->assertSame(1, $result);
    }
}
