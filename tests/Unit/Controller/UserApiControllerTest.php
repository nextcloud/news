<?php
/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Alessandro Cosentino <cosenal@gmail.com>
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Alessandro Cosentino 2012
 * @copyright Bernhard Posselt 2012, 2014
 */

namespace OCA\News\Controller;


class UserApiControllerTest extends \PHPUnit_Framework_TestCase {

    private $request;
    private $appName;
    private $rootFolder;
    private $userSession;
    private $controller;
    private $user;
    private $file;

    protected function setUp() {
        $this->appName = 'news';
        $this->request = $this->getMockBuilder(
            '\OCP\IRequest')
            ->disableOriginalConstructor()
            ->getMock();
        $this->rootFolder = $this->getMockBuilder(
            '\OCP\Files\IRootFolder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->file = $this->getMockBuilder(
            '\OCP\Files\File')
            ->disableOriginalConstructor()
            ->getMock();
        $this->userSession = $this->getMockBuilder(
            '\OCP\IUserSession')
            ->disableOriginalConstructor()
            ->getMock();
        $this->user = $this->getMockBuilder(
            '\OCP\IUser')
            ->disableOriginalConstructor()
            ->getMock();
        $this->controller = new UserApiController(
            $this->appName, $this->request, $this->userSession,
            $this->rootFolder
        );


    }

    private function expectUser($uid, $displayName, $lastLogin) {
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

    private function expectImg($isJpg, $isPng, $user, $exists, $data) {
        $jpg = '/' . $user . '/' . 'avatar.jpg';
        $png = '/' . $user . '/' . 'avatar.png';

        $this->rootFolder->expects($this->any())
            ->method('nodeExists')
            ->will($this->returnValueMap([
                [$jpg, $isJpg],
                [$png, $isPng]
            ]));
        $this->rootFolder->expects($this->any())
            ->method('get')
            ->will($this->returnValue($this->file));
        $this->file->expects($this->any())
            ->method('getContent')
            ->will($this->returnValue($data));
    }

    public function testGetJpeg() {
        $this->expectUser('john', 'John', 123);
        $this->expectImg(true, false, 'john', true, 'hi');

        $result = $this->controller->index();
        $expected = [
            'userId' => 'john',
            'displayName' => 'John',
            'lastLoginTimestamp' => 123,
            'avatar' => [
                'data' => base64_encode('hi'),
                'mime' => 'image/jpeg'
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    public function testGetPng() {
        $this->expectUser('john', 'John', 123);
        $this->expectImg(false, true, 'john', false, 'hi');

        $result = $this->controller->index();
        $expected = [
            'userId' => 'john',
            'displayName' => 'John',
            'lastLoginTimestamp' => 123,
            'avatar' => [
                'data' => base64_encode('hi'),
                'mime' => 'image/png'
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    public function testNoAvatar() {
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
