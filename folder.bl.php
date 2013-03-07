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

	public function delete($folderid) {
		return $this->folderMapper->deleteById($folderid);
	}

	public function modify($folderid, $name = null, $parent = null, $opened = null) {
		$folder = $this->folderMapper->find($folderid);
		if(!$folder)
			return false;

		if($name)
			$folder->setName($name);
		if($parent)
			$folder->setParentId($parent);
		if($opened)
			$folder->setOpened($opened);
		return $this->folderMapper->update($folder);
	}
}
