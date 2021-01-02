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

use OCA\News\Command\Config\FeedAdd;
use OCA\News\Command\Config\FeedDelete;
use OCA\News\Command\Config\FolderDelete;
use OCA\News\Command\Updater\UpdateFeed;
use OCA\News\Db\Feed;
use OCA\News\Service\Exceptions\ServiceNotFoundException;
use OCA\News\Service\FeedServiceV2;
use OCA\News\Service\FolderServiceV2;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FolderDeleteTest extends TestCase
{
    /** @var MockObject|FolderServiceV2 */
    protected $service;
    /** @var MockObject|InputInterface */
    protected $consoleInput;
    /** @var MockObject|OutputInterface */
    protected $consoleOutput;

    /** @var UpdateFeed */
    protected $command;

    protected function setUp(): void
    {
        $this->service = $this->getMockBuilder(FolderServiceV2::class)
                              ->disableOriginalConstructor()
                              ->getMock();

        $this->consoleInput = $this->getMockBuilder(InputInterface::class)->getMock();
        $this->consoleOutput = $this->getMockBuilder(OutputInterface::class)->getMock();

        $this->command = new FolderDelete($this->service);
    }

    /**
     * Test a valid call will work
     */
    public function testValid()
    {
        $this->consoleInput->expects($this->exactly(2))
                           ->method('getArgument')
                           ->will($this->returnValueMap([
                               ['folder-id', '1'],
                               ['user-id', 'admin'],
                           ]));

        $this->service->expects($this->exactly(1))
                           ->method('delete')
                           ->with('admin', '1');

        $result = $this->command->run($this->consoleInput, $this->consoleOutput);
        $this->assertSame(0, $result);
    }

    /**
     * Test a valid call will work
     */
    public function testInValid()
    {
        $this->expectException('OCA\News\Service\Exceptions\ServiceValidationException');
        $this->expectExceptionMessage('Can not remove root folder!');

        $this->consoleInput->expects($this->exactly(2))
                           ->method('getArgument')
                           ->will($this->returnValueMap([
                               ['folder-id', null],
                               ['user-id', 'admin'],
                           ]));

        $this->service->expects($this->never())
                           ->method('delete');

        $result = $this->command->run($this->consoleInput, $this->consoleOutput);
        $this->assertSame(0, $result);
    }
}
