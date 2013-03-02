<?php

namespace OCA\News;

class FolderBL {

	public function __construct($folderMapper){
		$this->folderMapper = $folderMapper;
	}
	
	public function getAll() {
		return $this->folderMapper->getAll();	
	}
}
