<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 */

namespace OCA\News\Tests\Unit\Controller;

use OCA\News\Controller\ImportController;
use OCA\News\Service\Exceptions\ServiceValidationException;
use OCA\News\Service\ImportService;
use OCA\News\Service\OpmlService;

use OCP\IRequest;

use OCP\IUser;
use OCP\IUserSession;
use PHPUnit\Framework\TestCase;

class ImportControllerTest extends TestCase
{

    private $controller;
    private $user;
    private $request;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|IUserSession
     */
    private $userSession;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ImportService
     */
    private $importService;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|OpmlService
     */
    private $opmlService;

    /**
     * Gets run before each test
     */
    public function setUp(): void
    {
        $appName = 'news';
        $this->importService = $this->getMockBuilder(ImportService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->opmlService = $this->getMockBuilder(OpmlService::class)
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
        $this->request = $this->getMockBuilder(IRequest::class)
            ->getMock();
        $this->controller = new ImportController(
            $this->request,
            $this->userSession,
            $this->importService,
            $this->opmlService
        );
    }


    public function testImportArticlesSuccess()
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'news-import-test_');
        file_put_contents($tmpFile, json_encode(['foo' => 'bar']));

        $this->request->files = [
            'file' => [
                'tmp_name' => $tmpFile
            ]
        ];

        $this->importService
            ->expects($this->once())
            ->method('articles')
            ->with('user', ['foo' => 'bar']);

        $response = $this->controller->articles();

        $this->assertEquals('ok', $response['status']);
        $this->assertEquals('', $response['message']);

        unlink($tmpFile);
    }


    public function testImportArticlesError()
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'news-import-test_');
        file_put_contents($tmpFile, json_encode(['foo' => 'bar']));

        $this->request->files = [
            'file' => [
                'tmp_name' => $tmpFile
            ]
        ];

        $this->importService
            ->method('articles')
            ->willThrowException(new ServiceValidationException("Invalid data"));

        $response = $this->controller->articles();

        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Invalid data', $response['message']);

        unlink($tmpFile);
    }

    public function testImportArticlesInvalidUpload()
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'news-import-test_');
        file_put_contents($tmpFile, 'invalid json');

        $this->request->files = [
            'file' => [
                'tmp_name' => $tmpFile
            ]
        ];

        $this->importService
            ->expects($this->never())
            ->method('articles');

        $response = $this->controller->articles();

        $this->assertEquals('error', $response['status']);

        unlink($tmpFile);
    }

    public function testImportArticlesEmptyUpload()
    {
        $this->request->files = [];

        $this->importService
            ->expects($this->never())
            ->method('articles');

        $response = $this->controller->articles();

        $this->assertEquals('error', $response['status']);
    }
}
