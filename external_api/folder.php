<?php

namespace OCA\News;

use \OCA\News\Controller\FolderController;

class API_Folder {

	public static function getAll() {
		$container = createDIContainer();
		$bl = $container['FolderBL'];
		$folders = $bl->getAll();
		$serializedFolders = array();
		foreach ($folders as $folder) {
			$serializedFolders[] = $folder->jsonSerialize();
		}
		return new \OC_OCS_Result($serializedFolders);
	}
}

