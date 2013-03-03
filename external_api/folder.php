<?php

namespace OCA\News;

use \OCA\News\Controller\FolderController;

class API_Folder {

	public static function getAll() {
		$container = createDIContainer();
		$bl = $container['FolderBL'];
		$folders = $bl->getAll();
		$serializedFolders = array();
		
		//TODO: check the behaviour for nested folders 
		foreach ($folders as $folder) {
			$serializedFolders[] = $folder->jsonSerialize();
		}
		return new \OC_OCS_Result($serializedFolders);
	}
	
	public static function create() {
		
		$name = $_POST['name'];
		$parentId = $_POST['parentid'];
		
		$container = createDIContainer();
		$bl = $container['FolderBL'];
		$bl->create($name, $parentId);
		
		return new \OC_OCS_Result();
	}
}

