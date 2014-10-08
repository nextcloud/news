<?php
/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Alessandro Cosentino <cosenal@gmail.com>
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Alessandro Cosentino 2012
 * @copyright Bernhard Posselt 2012, 2014
 */

namespace OCA\News\ArticleEnhancer;

use \DOMDocument;
use \DOMXpath;

use \ZendXml\Security;

use \OCA\News\Db\Item;
use \OCA\News\Utility\SimplePieAPIFactory;
use \OCA\News\Utility\Config;



class XPathArticleEnhancer implements ArticleEnhancer {


	private $fileFactory;
	private $maximumTimeout;
	private $config;
	private $regexXPathPair;


    /**
     * @param \OCA\News\Utility\SimplePieAPIFactory $fileFactory
     * @param array $regexXPathPair an associative array containing regex to
     * match the url and the xpath that should be used for it to extract the
     * page
     * @param \OCA\News\Utility\Config $config
     */
	public function __construct(SimplePieAPIFactory $fileFactory,
	                            array $regexXPathPair,
	                            Config $config){
		$this->fileFactory = $fileFactory;
		$this->regexXPathPair = $regexXPathPair;
		$this->config = $config;
		$this->maximumTimeout = $config->getFeedFetcherTimeout();
	}

	/**
	 * @param \OCA\News\Db\Item $item
	 * @return \OCA\News\Db\Item enhanced item
	 */
	public function enhance(Item $item){

		foreach($this->regexXPathPair as $regex => $search) {

			if(preg_match($regex, $item->getUrl())) {
				$file = $this->getFile($item->getUrl());

				// convert encoding by detecting charset from header
                $contentType = $file->headers['content-type'];
                $body = $file->body;

				if( preg_match( '/(?<=charset=)[^;]*/', $contentType, $matches ) ) {
                    $body = mb_convert_encoding($body, 'HTML-ENTITIES', $matches[0]);
				}

				$dom = new DOMDocument();

				Security::scan($body, $dom, function ($xml, $dom) {
					return @$dom->loadHTML($xml, LIBXML_NONET);
				});

				$xpath = new DOMXpath($dom);
				$xpathResult = $xpath->evaluate($search);

				// in case it wasnt a text query assume its a dom element and
				// convert it to text
				if(!is_string($xpathResult)) {
					$xpathResult = $this->domToString($xpathResult);
				}

				$xpathResult = trim($xpathResult);

				// convert all relative to absolute URLs
				$xpathResult = $this->substituteRelativeLinks($xpathResult, $item->getUrl());

				if($xpathResult) {
					$item->setBody($xpathResult);
				}
			}
		}

		return $item;
	}


	private function getFile($url) {
		return $this->fileFactory->getFile(
			$url, $this->maximumTimeout, 5, null, 'Mozilla/5.0 AppleWebKit'
		);
	}


	/**
	 * Method which converts all relative "href" and "src" URLs of
	 * a HTML snippet with their absolute equivalent
	 * @param string $xmlString a HTML snippet as string with the relative URLs to be replaced
	 * @param string $absoluteUrl the approptiate absolute url of the HTML snippet
	 * @return string the result HTML snippet as a string
	 */
	protected function substituteRelativeLinks($xmlString, $absoluteUrl) {
		$dom = new DOMDocument();
		$dom->preserveWhiteSpace = false;

		$isOk = Security::scan($xmlString, $dom, function ($xml, $dom) {
			// wrap in div to prevent loadHTML from inserting weird elements
			$xml = '<div>' . $xml . '</div>';
			return @$dom->loadHTML($xml, LIBXML_NONET | LIBXML_HTML_NODEFDTD
				| LIBXML_HTML_NOIMPLIED);
		});

		if($xmlString === '' || !$isOk) {
			return false;
		}

		foreach (['href', 'src'] as $attribute) {
			$xpath = new DOMXpath($dom);
			$xpathResult = $xpath->query(
				"//*[@" . $attribute . " " .
				"and not(contains(@" . $attribute . ", '://')) " .
				"and not(starts-with(@" . $attribute . ", 'mailto:')) " .
				"and not(starts-with(@" . $attribute . ", '//'))]");
			foreach ($xpathResult as $linkNode) {
				$urlElement = $linkNode->attributes->getNamedItem($attribute);
				$abs = $this->relativeToAbsoluteUrl($urlElement->nodeValue, $absoluteUrl);
				$urlElement->nodeValue = htmlspecialchars($abs);
			}
		}

		$xmlString = $dom->saveHTML();

		// domdocument spoils the string with line breaks between the elements. strip them.
		$xmlString = str_replace("\n", '', $xmlString);

		return $xmlString;
	}


	/**
	 * Method which builds a URL by taking a relative URL and its corresponding
	 * absolute URL
	 * For example relative URL "../example/path/file.php?a=1#anchor" and
	 * absolute URL "https://username:password@www.website.com/subfolder/index.html"
	 * will result in "https://username:password@www.website.com/example/path/file.php?a=1#anchor"
	 * @param string $relativeUrl the relative URL
	 * @param string $absoluteUrl the absolute URL with at least scheme and host
	 * @return string the resulting absolute URL
	 */
	protected function relativeToAbsoluteUrl($relativeUrl, $absoluteUrl) {
		$base = new \Net_URL2($absoluteUrl);
		return $base->resolve($relativeUrl);
	}


	/**
	 * Method which turns an xpath result to a string
	 * you can customize it by overwriting this method
	 * @param mixed $xpathResult the result from the xpath query
	 * @return string the result as a string
	 */
	protected function domToString($xpathResult) {
		$result = '';
		foreach($xpathResult as $node) {
			$result .= $this->toInnerHTML($node);
		}
		return $result;
	}


	protected function toInnerHTML($node) {
		$dom = new DOMDocument();
		$dom->appendChild($dom->importNode($node, true));
		return trim($dom->saveHTML($dom->documentElement));
	}


}
