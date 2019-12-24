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

use FeedIo\Feed;
use FeedIo\FeedIo;
use Favicon\Favicon;
use FeedIo\Reader\Result;
use OCA\News\Command\ExploreGenerator;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Test\TestCase;

class ExploreGeneratorTest extends TestCase {
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $favicon;
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $feedio;
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $consoleInput;
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $consoleOutput;

    /** @var \Symfony\Component\Console\Command\Command */
    protected $command;

    protected function setUp(): void
    {
        parent::setUp();

        $feedio = $this->feedio = $this->getMockBuilder(FeedIo::class)
            ->disableOriginalConstructor()
            ->getMock();

        $favicon = $this->favicon = $this->getMockBuilder(Favicon::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->consoleInput = $this->getMockBuilder(InputInterface::class)->getMock();
        $this->consoleOutput = $this->getMockBuilder(OutputInterface::class)->getMock();

        /** @var \FeedIo\FeedIo $feedio, \Favicon\Favicon $favicon */
        $this->command = new ExploreGenerator($feedio, $favicon);
    }

    /**
     * Test a valid feed will write the data needed.
     */
    public function testValidFeed()
    {
        $result = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        $feed = $this->getMockBuilder(Feed::class)
            ->disableOriginalConstructor()
            ->getMock();
        $feed->expects($this->once())
            ->method('getTitle')
            ->willReturn('Title');
        $feed->expects($this->exactly(2))
            ->method('getLink')
            ->willReturn('Link');
        $feed->expects($this->once())
            ->method('getDescription')
            ->willReturn('Description');

        $result->expects($this->once())
            ->method('getFeed')
            ->willReturn($feed);

        $this->favicon->expects($this->once())
            ->method('get')
            ->willReturn('https://feed.io/favicon.ico');

        $this->feedio->expects($this->once())
            ->method('read')
            ->with('https://feed.io/rss.xml')
            ->willReturn($result);

        $this->consoleInput->expects($this->once())
            ->method('getArgument')
            ->with('feed')
            ->willReturn('https://feed.io/rss.xml');

        $this->consoleInput->expects($this->once())
            ->method('getOption')
            ->with('votes')
            ->willReturn(100);

        $this->consoleOutput->expects($this->once())
            ->method('writeln')
            ->with($this->stringContains('https:\/\/feed.io\/rss.xml'));

        self::invokePrivate($this->command, 'execute', [$this->consoleInput, $this->consoleOutput]);
    }

    /**
     * Test a valid feed will write the data needed.
     */
    public function testFailingFeed()
    {

        $this->favicon->expects($this->never())
            ->method('get');

        $this->feedio->expects($this->once())
            ->method('read')
            ->with('https://feed.io/rss.xml')
            ->will($this->throwException(new \Exception('Failure')));

        $this->consoleInput->expects($this->once())
            ->method('getArgument')
            ->with('feed')
            ->willReturn('https://feed.io/rss.xml');

        $this->consoleInput->expects($this->once())
            ->method('getOption')
            ->with('votes')
            ->willReturn(100);

        $this->consoleOutput->expects($this->at(0))
            ->method('writeln')
            ->with($this->stringContains('<error>'));

        $this->consoleOutput->expects($this->at(1))
            ->method('writeln')
            ->with($this->stringContains('Failure'));

        self::invokePrivate($this->command, 'execute', [$this->consoleInput, $this->consoleOutput]);
    }

    /**
     * Test a valid feed and votes will write the data needed.
     */
    public function testFeedWithVotes()
    {
        $result = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        $feed = $this->getMockBuilder(Feed::class)
            ->disableOriginalConstructor()
            ->getMock();
        $feed->expects($this->once())
            ->method('getTitle')
            ->willReturn('Title');
        $feed->expects($this->exactly(2))
            ->method('getLink')
            ->willReturn('Link');
        $feed->expects($this->once())
            ->method('getDescription')
            ->willReturn('Description');

        $result->expects($this->once())
            ->method('getFeed')
            ->willReturn($feed);

        $this->favicon->expects($this->once())
            ->method('get')
            ->willReturn('https://feed.io/favicon.ico');

        $this->feedio->expects($this->once())
            ->method('read')
            ->with('https://feed.io/rss.xml')
            ->willReturn($result);

        $this->consoleInput->expects($this->once())
            ->method('getArgument')
            ->with('feed')
            ->willReturn('https://feed.io/rss.xml');

        $this->consoleInput->expects($this->once())
            ->method('getOption')
            ->with('votes')
            ->willReturn(200);

        $this->consoleOutput->expects($this->once())
            ->method('writeln')
            ->with($this->stringContains('200'));

        self::invokePrivate($this->command, 'execute', [$this->consoleInput, $this->consoleOutput]);
    }
}
