<?php

namespace OCA\News;

class FeedBL {

	public function __construct($feedMapper){
		$this->feedMapper = $feedMapper;
	}
	
	public function getAll() {
		return $this->feedMapper->findAll();	
	}
	
	public function getById($feedid) {
		return $this->feedMapper->findById($feedid);
	}
	
	public function create($url, $folderid) {
		$feed = \OC_News_Utils::fetch($url);
		$this->feedMapper->save($feed, $folderid);
		return true;
	}
	
}
