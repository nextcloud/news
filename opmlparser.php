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

	private $title;
	private $body;
	private $data;
	private $count;
	
	private function __construct() {
		$this->data = array();
		$this->count = 0;
	}

	/**
	 * @brief
	 * @returns the title element from the head section of the OPML file
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @brief
	 * @returns the number of feeds found in the OPML file
	 */
	public function getData() {
		return $this->data;
	}
	
	/**
	 * @brief
	 * @returns the number of feeds found in the file
	 */
	public function getCount() {
		return $this->count;
	}
	
	/**
	 * @brief This is used as a utility private function by the method OPMLParser::parse
	 */
	private static function parseFolder($rawfolder, &$count) {
		$list = array();
		foreach ($rawfolder->outline as $rawcollection) {
			if ($rawcollection['type'] == 'rss') {
				$collection = self::parseFeed($rawcollection);
				$count++;
			}
			else {
				$name = (string)$rawcollection['text'];
				$children = self::parseFolder($rawcollection, $count);
				$collection = new OCA\News\Folder($name);
				$collection->addChildren($children);
			}
			if ($collection !== null) {
				$list[] = $collection;
			}
		}
		return $list;
	}
	
	private static function parseFeed($rawfeed) {
		$url = (string)$rawfeed['xmlUrl'];
		$title = (string)$rawfeed['title'];

		$feed = new OCA\News\Feed($url, $title);
		return $feed;
	}
	
	/**
	 * @brief 
	 * @param $raw the XML string to be parsed
	 * @return an object of the OPMLParser class itself
	 *	or null if the parsing failed
	 * @throws 
	 */
	public static function parse($raw) {
		$parsed = new OPMLParser();
		
		$xml_parser = new SimpleXMLElement($raw, LIBXML_NOERROR);
		$parsed->title = (string)$xml_parser->head->title;
		$parsed->body = $xml_parser->body;
		
		if ($parsed->body != null) {
			$parsed->data =  self::parseFolder($parsed->body, $parsed->count);
			return $parsed;
		} else {
			return null;
		}
	}
}