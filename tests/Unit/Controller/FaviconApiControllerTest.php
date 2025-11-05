<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 */

namespace OCA\News\Tests\Unit\Controller;

use OCA\News\Controller\FaviconApiController;
use OCA\News\Constants;
use OCA\News\Utility\AppData;

use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\DataDownloadResponse;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;

use PHPUnit\Framework\TestCase;

class FaviconApiControllerTest extends TestCase
{
    private $controller;
    private $user;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|IUserSession
     */
    private $userSession;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|appData
     */
    private $appData;

    /**
     * Gets run before each test
     */
    public function setUp(): void
    {
        $appName = 'news';
        $this->appData = $this->getMockBuilder(AppData::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->user = $this->getMockBuilder(IUser::class)->getMock();
        $this->user->expects($this->any())
                   ->method('getUID')
                   ->will($this->returnValue('user'));
        $this->userSession = $this->getMockBuilder(IUserSession::class)
                                  ->getMock();
        $this->userSession->expects($this->any())
                          ->method('getUser')
                          ->will($this->returnValue($this->user));
        $request = $this->getMockBuilder(IRequest::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->controller = new FaviconApiController(
            $request,
            $this->userSession,
            $this->appData
        );
    }

    public function testGetWithExistingLogo()
    {
        $feedUrlHash = '51f108ce113f11fbcbb7da6083c621cd';
        $logoHash = 'cae4432725b27adb4d0fd72cd01edd53';
        $logoContent = <<<'SVG'
            <?xml version="1.0" encoding="UTF-8"?>
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64">
              <rect width="64" height="64" fill="#ff6600"/>
              <circle cx="32" cy="32" r="20" fill="#ffffff"/>
            </svg>
            SVG;


        $this->appData->expects($this->exactly(2))
            ->method('getFileContent')
            ->willReturnMap([
                [Constants::LOGO_INFO_DIR, 'img_'.$feedUrlHash, $logoHash],
                [Constants::LOGO_IMAGE_DIR, $logoHash, $logoContent],
            ]);

        $return = $this->controller->get($feedUrlHash);

        $this->assertTrue($return instanceof DataDownloadResponse);
        $this->assertEquals($logoContent, $return->render());
    }

    public function testGetWithDefaultLogo()
    {
        $feedUrlHash = '51f108ce113f11fbcbb7da6083c621cd';
        $defaultLogoContent = file_get_contents(__DIR__ . '/../../../img/rss.svg');

        $this->appData->expects($this->exactly(1))
            ->method('getFileContent')
            ->willReturnMap([
                [Constants::LOGO_INFO_DIR, 'img_'.$feedUrlHash, null],
            ]);

        $return = $this->controller->get($feedUrlHash);

        $this->assertTrue($return instanceof DataDownloadResponse);
        $this->assertEquals($defaultLogoContent, $return->render());
    }
}
