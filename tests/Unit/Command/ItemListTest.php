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

use OCA\News\Command\Debug\ItemList;
use OCA\News\Command\Updater\UpdateFeed;
use OCA\News\Service\ItemServiceV2;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ItemListTest extends TestCase
{
    /** @var MockObject|ItemServiceV2 */
    protected $service;
    /** @var MockObject|InputInterface */
    protected $consoleInput;
    /** @var MockObject|OutputInterface */
    protected $consoleOutput;

    /** @var ItemList */
    protected $command;

    protected function setUp(): void
    {
        $this->service = $this->getMockBuilder(ItemServiceV2::class)
                              ->disableOriginalConstructor()
                              ->getMock();

        $this->consoleInput = $this->getMockBuilder(InputInterface::class)->getMock();
        $this->consoleOutput = $this->getMockBuilder(OutputInterface::class)->getMock();

        $this->command = new ItemList($this->service);
    }

    /**
     * Test a valid call will work
     */
    public function testInvalidType()
    {
        $this->consoleInput->expects($this->exactly(1))
                           ->method('getArgument')
                           ->will($this->returnValueMap([
                               ['user-id', 'admin'],
                           ]));

        $this->consoleOutput->expects($this->exactly(1))
                            ->method('writeln')
                            ->with('Invalid type!');

        $result = $this->command->run($this->consoleInput, $this->consoleOutput);
        $this->assertSame(255, $result);
    }

    /**
     * Test a valid call will work
     */
    public function testInvalidLimit()
    {
        $this->consoleInput->expects($this->exactly(1))
                           ->method('getArgument')
                           ->will($this->returnValueMap([
                               ['user-id', 'admin'],
                           ]));
        $this->consoleInput->expects($this->exactly(2))
                           ->method('getOption')
                           ->will($this->returnValueMap([
                               ['type', '1'],
                               ['limit', 'admin'],
                           ]));

        $this->consoleOutput->expects($this->exactly(1))
                            ->method('writeln')
                            ->with('Invalid limit!');

        $result = $this->command->run($this->consoleInput, $this->consoleOutput);
        $this->assertSame(255, $result);
    }

    /**
     * Test a valid call will work
     */
    public function testInvalidOffset()
    {
        $this->consoleInput->expects($this->exactly(1))
            ->method('getArgument')
            ->will($this->returnValueMap([
                ['user-id', 'admin'],
            ]));
        $this->consoleInput->expects($this->exactly(3))
            ->method('getOption')
            ->will($this->returnValueMap([
                ['type', '1'],
                ['limit', '1'],
                ['offset', 'admin'],
            ]));

        $this->consoleOutput->expects($this->exactly(1))
                            ->method('writeln')
                            ->with('Invalid offset!');

        $result = $this->command->run($this->consoleInput, $this->consoleOutput);
        $this->assertSame(255, $result);
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
        $this->consoleInput->expects($this->exactly(4))
                           ->method('getOption')
                           ->will($this->returnValueMap([
                               ['type', '1'],
                               ['limit', '1'],
                               ['offset', '2'],
                               ['reverse-sort', false],
                           ]));

        $this->service->expects($this->exactly(1))
                      ->method('findAllWithFilters')
                      ->with('admin', 1, 1, 2, false, [])
                      ->willReturn([]);

        $this->consoleOutput->expects($this->exactly(1))
                            ->method('writeln')
                            ->with('[]');

        $result = $this->command->run($this->consoleInput, $this->consoleOutput);
        $this->assertSame(0, $result);
    }
}
