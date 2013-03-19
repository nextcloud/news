<?php

/**
* ownCloud - News
*
* @author Alessandro Copyright
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

abstract class Entity {

	public $id;
	
	private $updatedFields;


	public function __construct(){
		$this->updatedFields = array();
	}


	/**
	 * Each time a setter is called, push the part after set
	 * into an array: for instance setId will save Id in the 
	 * updated fields array so it can be easily used to create the
	 * getter method
	 */
	public function __call($methodName, $args){
		if(startsWith($methodName, 'set')){
			$setterPart = substr($methodName, 2);
			array_push($this->updatedFields, $setterPart);
		}
	}


	/**
	 * @return array array of updated fields for update query
	 */
	public function getUpdatedFields(){
		return $this->updatedFields;
	}


	/**
	 * Maps the keys of the row array to the attributes
	 * @param array $row the row to map onto the entity
	 */
	public function fromRow(array $row){
		foreach($row as $key => $value){
			$this->$key = $value;
		}
	}


	public function setId($id){
		$this->id = $id;
	}


	public function getId(){
		return $this->id;
	}

}