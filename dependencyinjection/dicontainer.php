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

namespace OCA\News\DependencyInjection;

use \OC\Files\View;

use \OCA\AppFramework\DependencyInjection\DIContainer as BaseContainer;
use \OCA\AppFramework\Middleware\MiddlewareDispatcher;

use \OCA\News\Controller\PageController;
use \OCA\News\Controller\FolderController;
use \OCA\News\Controller\FeedController;
use \OCA\News\Controller\ItemController;
use \OCA\News\Controller\ExportController;
use \OCA\News\Controller\UserSettingsController;

use \OCA\News\BusinessLayer\FolderBusinessLayer;
use \OCA\News\BusinessLayer\FeedBusinessLayer;
use \OCA\News\BusinessLayer\ItemBusinessLayer;

use \OCA\News\Db\FolderMapper;
use \OCA\News\Db\FeedMapper;
use \OCA\News\Db\ItemMapper;
use \OCA\News\Db\StatusFlag;
use \OCA\News\Db\MapperFactory;

use \OCA\News\API\NewsAPI;
use \OCA\News\API\FolderAPI;
use \OCA\News\API\FeedAPI;
use \OCA\News\API\ItemAPI;

use \OCA\News\Utility\Config;
use \OCA\News\Utility\OPMLExporter;
use \OCA\News\Utility\Updater;
use \OCA\News\Utility\SimplePieAPIFactory;
use \OCA\News\Utility\TimeFactory;
use \OCA\News\Utility\FaviconFetcher;

use \OCA\News\Fetcher\Fetcher;
use \OCA\News\Fetcher\FeedFetcher;

use \OCA\News\ArticleEnhancer\Enhancer;
use \OCA\News\ArticleEnhancer\XPathArticleEnhancer;
use \OCA\News\ArticleEnhancer\RegexArticleEnhancer;

use \OCA\News\Middleware\CORSMiddleware;

require_once __DIR__ . '/../3rdparty/htmlpurifier/library/HTMLPurifier.auto.php';

// uncomment once appframework not required anymore
//require_once __DIR__ . '/../3rdparty/simplepie/autoloader.php';

class DIContainer extends BaseContainer {


	/**
	 * Define your dependencies in here
	 */
	public function __construct(){
		// tell parent container about the app name
		parent::__construct('news');

		/**
		 * Configuration values
		 */
		$this['configView'] = $this->share(function($c) {
			$view = new View('/news/config');
			if (!$view->file_exists('')) {
				$view->mkdir('');
			}

			return $view;
		});

		$this['Config'] = $this->share(function($c) {
			$config = new Config($c['configView'], $c['API']);
			$config->read('config.ini', true);
			return $config;
		});

		// set by config class from config file
		// look up defaults in utility/config.php
		$this['autoPurgeMinimumInterval'] = $this['Config']->getAutoPurgeMinimumInterval();
		$this['autoPurgeCount'] = $this['Config']->getAutoPurgeCount();
		$this['simplePieCacheDuration'] = $this['Config']->getSimplePieCacheDuration();
		$this['feedFetcherTimeout'] = $this['Config']->getFeedFetcherTimeout();
		$this['useCronUpdates'] = $this['Config']->getUseCronUpdates();


		$this['simplePieCacheDirectory'] = $this->share(function($c) {
			$directory = $c['API']->getSystemValue('datadirectory') .
				'/news/cache/simplepie';

			if(!is_dir($directory)) {
				mkdir($directory, 0770, true);
			}
			return $directory;
		});

		$this['HTMLPurifier'] = $this->share(function($c) {
			$directory = $c['API']->getSystemValue('datadirectory') .
				'/news/cache/purifier';

			if(!is_dir($directory)) {
				mkdir($directory, 0770, true);
			}

			$config = \HTMLPurifier_Config::createDefault();
			$config->set('HTML.ForbiddenAttributes', 'class');
			$config->set('Cache.SerializerPath', $directory);
			$config->set('HTML.SafeIframe', true);
			$config->set('URI.SafeIframeRegexp',
				'%^(?:https?:)?//(' . 
				'www.youtube(?:-nocookie)?.com/embed/|' .
				'player.vimeo.com/video/)%'); //allow YouTube and Vimeo
			return new \HTMLPurifier($config);
		});


		/**
		 * CONTROLLERS
		 */
		$this['PageController'] = $this->share(function($c){
			return new PageController($c['API'], $c['Request']);
		});

		$this['FolderController'] = $this->share(function($c){
			return new FolderController($c['API'], $c['Request'],
				$c['FolderBusinessLayer'],
				$c['FeedBusinessLayer'],
				$c['ItemBusinessLayer']);
		});

		$this['FeedController'] = $this->share(function($c){
			return new FeedController($c['API'], $c['Request'],
				$c['FolderBusinessLayer'],
				$c['FeedBusinessLayer'],
				$c['ItemBusinessLayer']);
		});

		$this['ItemController'] = $this->share(function($c){
			return new ItemController($c['API'], $c['Request'],
				$c['FeedBusinessLayer'],
				$c['ItemBusinessLayer']);
		});

		$this['ExportController'] = $this->share(function($c){
			return new ExportController($c['API'], $c['Request'],
			                            $c['FeedBusinessLayer'],
				                        $c['FolderBusinessLayer'],
				                        $c['ItemBusinessLayer'],
				                        $c['OPMLExporter']);
		});

		$this['UserSettingsController'] = $this->share(function($c){
			return new UserSettingsController($c['API'], $c['Request'],
					$c['ItemBusinessLayer']);
		});


		/**
		 * Business Layer
		 */
		$this['FolderBusinessLayer'] = $this->share(function($c){
			return new FolderBusinessLayer(
				$c['FolderMapper'],
				$c['API'],
				$c['TimeFactory'],
				$c['autoPurgeMinimumInterval']);
		});

		$this['FeedBusinessLayer'] = $this->share(function($c){
			return new FeedBusinessLayer(
				$c['FeedMapper'],
				$c['Fetcher'],
				$c['ItemMapper'],
				$c['API'],
				$c['TimeFactory'],
				$c['autoPurgeMinimumInterval'],
				$c['Enhancer'],
				$c['HTMLPurifier']);
		});

		$this['ItemBusinessLayer'] = $this->share(function($c){
			return new ItemBusinessLayer(
				$c['ItemMapper'],
				$c['StatusFlag'],
				$c['TimeFactory'],
				$c['autoPurgeCount']);
		});


		/**
		 * MAPPERS
		 */
		$this['MapperFactory'] = $this->share(function($c){
			return new MapperFactory($c['API']);
		});

		$this['FolderMapper'] = $this->share(function($c){
			return new FolderMapper($c['API']);
		});

		$this['FeedMapper'] = $this->share(function($c){
			return new FeedMapper($c['API']);
		});

		$this['ItemMapper'] = $this->share(function($c){
			return $c['MapperFactory']->getItemMapper($c['API']);
		});


		/**
		 * External API
		 */
		$this['NewsAPI'] = $this->share(function($c){
			return new NewsAPI($c['API'], $c['Request'], $c['Updater']);
		});

		$this['FolderAPI'] = $this->share(function($c){
			return new FolderAPI($c['API'], $c['Request'],
			                     $c['FolderBusinessLayer'],
			                     $c['ItemBusinessLayer']);
		});

		$this['FeedAPI'] = $this->share(function($c){
			return new FeedAPI($c['API'], $c['Request'],
			                   $c['FolderBusinessLayer'],
			                   $c['FeedBusinessLayer'],
			                   $c['ItemBusinessLayer']);
		});

		$this['ItemAPI'] = $this->share(function($c){
			return new ItemAPI($c['API'], $c['Request'],
			                   $c['ItemBusinessLayer']);
		});

		/**
		 * Utility
		 */
		$this['Enhancer'] = $this->share(function($c){
			$enhancer = new Enhancer();

			// register simple enhancers from config json file
			$xpathEnhancerConfig = file_get_contents(
				__DIR__ . '/../articleenhancer/xpathenhancers.json'
			);
			
			foreach(json_decode($xpathEnhancerConfig, true) as $feed => $config) {
				$articleEnhancer = new XPathArticleEnhancer(
					$c['SimplePieAPIFactory'],
					$config,
					$c['feedFetcherTimeout']
				);
				$enhancer->registerEnhancer($feed, $articleEnhancer);
			}

			$regexEnhancerConfig = file_get_contents(
				__DIR__ . '/../articleenhancer/regexenhancers.json'
			);
			foreach(json_decode($regexEnhancerConfig, true) as $feed => $config) {
				foreach ($config as $matchArticleUrl => $regex) {
					$articleEnhancer = new RegexArticleEnhancer($matchArticleUrl, $regex);
					$enhancer->registerEnhancer($feed, $articleEnhancer);
				}
			}

			return $enhancer;
		});

		$this['Fetcher'] = $this->share(function($c){
			$fetcher = new Fetcher();

			// register fetchers in order
			// the most generic fetcher should be the last one
			$fetcher->registerFetcher($c['FeedFetcher']);

			return $fetcher;
		});

		$this['FeedFetcher'] = $this->share(function($c){
			return new FeedFetcher(
				$c['API'],
				$c['SimplePieAPIFactory'],
				$c['FaviconFetcher'],
				$c['TimeFactory'],
				$c['simplePieCacheDirectory'],
				$c['simplePieCacheDuration'],
				$c['feedFetcherTimeout']);
		});

		$this['StatusFlag'] = $this->share(function($c){
			return new StatusFlag();
		});

		$this['OPMLExporter'] = $this->share(function($c){
			return new OPMLExporter();
		});

		$this['Updater'] = $this->share(function($c){
			return new Updater($c['FolderBusinessLayer'],
			                   $c['FeedBusinessLayer'],
			                   $c['ItemBusinessLayer']);
		});

		$this['SimplePieAPIFactory'] = $this->share(function($c){
			return new SimplePieAPIFactory();
		});

		$this['FaviconFetcher'] = $this->share(function($c){
			return new FaviconFetcher($c['SimplePieAPIFactory']);
		});

		$this['TimeFactory'] = $this->share(function($c){
			return new TimeFactory();
		});


		/** 
		 * Middleware
		 */
		$this['MiddlewareDispatcher'] = $this->share(function($c){
			$dispatcher = new MiddlewareDispatcher();
			$dispatcher->registerMiddleware($c['HttpMiddleware']);
			$dispatcher->registerMiddleware($c['SecurityMiddleware']);
			$dispatcher->registerMiddleware($c['CORSMiddleware']);
			return $dispatcher;
		});

		$this['CORSMiddleware'] = $this->share(function($c){
			return new CORSMiddleware($c['Request']);
		});		

	}
}

