<?php
/**
 * Nextcloud - News
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

use OCP\AppFramework\Http;

use OCA\News\Db\Feed;
use OCA\News\Db\FeedType;
use OCA\News\Service\ServiceNotFoundException;
use OCA\News\Service\ServiceConflictException;


class FeedControllerTest extends \PHPUnit_Framework_TestCase {

    private $appName;
    private $feedService;
    private $request;
    private $controller;
    private $folderService;
    private $itemService;
    private $settings;
    private $exampleResult;


    /**
     * Gets run before each test
     */
    public function setUp(){
        $this->appName = 'news';
        $this->user = 'jack';
        $this->settings = $this->getMockBuilder(
            '\OCP\IConfig')
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemService = $this
            ->getMockBuilder('\OCA\News\Service\ItemService')
            ->disableOriginalConstructor()
            ->getMock();
        $this->feedService = $this
            ->getMockBuilder('\OCA\News\Service\FeedService')
            ->disableOriginalConstructor()
            ->getMock();
        $this->folderService = $this
            ->getMockBuilder('\OCA\News\Service\FolderService')
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockBuilder(
            '\OCP\IRequest')
            ->disableOriginalConstructor()
            ->getMock();
        $this->controller = new FeedController($this->appName, $this->request,
                $this->folderService,
                $this->feedService,
                $this->itemService,
                $this->settings,
                $this->user);
        $this->exampleResult = [
            'activeFeed' => [
                'id' => 0,
                'type' => FeedType::SUBSCRIPTIONS
            ]
        ];
    }


    public function testIndex(){
        $result = [
            'feeds' => [
                ['a feed'],
            ],
            'starred' => 13
        ];
        $this->feedService->expects($this->once())
            ->method('findAll')
            ->with($this->equalTo($this->user))
            ->will($this->returnValue($result['feeds']));
        $this->itemService->expects($this->once())
            ->method('getNewestItemId')
            ->with($this->equalTo($this->user))
            ->will($this->throwException(new ServiceNotFoundException('')));
        $this->itemService->expects($this->once())
            ->method('starredCount')
            ->with($this->equalTo($this->user))
            ->will($this->returnValue($result['starred']));

        $response = $this->controller->index();

        $this->assertEquals($result, $response);
    }


    public function testIndexHighestItemIdExists(){
        $result = [
            'feeds' => [
                ['a feed'],
            ],
            'starred' => 13,
            'newestItemId' => 5
        ];
        $this->feedService->expects($this->once())
            ->method('findAll')
            ->with($this->equalTo($this->user))
            ->will($this->returnValue($result['feeds']));
        $this->itemService->expects($this->once())
            ->method('getNewestItemId')
            ->with($this->equalTo($this->user))
            ->will($this->returnValue($result['newestItemId']));
        $this->itemService->expects($this->once())
            ->method('starredCount')
            ->with($this->equalTo($this->user))
            ->will($this->returnValue($result['starred']));

        $response = $this->controller->index();

        $this->assertEquals($result, $response);
    }



    private function activeInitMocks($id, $type){
        $this->settings->expects($this->at(0))
            ->method('getUserValue')
            ->with($this->equalTo($this->user),
                $this->equalTo($this->appName),
                $this->equalTo('lastViewedFeedId'))
            ->will($this->returnValue($id));
        $this->settings->expects($this->at(1))
            ->method('getUserValue')
            ->with($this->equalTo($this->user),
                $this->equalTo($this->appName),
                $this->equalTo('lastViewedFeedType'))
            ->will($this->returnValue($type));
    }


    public function testActive(){
        $id = 3;
        $type = FeedType::STARRED;
        $result = [
            'activeFeed' => [
                'id' => $id,
                'type' => $type
            ]
        ];

        $this->activeInitMocks($id, $type);

        $response = $this->controller->active();

        $this->assertEquals($result, $response);
    }


    public function testActiveFeedDoesNotExist(){
        $id = 3;
        $type = FeedType::FEED;
        $ex = new ServiceNotFoundException('hiu');
        $result = $this->exampleResult;

        $this->feedService->expects($this->once())
            ->method('find')
            ->with($this->equalTo($id), $this->equalTo($this->user))
            ->will($this->throwException($ex));

        $this->activeInitMocks($id, $type);

        $response = $this->controller->active();

        $this->assertEquals($result, $response);
    }


    public function testActiveFolderDoesNotExist(){
        $id = 3;
        $type = FeedType::FOLDER;
        $ex = new ServiceNotFoundException('hiu');
        $result = $this->exampleResult;

        $this->folderService->expects($this->once())
            ->method('find')
            ->with($this->equalTo($id), $this->equalTo($this->user))
            ->will($this->throwException($ex));

        $this->activeInitMocks($id, $type);

        $response = $this->controller->active();

        $this->assertEquals($result, $response);
    }


    public function testActiveActiveIsNull(){
        $id = 3;
        $type = null;
        $result = $this->exampleResult;


        $this->activeInitMocks($id, $type);

        $response = $this->controller->active();

        $this->assertEquals($result, $response);
    }


    public function testCreate(){
        $result = [
            'feeds' => [new Feed()],
            'newestItemId' => 3
        ];

        $this->itemService->expects($this->once())
            ->method('getNewestItemId')
            ->will($this->returnValue($result['newestItemId']));
        $this->feedService->expects($this->once())
            ->method('purgeDeleted')
            ->with($this->equalTo($this->user), $this->equalTo(false));
        $this->feedService->expects($this->once())
            ->method('create')
            ->with($this->equalTo('hi'),
                $this->equalTo(4),
                $this->equalTo($this->user),
                $this->equalTo('yo'))
            ->will($this->returnValue($result['feeds'][0]));

        $response = $this->controller->create('hi', 4, 'yo');

        $this->assertEquals($result, $response);
    }


    public function testCreateNoItems(){
        $result = ['feeds' => [new Feed()]];

        $this->feedService->expects($this->once())
            ->method('purgeDeleted')
            ->with($this->equalTo($this->user), $this->equalTo(false));

        $this->itemService->expects($this->once())
            ->method('getNewestItemId')
            ->will($this->throwException(new ServiceNotFoundException('')));

        $this->feedService->expects($this->once())
            ->method('create')
            ->with($this->equalTo('hi'),
                $this->equalTo(4),
                $this->equalTo($this->user),
                $this->equalTo('yo'))
            ->will($this->returnValue($result['feeds'][0]));

        $response = $this->controller->create('hi', 4, 'yo');

        $this->assertEquals($result, $response);
    }


    public function testCreateReturnsErrorForInvalidCreate(){
        $msg = 'except';
        $ex = new ServiceNotFoundException($msg);
        $this->feedService->expects($this->once())
            ->method('purgeDeleted')
            ->with($this->equalTo($this->user), $this->equalTo(false));
        $this->feedService->expects($this->once())
            ->method('create')
            ->will($this->throwException($ex));

        $response = $this->controller->create('hi', 4, 'test');
        $params = json_decode($response->render(), true);

        $this->assertEquals($msg, $params['message']);
        $this->assertEquals(
            $response->getStatus(), Http::STATUS_UNPROCESSABLE_ENTITY
        );
    }


    public function testCreateReturnsErrorForDuplicateCreate(){
        $msg = 'except';
        $ex = new ServiceConflictException($msg);
        $this->feedService->expects($this->once())
            ->method('purgeDeleted')
            ->with($this->equalTo($this->user), $this->equalTo(false));
        $this->feedService->expects($this->once())
            ->method('create')
            ->will($this->throwException($ex));

        $response = $this->controller->create('hi', 4, 'test');
        $params = json_decode($response->render(), true);

        $this->assertEquals($msg, $params['message']);
        $this->assertEquals($response->getStatus(), Http::STATUS_CONFLICT);
    }


    public function testDelete(){
        $this->feedService->expects($this->once())
            ->method('markDeleted')
            ->with($this->equalTo(4));

        $this->controller->delete(4);
    }


    public function testDeleteDoesNotExist(){
        $msg = 'hehe';

        $this->feedService->expects($this->once())
            ->method('markDeleted')
            ->will($this->throwException(new ServiceNotFoundException($msg)));

        $response = $this->controller->delete(4);
        $params = json_decode($response->render(), true);

        $this->assertEquals($msg, $params['message']);
        $this->assertEquals($response->getStatus(), Http::STATUS_NOT_FOUND);
    }


    public function testUpdate(){
        $feed = new Feed();
        $feed->setId(3);
        $feed->setUnreadCount(44);
        $result = [
            'feeds' => [
                [
                    'id' => $feed->getId(),
                    'unreadCount' => $feed->getUnreadCount()
                ]
            ]
        ];

        $this->feedService->expects($this->once())
            ->method('update')
            ->with($this->equalTo(4), $this->equalTo($this->user))
            ->will($this->returnValue($feed));

        $response = $this->controller->update(4);

        $this->assertEquals($result, $response);
    }


    public function testUpdateReturnsJSONError(){
        $this->feedService->expects($this->once())
            ->method('update')
            ->with($this->equalTo(4), $this->equalTo($this->user))
            ->will($this->throwException(new ServiceNotFoundException('NO!')));

        $response = $this->controller->update(4);
        $render = $response->render();

        $this->assertEquals('{"message":"NO!"}', $render);
        $this->assertEquals($response->getStatus(), Http::STATUS_NOT_FOUND);
    }


    public function testImport() {
        $feed = new Feed();

        $expected = [
            'starred' => 3,
            'feeds' => [$feed]
        ];

        $this->feedService->expects($this->once())
            ->method('importArticles')
            ->with($this->equalTo(['json']),
                $this->equalTo($this->user))
            ->will($this->returnValue($feed));

        $this->itemService->expects($this->once())
            ->method('starredCount')
            ->with($this->equalTo($this->user))
            ->will($this->returnValue(3));

        $response = $this->controller->import(['json']);

        $this->assertEquals($expected, $response);
    }


    public function testImportCreatesNoAdditionalFeed() {
        $this->feedService->expects($this->once())
            ->method('importArticles')
            ->with($this->equalTo(['json']),
                $this->equalTo($this->user))
            ->will($this->returnValue(null));

        $this->itemService->expects($this->once())
            ->method('starredCount')
            ->with($this->equalTo($this->user))
            ->will($this->returnValue(3));

        $response = $this->controller->import(['json']);

        $this->assertEquals(['starred' => 3], $response);
    }


    public function testReadFeed(){
        $expected = [
            'feeds' => [
                [
                    'id' => 4,
                    'unreadCount' => 0
                ]
            ]
        ];

        $this->itemService->expects($this->once())
            ->method('readFeed')
            ->with($this->equalTo(4), $this->equalTo(5), $this->user);

        $response = $this->controller->read(4, 5);
        $this->assertEquals($expected, $response);
    }


    public function testRestore() {
        $this->feedService->expects($this->once())
            ->method('unmarkDeleted')
            ->with($this->equalTo(4));

        $this->controller->restore(4);
    }


    public function testRestoreDoesNotExist(){
        $msg = 'hehe';

        $this->feedService->expects($this->once())
            ->method('unmarkDeleted')
            ->will($this->throwException(new ServiceNotFoundException($msg)));

        $response = $this->controller->restore(4);
        $params = json_decode($response->render(), true);

        $this->assertEquals($msg, $params['message']);
        $this->assertEquals($response->getStatus(), Http::STATUS_NOT_FOUND);
    }

    public function testPatch() {
        $expected = [
            'pinned' => true,
            'fullTextEnabled' => true,
            'updateMode' => 1
        ];
        $this->feedService->expects($this->once())
            ->method('patch')
            ->with($this->equalTo(4),
                    $this->equalTo($this->user),
                    $this->equalTo($expected))
            ->will($this->returnValue(1));

        $this->controller->patch(4, true, true, 1);
    }

    public function testPatchDoesNotExist(){
        $msg = 'hehe';

        $this->feedService->expects($this->once())
            ->method('patch')
            ->will($this->throwException(new ServiceNotFoundException($msg)));

        $response = $this->controller->patch(4, 2);
        $params = json_decode($response->render(), true);

        $this->assertEquals($msg, $params['message']);
        $this->assertEquals($response->getStatus(), Http::STATUS_NOT_FOUND);
    }


}
