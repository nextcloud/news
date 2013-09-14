<?php

namespace OCA\News\Utility\ArticleEnhancer;

use \OCA\News\Utility\SimplePieFileFactory;

class ThemeRepublicEnhancer extends ArticleEnhancer {

	public function __construct(SimplePieFileFactory $fileFactory, $purifier,
	                            $timeout){
		parent::__construct(
			$purifier,
			$fileFactory,
			array(
				'/feedproxy.google.com\/~r\/blogspot\/DngUJ/' => "//*[@class='post hentry']"
			), 
			$timeout
		);
	}
}
?>