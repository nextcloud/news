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

namespace OCA\News\Db;
use \OCA\AppFramework\Core\API;

require_once(__DIR__ . "/../classloader.php");

class MapperNews extends NewsMapper {
	public function __construct(API $api, $tableName){
		parent::__construct($api, $tableName);
	}
	
	public function publicFindRow($sql, $id, $userId){
		return $this->findRow($sql, $id, $userId);
	}
}

class NewsMapperTest extends \OCA\AppFramework\Utility\MapperTestUtility {

	private $newsMapper;
	
	public function setUp() {
		$this->beforeEach();
		$this->newsMapper = new MapperNews($this->api, 'news_table');

		$this->userId = 'john';
		$this->id = 2;
		
		$this->rows = array(
		    array('testRow')
 		);
	}
	
	public function testFindRow() {
		$sql = 'test';
			
		$this->setMapperResult($sql, array($this->id, $this->userId), $this->rows);
		
		$result = $this->newsMapper->publicFindRow($sql, $this->id, $this->userId);
		$this->assertEquals($this->rows[0], $result);
		
	}
	
	public function testFindRowNoFound() {
		$sql = 'test';
			
		$this->setMapperResult($sql, array($this->id, $this->userId), array());

		$this->setExpectedException('\OCA\AppFramework\Db\DoesNotExistException');
		$this->newsMapper->publicFindRow($sql, $this->id, $this->userId);
		
	}
	
	public function testFindRowMultipleRows() {
		$sql = 'test';
		array_push($this->rows, array('testRow2'));	
		$this->setMapperResult($sql, array($this->id, $this->userId), $this->rows);

		$this->setExpectedException('\OCA\AppFramework\Db\MultipleObjectsReturnedException');
		$this->newsMapper->publicFindRow($sql, $this->id, $this->userId);
		
	}
}