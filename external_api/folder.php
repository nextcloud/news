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

	public function delete($params) {
		$id = $params['folderid'];
		if(!is_numeric($id))
			return new \OC_OCS_Result(null,999,'Invalid input! folderid must be an integer');

		if($this->bl->delete($id))
			return new \OC_OCS_Result();
		else
			return new \OC_OCS_Result(null,999,'Could not delete folder');
	}

	public function modify($params) {
		$id = $params['folderid'];
		if(!is_numeric($id))
			return new \OC_OCS_Result(null,999,'Invalid input! folderid must be an integer'.$id);

		$name = $_POST['name'];
		$parentId = $_POST['parentid'];
		$opened = $_POST['opened'];

		if($this->bl->modify($id, $name, $parentid, $opened))
			return new \OC_OCS_Result();
		else
			return new \OC_OCS_Result(null,999,'Could not modify folder');
	}
}

