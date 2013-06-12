<?php

/**
* ownCloud - News
*
* @author Alessandro Cosentino
* @author Bernhard Posselt
* @copyright 2012 Alessandro Cosentino cosenal@gmail.com
* @copyright 2012 Bernhard Posselt nukeawhale@gmail.com
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
* License as published by the Free Software Foundation; either
* version 3 of the License, or any later version.
*
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*
* You should have received a copy of the GNU Affero General Public
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
*
*/

namespace OCA\News\External;

use \OCA\AppFramework\Http\Request;
use \OCA\AppFramework\Http\JSONResponse;
use \OCA\AppFramework\Utility\ControllerTestUtility;
use \OCA\AppFramework\Http\Http;

use \OCA\News\BusinessLayer\BusinessLayerException;
use \OCA\News\BusinessLayer\BusinessLayerExistsException;

use \OCA\News\Db\Folder;
use \OCA\News\Db\Feed;
use \OCA\News\Db\Item;

require_once(__DIR__ . "/../../classloader.php");


class FolderAPITest extends ControllerTestUtility {

	private $folderBusinessLayer;
	private $itemBusinessLayer;
	private $folderAPI;
	private $api;
	private $user;
	private $request;
	private $msg;

	protected function setUp() {
		$this->api = $this->getMockBuilder(
			'\OCA\AppFramework\Core\API')
			->disableOriginalConstructor()
			->getMock();
		$this->request = $this->getMockBuilder(
			'\OCA\AppFramework\Http\Request')
			->disableOriginalConstructor()
			->getMock();
		$this->folderBusinessLayer = $this->getMockBuilder(
			'\OCA\News\BusinessLayer\FolderBusinessLayer')
			->disableOriginalConstructor()
			->getMock();
		$this->itemBusinessLayer = $this->getMockBuilder(
			'\OCA\News\BusinessLayer\ItemBusinessLayer')
			->disableOriginalConstructor()
			->getMock();
		$this->folderAPI = new FolderAPI(
			$this->api,
			$this->request,
			$this->folderBusinessLayer,
			$this->itemBusinessLayer
		);
		$this->user = 'tom';
		$this->msg = 'test';
	}


	private function assertDefaultAnnotations($methodName){
		$annotations = array('IsAdminExemption', 'IsSubAdminExemption', 'Ajax');
		$this->assertAnnotations($this->folderAPI, $methodName, $annotations);
	}


	public function testGetAllAnnotations(){
		$this->assertDefaultAnnotations('getAll');
	}


	public function testCreateAnnotations(){
		$this->assertDefaultAnnotations('create');
	}


	public function testDeleteAnnotations(){
		$this->assertDefaultAnnotations('delete');
	}


	public function testUpdateAnnotations(){
		$this->assertDefaultAnnotations('update');
	}


	public function testReadAnnotations(){
		$this->assertDefaultAnnotations('read');
	}


	public function testGetAll() {
		$folders = array(
			new Folder()
		);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->folderBusinessLayer->expects($this->once())
			->method('findAll')
			->with($this->equalTo($this->user))
			->will($this->returnValue($folders));

		$response = $this->folderAPI->getAll();

		$this->assertEquals(array(
			'folders' => array($folders[0]->toAPI())
		), $response->getData());
	}


	public function testCreate() {
		$folderName = 'test';
		$folder = new Folder();
		$folder->setName($folderName);
		$folders = array(
			$folder
		);
		$this->folderAPI = new FolderAPI(
			$this->api,
			new Request(array('params' => array(
				'name' => $folderName
			))),
			$this->folderBusinessLayer,
			$this->itemBusinessLayer
		);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->folderBusinessLayer->expects($this->once())
			->method('purgeDeleted')
			->with($this->equalTo($this->user), $this->equalTo(false));
		$this->folderBusinessLayer->expects($this->once())
			->method('create')
			->with($this->equalTo($folderName), $this->equalTo($this->user))
			->will($this->returnValue($folder));

		$response = $this->folderAPI->create();

		$this->assertEquals(array(
			'folders' => array($folders[0]->toAPI())
		), $response->getData());
	}


	public function testCreateAlreadyExists() {
		$msg = 'exists';

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->folderBusinessLayer->expects($this->once())
			->method('purgeDeleted')
			->with($this->equalTo($this->user), $this->equalTo(false));
		$this->folderBusinessLayer->expects($this->once())
			->method('create')
			->will($this->throwException(new BusinessLayerExistsException($msg)));

		$response = $this->folderAPI->create();

		$data = $response->getData();
		$this->assertEquals($msg, $data['message']);
		$this->assertEquals(Http::STATUS_CONFLICT, $response->getStatus());
	}


	public function testDelete() {
		$folderId = 23;

		$this->folderAPI = new FolderAPI(
			$this->api,
			new Request(array('urlParams' => array(
				'folderId' => $folderId
			))),
			$this->folderBusinessLayer,
			$this->itemBusinessLayer
		);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->folderBusinessLayer->expects($this->once())
			->method('delete')
			->with($this->equalTo($folderId), $this->equalTo($this->user));

		$response = $this->folderAPI->delete();

		$this->assertEmpty($response->getData());
	}


	public function testDeleteDoesNotExist() {
		$folderId = 23;

		$this->folderAPI = new FolderAPI(
			$this->api,
			new Request(array('urlParams' => array(
				'folderId' => $folderId
			))),
			$this->folderBusinessLayer,
			$this->itemBusinessLayer
		);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->folderBusinessLayer->expects($this->once())
			->method('delete')
			->will($this->throwException(new BusinessLayerException($this->msg)));

		$response = $this->folderAPI->delete();

		$data = $response->getData();
		$this->assertEquals($this->msg, $data['message']);
		$this->assertEquals(Http::STATUS_NOT_FOUND, $response->getStatus());
	}


	public function testUpdate() {
		$folderId = 23;
		$folderName = 'test';

		$this->folderAPI = new FolderAPI(
			$this->api,
			new Request(
				array(
					'urlParams' => array(
						'folderId' => $folderId
					),

					'params' => array(
						'name' => $folderName
					)
				)
			),
			$this->folderBusinessLayer,
			$this->itemBusinessLayer
		);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->folderBusinessLayer->expects($this->once())
			->method('rename')
			->with($this->equalTo($folderId),
				$this->equalTo($folderName),
				$this->equalTo($this->user));

		$response = $this->folderAPI->update();

		$this->assertEmpty($response->getData());
		$this->assertEquals(Http::STATUS_OK, $response->getStatus());
	}

	public function testUpdateDoesNotExist() {
		$folderId = 23;
		$folderName = 'test';

		$this->folderAPI = new FolderAPI(
			$this->api,
			new Request(
				array(
					'urlParams' => array(
						'folderId' => $folderId
					),

					'params' => array(
						'name' => $folderName
					)
				)
			),
			$this->folderBusinessLayer,
			$this->itemBusinessLayer
		);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->folderBusinessLayer->expects($this->once())
			->method('rename')
			->will($this->throwException(new BusinessLayerException($this->msg)));

		$response = $this->folderAPI->update();

		$data = $response->getData();
		$this->assertEquals($this->msg, $data['message']);
		$this->assertEquals(Http::STATUS_NOT_FOUND, $response->getStatus());
	}


	public function testUpdateExists() {
		$folderId = 23;
		$folderName = 'test';

		$this->folderAPI = new FolderAPI(
			$this->api,
			new Request(
				array(
					'urlParams' => array(
						'folderId' => $folderId
					),

					'params' => array(
						'name' => $folderName
					)
				)
			),
			$this->folderBusinessLayer,
			$this->itemBusinessLayer
		);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->folderBusinessLayer->expects($this->once())
			->method('rename')
			->will($this->throwException(new BusinessLayerExistsException($this->msg)));

		$response = $this->folderAPI->update();

		$data = $response->getData();
		$this->assertEquals($this->msg, $data['message']);
		$this->assertEquals(Http::STATUS_CONFLICT, $response->getStatus());
	}


	public function testRead() {
		$request = new Request(array(
			'urlParams' => array(
				'folderId' => 3
			),
			'params' => array(
				'newestItemId' => 30,
			)
		));
		$this->folderAPI = new FolderAPI(
			$this->api,
			$request,
			$this->folderBusinessLayer,
			$this->itemBusinessLayer
		);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->itemBusinessLayer->expects($this->once())
			->method('readFolder')
			->with(
				$this->equalTo(3),
				$this->equalTo(30),
				$this->equalTo($this->user));

		$response = $this->folderAPI->read();

		$this->assertEmpty($response->getData());
		$this->assertEquals(Http::STATUS_OK, $response->getStatus());
	}


}
