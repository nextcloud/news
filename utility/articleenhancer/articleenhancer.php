<?php

/**
* ownCloud - News
*
* @author Alessandro Cosentino
* @author Bernhard Posselt
* @copyright 2012 Alessandro Cosentino cosenal@gmail.com
* @copyright 2012 Bernhard Posselt dev@bernhard-posselt.com
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
* License as published by the Free Software Foundation; either
* version 3 of the License, or any later version.
*
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*
* You should have received a copy of the GNU Affero General Public
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
*
*/

namespace OCA\News\Utility\ArticleEnhancer;

use \OCA\News\Utility\SimplePieFileFactory;


abstract class ArticleEnhancer {


	private $feedRegex;
	private $purifier;
	private $fileFactory;
	private $maximumTimeout;


	/**
	 * @param $purifier the purifier object to clean the html which will be
	 * matched
	 * @param SimplePieFileFactory a factory for getting a simple pie file instance
	 * @param array $regexXPathPair an associative array containing regex to 
	 * match the url and the xpath that should be used for it to extract the 
	 * page
	 * @param int $maximumTimeout maximum timeout in seconds, defaults to 10 sec
	 */
	public function __construct($purifier, SimplePieFileFactory $fileFactory, 
	                            array $regexXPathPair, $maximumTimeout=10){
		$this->purifier = $purifier;
		$this->regexXPathPair = $regexXPathPair;
		$this->fileFactory = $fileFactory;
		$this->maximumTimeout = $maximumTimeout;
	}


	public function enhance($item){

		foreach($this->regexXPathPair as $regex => $search) {

			if(preg_match($regex, $item->getUrl())) {
				$file = $this->fileFactory->getFile($item->getUrl(), $this->maximumTimeout);
				
				// convert encoding by detecting charset from header
				$contentType = $file->headers['content-type'];
				if( preg_match( '/(?<=charset=)[^;]*/', $contentType, $matches ) ) {
					$body = mb_convert_encoding($file->body, 'HTML-ENTITIES', $matches[0]);
				} else {
					$body = $file->body;
				}

				$dom = new \DOMDocument();
				@$dom->loadHTML($body);

				$xpath = new \DOMXpath($dom);
				$xpathResult = $xpath->evaluate($search);

				// in case it wasnt a text query assume its a single 
				if(!is_string($xpathResult)) {
					$xpathResult = $this->domToString($xpathResult);
				}
				
				// convert all relative to absolute URLs
				$xpathResult = $this->substituteRelativeLinks($xpathResult, $item->getUrl());

				$sanitizedResult = $this->purifier->purify($xpathResult);
				$item->setBody($sanitizedResult);
			}
		}

		return $item;
	}


	/**
	 * Method which converts all relative "href" and "src" URLs of
	 * a HTML snippet with their absolute equivalent
	 * @param string $xmlString a HTML snippet as string with the relative URLs to be replaced
	 * @param string $absoluteUrl the approptiate absolute url of the HTML snippet
	 * @return string the result HTML snippet as a string
	 */
	protected function substituteRelativeLinks($xmlString, $absoluteUrl) {
		$dom = new \DOMDocument();
		$dom->preserveWhiteSpace = false;

		// return, if xml is empty or loading the HTML fails
		if( trim($xmlString) == "" || !$dom->loadHTML($xmlString) ) {
			return $xmlString;
		}

		// remove <!DOCTYPE 
		$dom->removeChild($dom->firstChild);            
		// remove <html></html> 
		$dom->replaceChild($dom->firstChild->firstChild, $dom->firstChild);
		
		$substitution = array("href", "src");

		foreach ($substitution as $attribute) {
			$xpath = new \DOMXpath($dom);
			$xpathResult = $xpath->query("//*[@".$attribute." and not(contains(@".$attribute.", '://')) and not(starts-with(@".$attribute.", 'mailto:'))]");
			foreach ($xpathResult as $linkNode) {
				$urlElement = $linkNode->attributes->getNamedItem($attribute);
				$urlElement->nodeValue = $this->relativeToAbsoluteUrl( $urlElement->nodeValue, $absoluteUrl );
			}
		}

		// save dom to string and remove <body></body>
		$xmlString = substr(trim($dom->saveHTML()), 6, -7);
		// domdocument spoils the string with line breaks between the elements. strip them.
		$xmlString = str_replace("\n", "", $xmlString);

		return $xmlString;
	}


	/**
	 * Method which builds a URL by taking a relative URL and its corresponding
	 * absolute URL
	 * For examle relative URL "../example/path/file.php?a=1#anchor" and
	 * absolute URL "https://username:password@www.website.com/subfolder/index.html"
	 * will result in "https://username:password@www.website.com/example/path/file.php?a=1#anchor"
	 * @param string $relativeUrl the relative URL
	 * @param string $absoluteUrl the absolute URL with at least scheme and host
	 * @return string the resulting absolute URL
	 */
	protected function relativeToAbsoluteUrl($relativeUrl, $absoluteUrl) {
		$abs = parse_url($absoluteUrl);

		$newUrl =	$abs["scheme"]."://"
					.( (isset($abs["user"])) ? $abs["user"] . ( (isset($abs["pass"])) ? ":".$abs["pass"] : "") . "@" : "" )
					.$abs["host"]
					.( (isset($abs["port"])) ? ":".$abs["port"] : "" );

		if(substr(trim($relativeUrl), 0, 1) == "/") {
			// we have a relative url like "/a/path/file"
			return $newUrl . $relativeUrl;
		} else {
			// we have a relative url like "a/path/file", handle "."" and ".." directories

			// the starting point is the absolute path, but with out the last part (we don't need the file name)
			$newPath = explode("/", substr($abs["path"], 1) );
			array_pop($newPath);

			$relPath = parse_url($relativeUrl, PHP_URL_PATH);
			$relPath = explode("/", $relPath);

			// cross the relative and the absolute path
			for($i=0; $i<count($relPath)-1; $i++) {
				if($relPath[$i] == ".") {
					continue;
				} elseif($relPath[$i] == "..") {
					array_pop($newPath);
				} else {
					$newPath[] = $relPath[$i];
				}
			}

			// add the last part (the file name) of the relative URL
			$newPath[] = $relPath[ count($relPath)-1 ];
			$newPath = implode("/", $newPath);

			$rel = parse_url($relativeUrl);
			return $newUrl . "/" . $newPath
					. ( (isset($rel["query"]))		? "?".$rel["query"]		: "")
					. ( (isset($rel["fragment"]))	? "#".$rel["fragment"]	: "");
		}
	}


	/**
	 * Method which turns an xpath result to a string
	 * you can customize it by overwriting this method
	 * @param $xpathResult the result from the xpath query
	 * @return the result as a string
	 */
	protected function domToString($xpathResult) {
		$result = "";
		foreach($xpathResult as $node) {
			$result .= $this->toInnerHTML($node);
		}
		return $result;
	}


	protected function toInnerHTML($node) {
		$dom = new \DOMDocument();
		$dom->appendChild($dom->importNode($node, true));
		return trim($dom->saveHTML($dom->documentElement));
	}


}