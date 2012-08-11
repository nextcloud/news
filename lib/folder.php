<?php
/**
* ownCloud - News app
*
* @author Alessandro Cosentino
* Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
*
* This file is licensed under the Affero General Public License version 3 or later.
* See the COPYING-README file
*
*/

namespace OCA\News;

/**
 * This class models a folder that contains feeds.
 */
class Folder extends Collection {

	private $name;
	private $children;
	private $parent;

	public function __construct($name, $id = null, Collection $parent = null){
		$this->name = $name;
		if ($id !== null){
			parent::__construct($id);
		}
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

	public function addChild(Collection $child){
		$this->children[] = $child;
	}
	
	public function addChildren($children){
		$this->children = $children;
	}
	
	public function getChildren(){
		return $this->children;
	}

}