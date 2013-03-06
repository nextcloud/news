<?php

namespace OCA\News;

use \OCA\News\Controller\FolderController;

class FolderApi {

	public function __construct($bl){
		$this->bl = $bl;
	}

	public function getAll() {
		$folders = $this->bl->getAll();
		$serializedFolders = array();
		
		//TODO: check the behaviour for nested folders 
		foreach ($folders as $folder) {
			$serializedFolders[] = $folder->jsonSerialize();
		}
		return new \OC_OCS_Result($serializedFolders);
	}
	
	public function create() {
		
		$name = $_POST['name'];
		$parentId = $_POST['parentid'];
		
		$this->bl->create($name, $parentId);
		
		return new \OC_OCS_Result();
	}
}

