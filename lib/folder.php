<?php
/**
* ownCloud - News app
*
* @author Alessandro Cosentino
* @copyright 2012 Alessandro Cosentino cosenal@gmail.com
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
* You should have received a copy of the GNU Lesser General Public 
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
* 
*/

/**
 * This class models a folder that contains feeds.
 */
class OC_News_Folder extends OC_News_Collection {

	private $name;
	private $children;
	private $parent;

	public function __construct($name, $parent = null){
		$this->name = $name;
		$this->children = array();
		if ($parent !== null){
			$this->parent = $parent;
		}
	}

	public function getName(){
		return $this->name;
	}

	public function setName($name){
		$this->name = $name;
	}

	public function getParentId(){
		if ($this->parent === null){
			return 0;
		}
		return $this->parent->getId();
	}
	
	public function addChild(OC_News_Collection $child){
		$this->children[] = $child;
	}

}