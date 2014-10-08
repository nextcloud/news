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

namespace OCA\News\Utility;

use \ZendXml\Security;


class FaviconFetcher {

	private $apiFactory;

	/**
	 * Inject a factory to build a simplepie file object. This is needed because
	 * the file object contains logic in its constructor which makes it
	 * impossible to inject and test
	 */
	public function __construct(SimplePieAPIFactory $apiFactory) {
		$this->apiFactory = $apiFactory;
	}


    /**
     * Fetches a favicon from a given URL
     *
     * @param string|null $url the url where to fetch it from
     * @return null|string
     */
	public function fetch($url) {
		try {
			$url = $this->buildURL($url);
		} catch (NoValidUrlException $e) {
			return null;
		}

		$faviconUrl = $this->extractFromPage($url);

		// check the url for a valid image
		if($faviconUrl && $this->isImage($faviconUrl)) {
			return $faviconUrl;
		} elseif ($url) {
			$base = new \Net_URL2($url);
			$faviconUrl = (string) $base->resolve('/favicon.ico');

			if($this->isImage($faviconUrl)) {
				return $faviconUrl;
			}
		}

		return null;
	}

	/**
	 * Get the attribute if its a DOMElement, otherwise return null
	 */
	private function getAttribute($item, $name) {
		// used to fix scrutinizer errors
		if ($item instanceof \DOMElement) {
			return $item->getAttribute($name);
		} else {
			return null;
		}
	}


	/**
	 * Tries to get a favicon from a page
	 * @param string $url the url to the page
	 * @return string the full url to the page
	 */
	protected function extractFromPage($url) {
		if(!$url) {
			return null;
		}

		$file = $this->getFile($url);

        /** @noinspection PhpUndefinedFieldInspection */
        if($file->body !== '') {
			$dom = new \DOMDocument();

			Security::scan($file->body, $dom, function ($xml, $dom) {
				return @$dom->loadHTML($xml, LIBXML_NONET);
			});

			if($dom) {
				$xpath = new \DOMXpath($dom);
				$elements = $xpath->query("//link[contains(@rel, 'icon')]");

				if ($elements->length > 0) {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $iconPath = $this->getAttribute($elements->item(0), 'href');
                    $base = new \Net_URL2($url);
					$absPath = (string) $base->resolve($iconPath, $url);
					return $absPath;
				}
			}
		}

        return null;
	}


	private function getFile($url) {
		return $this->apiFactory->getFile(
			$url, 10, 5, null, 'Mozilla/5.0 AppleWebKit'
		);
	}


	/**
	 * Test if the file is an image
	 * @param string $url the url to the file
	 * @return bool true if image
	 */
	protected function isImage($url) {
		// check for empty urls
		if(!$url) {
			return false;
		}

		$file = $this->getFile($url);
        /** @noinspection PhpParamsInspection */
        $sniffer = new \SimplePie_Content_Type_Sniffer($file);
		return $sniffer->image() !== false;
	}


	/**
	 * Get HTTP or HTTPS addresses from an incomplete URL
	 * @param string $url the url that should be built
	 * @return string a string containing the http or https address
	 * @throws NoValidUrlException when no valid url can be returned
	 */
	protected function buildURL($url) {
		// trim the right / from the url
		$url = trim($url);
		$url = rtrim($url, '/');

		// check for http:// or https:// and validate URL
		if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) {
			if (filter_var($url, FILTER_VALIDATE_URL)) {
				return $url;
			}
		} elseif (filter_var("http://" . $url, FILTER_VALIDATE_URL)) {
			// maybe $url was something like www.example.com
			return 'http://' . $url;
		}

		// no valid URL was passed in or could be built from $url
		throw new NoValidUrlException();
	}

}
