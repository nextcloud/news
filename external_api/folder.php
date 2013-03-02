<?php

namespace OCA\News;

use \OCA\News\Controller\FolderController;

class API_Folder {

	public static function getAll() {
		$container = createDIContainer();
		$controller = $container['FolderBL'];
		return \OC_OCS_Result($controller->getAll());
	}
}

