<?php

namespace OCA\News;

class FolderBL {

	public function __construct($folderMapper){
		$this->folderMapper = $folderMapper;
	}
	
	public static function getAll() {
		$folders = $this->folderMapper->getAll();	
	}
}
