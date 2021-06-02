<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 */

namespace OCA\News\Tests\Unit\Command;

use OCA\News\Command\Updater\UpdateUser;
use OCA\News\Db\Feed;
use OCA\News\Service\Exceptions\ServiceNotFoundException;
use OCA\News\Service\FeedServiceV2;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateUserTest extends TestCase
{
    /** @var MockObject|FeedServiceV2 */
    protected $service;
    /** @var MockObject|InputInterface */
    protected $consoleInput;
    /** @var MockObject|OutputInterface */
    protected $consoleOutput;

    /** @var UpdateUser */
    protected $command;

    protected function setUp(): void
    {
        $this->service = $this->getMockBuilder(FeedServiceV2::class)
                              ->disableOriginalConstructor()
                              ->getMock();

        $this->consoleInput = $this->getMockBuilder(InputInterface::class)->getMock();
        $this->consoleOutput = $this->getMockBuilder(OutputInterface::class)->getMock();

        $this->command = new UpdateUser($this->service);
    }

    /**
     * Test a valid call will work
     */
    public function testValid()
    {
        $this->consoleInput->expects($this->exactly(1))
             ->method('getArgument')
             ->will($this->returnValueMap([
                 ['user-id', 'admin'],
             ]));

        $feed = $this->createMock(Feed::class);

        $feed->expects($this->exactly(1))
             ->method('getUpdateErrorCount')
             ->willReturn(0);
        $feed->expects($this->exactly(0))
             ->method('getLastUpdateError');

        $this->service->expects($this->exactly(1))
            ->method('findAllForUser')
            ->with('admin')
            ->willReturn([$feed]);

        $this->service->expects($this->exactly(1))
             ->method('fetch')
             ->with($feed)
             ->willReturn($feed);

        $result = $this->command->run($this->consoleInput, $this->consoleOutput);
        $this->assertSame(0, $result);
    }

    /**
     * Test valid calls that fails on some updates
     */
    public function testValidFeedError()
    {
        $this->consoleInput->expects($this->exactly(1))
                           ->method('getArgument')
                           ->will($this->returnValueMap([
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
                      ->method('findAllForUser')
                      ->with('admin')
                      ->willReturn([$feed]);

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
     * Test valid calls that fails completely
     */
    public function testInValid()
    {
        $this->consoleInput->expects($this->exactly(1))
                           ->method('getArgument')
                           ->will($this->returnValueMap([
                               ['user-id', 'admin'],
                           ]));

        $feed = $this->createMock(Feed::class);

        $this->service->expects($this->exactly(1))
                      ->method('findAllForUser')
                      ->with('admin')
                      ->willReturn([$feed]);

        $this->service->expects($this->exactly(1))
                      ->method('fetch')
                      ->with($feed)
                      ->will($this->throwException(new ServiceNotFoundException('')));

        $result = $this->command->run($this->consoleInput, $this->consoleOutput);
        $this->assertSame(1, $result);
    }
}
