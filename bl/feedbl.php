<?php

namespace OCA\News;

class FeedBl {

	public function __construct($feedMapper){
		$this->feedMapper = $feedMapper;
	}
	
	public function getAll() {
		return $this->feedMapper->findAll();	
	}
	
	public function getById($feedid) {
		return $this->feedMapper->findById($feedid);
	}
	
	public function delete($feedid) {
		return $this->feedMapper->deleteById($feedid);
	}

	public function create($url, $folderid) {
		$feed = new Feed($url);
		$this->feedMapper->save($feed, $folderid);
		$feed = Utils::fetch($url);
		if ($feed != null) {
			$this->feedMapper->save($feed, $folderid);
		}
		return true;
	}
	
}
