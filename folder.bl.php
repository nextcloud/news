<?php

namespace OCA\News;

class FolderBl {

	public function __construct($folderMapper){
		$this->folderMapper = $folderMapper;
	}
	
	public function getAll() {
		return $this->folderMapper->getAll();	
	}
	
	public function create($name, $parentId) {
		//TODO: change the setparentid in the model class Folder
		$folder = new Folder($name, null, null);
		return $this->folderMapper->save($folder);	
	}
}
