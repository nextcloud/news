<?php 

/**
* ownCloud - News app
*
* @author Alessandro Cosentino
* Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
*
* This file is licensed under the Affero General Public License version 3 or later.
* See the COPYING-README file
*
*/

class OPMLParser {

	private $raw;
	private $body;
	private $data;
	private $title;
	private $error;

	public function __construct($raw) {
		$this->raw = $raw;
		$this->data = array();
		try {
			$xml_parser = new SimpleXMLElement($this->raw, LIBXML_NOERROR);
			$this->title = (string)$xml_parser->head->title;
			$this->body = $xml_parser->body;
		}
		catch (Exception $e) {
			$this->error = $e->getMessage();
			return;
		}
	}
	
	public function parse(){
		
	}
	
	//TODO: implement an iterator to get data in a fancier way
	public function getData() {
		return $this->data;
	}
	
	public function getTitle() {
		return $this->title;
	}
	
	public function getError() {
		return $this->error;
	}
}