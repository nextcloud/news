<?php

namespace OCA\News;

class FeedBL {

	public function __construct($feedMapper){
		$this->feedMapper = $feedMapper;
	}
	
	public function getAll() {
		return $this->feedMapper->findAll();	
	}
}
