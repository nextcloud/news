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

namespace OCA\News\App;

use \OC\Files\View;
use \OCP\AppFramework\App;


use \OCA\News\Core\API;

use \OCA\News\Controller\PageController;
use \OCA\News\Controller\FolderController;
use \OCA\News\Controller\FeedController;
use \OCA\News\Controller\ItemController;
use \OCA\News\Controller\ExportController;
use \OCA\News\Controller\ApiController;
use \OCA\News\Controller\FolderApiController;
use \OCA\News\Controller\FeedApiController;
use \OCA\News\Controller\ItemApiController;

use \OCA\News\BusinessLayer\FolderBusinessLayer;
use \OCA\News\BusinessLayer\FeedBusinessLayer;
use \OCA\News\BusinessLayer\ItemBusinessLayer;

use \OCA\News\Db\FolderMapper;
use \OCA\News\Db\FeedMapper;
use \OCA\News\Db\ItemMapper;
use \OCA\News\Db\StatusFlag;
use \OCA\News\Db\MapperFactory;

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

// to prevent clashes with installed app framework versions
if(!class_exists('\SimplePie')) {
	require_once __DIR__ . '/../3rdparty/simplepie/autoloader.php';
}


class News extends App {

	public function __construct(array $urlParams=array()){
		parent::__construct('news', $urlParams);

		$container = $this->getContainer();


		/**
		 * Controllers
		 */
		$container->registerService('PageController', function($c) {
			return new PageController(
				$c->query('API'), 
				$c->query('Request')
			);
		});

		$container->registerService('FolderController', function($c) {
			return new FolderController(
				$c->query('API'), 
				$c->query('Request'),
				$c->query('FolderBusinessLayer'),
				$c->query('FeedBusinessLayer'),
				$c->query('ItemBusinessLayer')
			);
		});

		$container->registerService('FeedController', function($c) {
			return new FeedController(
				$c->query('API'), 
				$c->query('Request'),
				$c->query('FolderBusinessLayer'),
				$c->query('FeedBusinessLayer'),
				$c->query('ItemBusinessLayer')
			);
		});

		$container->registerService('ItemController', function($c) {
			return new ItemController(
				$c->query('API'), 
				$c->query('Request'),
				$c->query('FeedBusinessLayer'),
				$c->query('ItemBusinessLayer')
			);
		});

		$container->registerService('ExportController', function($c) {
			return new ExportController(
				$c->query('API'), 
				$c->query('Request'),
				$c->query('FeedBusinessLayer'),
				$c->query('FolderBusinessLayer'),
				$c->query('ItemBusinessLayer'),
				$c->query('OPMLExporter'));
		});

		$container->registerService('ApiController', function($c) {
			return new ApiController(
				$c->query('API'), 
				$c->query('Request'), 
				$c->query('Updater')
			);
		});

		$container->registerService('FolderApiController', function($c) {
			return new FolderApiController(
				$c->query('API'), 
				$c->query('Request'),
				$c->query('FolderBusinessLayer'),
				$c->query('ItemBusinessLayer')
			);
		});

		$container->registerService('FeedApiController', function($c) {
			return new FeedApiController(
				$c->query('API'), 
				$c->query('Request'),
				$c->query('FolderBusinessLayer'),
				$c->query('FeedBusinessLayer'),
				$c->query('ItemBusinessLayer')
			);
		});

		$container->registerService('ItemApiController', function($c) {
			return new ItemApiController(
				$c->query('API'), 
				$c->query('Request'),
				$c->query('ItemBusinessLayer')
			);
		});


		/**
		 * Business Layer
		 */
		$container->registerService('FolderBusinessLayer', function($c) {
			return new FolderBusinessLayer(
				$c->query('FolderMapper'),
				$c->query('API'),
				$c->query('TimeFactory'),
				$c->query('Config')
			);
		});

		$container->registerService('FeedBusinessLayer', function($c) {
			return new FeedBusinessLayer(
				$c->query('FeedMapper'),
				$c->query('Fetcher'),
				$c->query('ItemMapper'),
				$c->query('API'),
				$c->query('TimeFactory'),
				$c->query('Config'),
				$c->query('Enhancer'),
				$c->query('HTMLPurifier')
			);
		});

		$container->registerService('ItemBusinessLayer', function($c) {
			return new ItemBusinessLayer(
				$c->query('ItemMapper'),
				$c->query('StatusFlag'),
				$c->query('TimeFactory'),
				$c->query('Config')
			);
		});


		/**
		 * Mappers
		 */
		$container->registerService('MapperFactory', function($c) {
			return new MapperFactory(
				$c->query('API')
			);
		});

		$container->registerService('FolderMapper', function($c) {
			return new FolderMapper(
				$c->query('API')
			);
		});

		$container->registerService('FeedMapper', function($c) {
			return new FeedMapper(
				$c->query('API')
			);
		});

		$container->registerService('ItemMapper', function($c) {
			return $c->query('MapperFactory')->getItemMapper(
				$c->query('API')
			);
		});
		

		/**
		 * Utility
		 */
		$container->registerService('API', function($c){
			return new API(
				$c->query('news')
			);
		});

		$container->registerService('ConfigView', function($c) {
			$view = new View('/news/config');
			if (!$view->file_exists('')) {
				$view->mkdir('');
			}

			return $view;
		});

		$container->registerService('Config', function($c) {
			$config = new Config($c->query('ConfigView'), $c->query('API'));
			$config->read('config.ini', true);
			return $config;
		});

		$container->registerService('simplePieCacheDirectory', function($c) {
			$directory = $c->query('API')->getSystemValue('datadirectory') .
				'/news/cache/simplepie';

			if(!is_dir($directory)) {
				mkdir($directory, 0770, true);
			}
			return $directory;
		});

		$container->registerService('HTMLPurifier', function($c) {
			$directory = $c->query('API')->getSystemValue('datadirectory') .
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

		$container->registerService('Enhancer', function($c) {
			$enhancer = new Enhancer();

			// register simple enhancers from config json file
			$xpathEnhancerConfig = file_get_contents(
				__DIR__ . '/../articleenhancer/xpathenhancers.json'
			);
			
			foreach(json_decode($xpathEnhancerConfig, true) as $feed => $config) {
				$articleEnhancer = new XPathArticleEnhancer(
					$c->query('SimplePieAPIFactory'),
					$config,
					$c->query('Config')
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

		/**
		 * Fetchers
		 */
		$container->registerService('Fetcher', function($c) {
			$fetcher = new Fetcher();

			// register fetchers in order
			// the most generic fetcher should be the last one
			$fetcher->registerFetcher($c->query('FeedFetcher'));

			return $fetcher;
		});

		$container->registerService('FeedFetcher', function($c) {
			return new FeedFetcher(
				$c->query('API'),
				$c->query('SimplePieAPIFactory'),
				$c->query('FaviconFetcher'),
				$c->query('TimeFactory'),
				$c->query('simplePieCacheDirectory'),
				$c->query('Config')
			);
		});

		$container->registerService('StatusFlag', function($c) {
			return new StatusFlag();
		});

		$container->registerService('OPMLExporter', function($c) {
			return new OPMLExporter();
		});

		$container->registerService('Updater', function($c) {
			return new Updater(
				$c->query('FolderBusinessLayer'),
				$c->query('FeedBusinessLayer'),
				$c->query('ItemBusinessLayer')
			);
		});

		$container->registerService('SimplePieAPIFactory', function($c) {
			return new SimplePieAPIFactory();
		});

		$container->registerService('FaviconFetcher', function($c) {
			return new FaviconFetcher(
				$c->query('SimplePieAPIFactory')
			);
		});

		/** 
		 * Middleware
		 */
		$container->registerService('CORSMiddleware', function($c) {
			return new CORSMiddleware(
				$c->query('Request')
			);
		});		

		$container->registerMiddleWare($container['CORSMiddleware']);

	}
}

