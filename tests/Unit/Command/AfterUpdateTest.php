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

use OCA\News\Command\Updater\AfterUpdate;
use OCA\News\Fetcher\Fetcher;
use OCA\News\Service\Exceptions\ServiceNotFoundException;
use OCA\News\Service\ItemServiceV2;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AfterUpdateTest extends TestCase
{
    /** @var MockObject|ItemServiceV2 */
    protected $service;
    /** @var MockObject|InputInterface */
    protected $consoleInput;
    /** @var MockObject|OutputInterface */
    protected $consoleOutput;

    /** @var AfterUpdate */
    protected $command;

    protected function setUp(): void
    {
        $this->service = $this->getMockBuilder(ItemServiceV2::class)
                              ->disableOriginalConstructor()
                              ->getMock();

        $this->consoleInput = $this->getMockBuilder(InputInterface::class)
                                   ->getMock();
        $this->consoleOutput = $this->getMockBuilder(OutputInterface::class)
                                    ->getMock();

        $this->command = new AfterUpdate($this->service);
    }

    /**
     * Test a valid call will work
     */
    public function testValid()
    {
        $this->consoleInput->expects($this->once())
                           ->method('getArgument')
                           ->with('purge_count')
                           ->willReturn('1');

        $this->service->expects($this->exactly(1))
                           ->method('purgeOverThreshold')
                           ->with('1')
                           ->willReturn('test');

        $this->consoleOutput->expects($this->exactly(1))
            ->method('writeln')
            ->with('test');

        $result = $this->command->run($this->consoleInput, $this->consoleOutput);
        $this->assertSame(0, $result);
    }
}
