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
	private $title;
	private $error;

	public function __construct($raw) {
		$this->raw = $raw;
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
		return self::parseFolder($this->body);
	}
	
	private function parseFolder($rawfolder) {
		$list = array();
		foreach ($rawfolder->outline as $rawcollection) {
			if ($rawcollection['type'] == 'rss') {
				$collection = self::parseFeed($rawcollection);
			}
			else {
				$name = (string)$rawcollection['text'];
				$children = self::parseFolder($rawcollection);
				$collection = new OC_News_Folder($name);
				$collection->addChildren($children);
			}
			if ($collection !== null) {
				$list[] = $collection;
			}
		}
		return $list;
	}
	
	private function parseFeed($rawfeed) {
		$url = (string)$rawfeed['xmlUrl'];
		
		$feed = OC_News_Utils::fetch($url);
		if ($feed !== null) {
			$title = $rawfeed['title'];
			$feed->setTitle($title);
		}
		echo $url;
		return $feed;
	}
	
	public function getTitle() {
		return $this->title;
	}
	
	public function getError() {
		return $this->error;
	}
}