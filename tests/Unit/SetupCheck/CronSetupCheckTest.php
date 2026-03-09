<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Alessandro Cosentino <cosenal@gmail.com>
 * @author    Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright 2012 Alessandro Cosentino
 * @copyright 2012-2014 Bernhard Posselt
 */

namespace OCA\News\Tests\Unit\SetupCheck;

use OCA\News\Service\StatusService;
use OCA\News\SetupCheck\CronSetupCheck;
use OCP\IL10N;
use OCP\SetupCheck\SetupResult;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CronSetupCheckTest extends TestCase
{
    /**
     * @var MockObject|IL10N
     */
    private $l10n;

    /**
     * @var MockObject|StatusService
     */
    private $statusService;

    /**
     * @var CronSetupCheck
     */
    private $check;

    public function setUp(): void
    {
        $this->l10n = $this->getMockBuilder(IL10N::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->l10n->method('t')
            ->willReturnCallback(fn ($msg) => $msg);

        $this->statusService = $this->getMockBuilder(StatusService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->check = new CronSetupCheck($this->l10n, $this->statusService);
    }

    public function testGetName(): void
    {
        $this->assertSame('Background job mode for feed updates', $this->check->getName());
    }

    public function testGetCategory(): void
    {
        $this->assertSame('system', $this->check->getCategory());
    }

    public function testRunSuccessWhenCronProperlyConfigured(): void
    {
        $this->statusService->expects($this->once())
            ->method('isCronProperlyConfigured')
            ->willReturn(true);

        $result = $this->check->run();

        $this->assertInstanceOf(SetupResult::class, $result);
    }

    public function testRunWarningWhenCronNotProperlyConfigured(): void
    {
        $this->statusService->expects($this->once())
            ->method('isCronProperlyConfigured')
            ->willReturn(false);

        $result = $this->check->run();

        $this->assertInstanceOf(SetupResult::class, $result);
    }
}
