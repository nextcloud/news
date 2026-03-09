<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Clayton <claytonlin1110@gmail.com>
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
    private MockObject|IL10N $l10n;
    private MockObject|StatusService $statusService;
    /**
     * The class to test
     */
    private CronSetupCheck $check;

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
        $this->assertSame('news', $this->check->getCategory());
    }

    public function testRunSuccessWhenCronProperlyConfigured(): void
    {
        $this->statusService->expects($this->once())
            ->method('isCronProperlyConfigured')
            ->willReturn(true);

        $result = $this->check->run();

        $this->assertInstanceOf(SetupResult::class, $result);
        $this->assertSame(SetupResult::SUCCESS, $result->getSeverity());
        $this->assertSame('Feed update method is correctly configured.', $result->getDescription());
        $this->assertNull($result->getLinkToDoc());
    }

    public function testRunWarningWhenCronNotProperlyConfigured(): void
    {
        $this->statusService->expects($this->once())
            ->method('isCronProperlyConfigured')
            ->willReturn(false);

        $result = $this->check->run();

        $this->assertInstanceOf(SetupResult::class, $result);
        $this->assertSame(SetupResult::WARNING, $result->getSeverity());
        $this->assertSame('Ajax or webcron mode detected! Your feeds will not be updated!', $result->getDescription());
        $this->assertSame(
            'https://docs.nextcloud.org/server/latest/admin_manual/'
            . 'configuration_server/background_jobs_configuration.html#cron',
            $result->getLinkToDoc()
        );
    }
}
