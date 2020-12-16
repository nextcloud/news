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

namespace OCA\News\Tests\Unit\Controller;

use OCA\News\Controller\UserApiController;
use OCP\Files\File;
use OCP\Files\IRootFolder;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;

use PHPUnit\Framework\TestCase;

class UserApiControllerTest extends TestCase
{

    private $request;
    private $appName;
    private $rootFolder;
    private $userSession;
    private $controller;
    private $user;
    private $file;

    protected function setUp(): void
    {
        $this->appName = 'news';
        $this->request = $this->getMockBuilder(IRequest::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->rootFolder = $this->getMockBuilder(IRootFolder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->file = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->userSession = $this->getMockBuilder(IUserSession::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->user = $this->getMockBuilder(IUser::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->controller = new UserApiController(
            $this->request,
            $this->userSession,
            $this->rootFolder
        );


    }

    private function expectUser($uid, $displayName, $lastLogin)
    {
        $this->userSession->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($this->user));
        $this->user->expects($this->any())
            ->method('getUID')
            ->will($this->returnValue($uid));
        $this->user->expects($this->any())
            ->method('getLastLogin')
            ->will($this->returnValue($lastLogin));
        $this->user->expects($this->any())
            ->method('getDisplayName')
            ->will($this->returnValue($displayName));
    }

    private function expectImg($isJpg, $isPng, $user, $exists, $data)
    {
        $jpg = '/' . $user . '/' . 'avatar.jpg';
        $png = '/' . $user . '/' . 'avatar.png';

        $this->rootFolder->expects($this->any())
            ->method('nodeExists')
            ->will(
                $this->returnValueMap(
                    [
                    [$jpg, $isJpg],
                    [$png, $isPng]
                    ]
                )
            );
        $this->rootFolder->expects($this->any())
            ->method('get')
            ->will($this->returnValue($this->file));
        $this->file->expects($this->any())
            ->method('getContent')
            ->will($this->returnValue($data));
    }

    public function testGetJpeg()
    {
        $this->expectUser('john', 'John', 123);
        $this->expectImg(true, false, 'john', true, 'hi');

        $result = $this->controller->index();
        $expected = [
            'userId' => 'john',
            'displayName' => 'John',
            'lastLoginTimestamp' => 123,
            'avatar' => null
        ];

        $this->assertEquals($expected, $result);
    }

    public function testGetPng()
    {
        $this->expectUser('john', 'John', 123);
        $this->expectImg(false, true, 'john', false, 'hi');

        $result = $this->controller->index();
        $expected = [
            'userId' => 'john',
            'displayName' => 'John',
            'lastLoginTimestamp' => 123,
            'avatar' => null
        ];

        $this->assertEquals($expected, $result);
    }

    public function testNoAvatar()
    {
        $this->expectUser('john', 'John', 123);
        $this->expectImg(false, false, 'john', false, 'hi');

        $result = $this->controller->index();
        $expected = [
            'userId' => 'john',
            'displayName' => 'John',
            'lastLoginTimestamp' => 123,
            'avatar' => null
        ];

        $this->assertEquals($expected, $result);
    }

}
