<?php 

class OPMLParser {

	public $raw;
	public $data;

	public function __construct($raw) {
		$this->raw = $raw;
		$this->data = array();
	}
	
	public function parse(){
		
	}
	
	//TODO: implement an iterator to get data in a fancier way
	public function getData() {
		return $this->data;
	}
	
}