<?php
/**
* ownCloud - News app
*
* @author Alessandro Copyright
* @author Bernhard Posselt
* @copyright 2012 Alessandro Cosentino cosenal@gmail.com
* @copyright 2012 Bernhard Posselt nukeawhale@gmail.com                    
*
* This file is licensed under the Affero General Public License version 3 or later.
* See the COPYING-README file
*
*/

namespace OCA\News;

\OC::$CLASSPATH['Pimple'] = 'apps/news/3rdparty/Pimple/Pimple.php';

\OC::$CLASSPATH['OC_Search_Provider_News'] = 'apps/news/lib/search.php';
\OC::$CLASSPATH['OCA\News\Backgroundjob'] = 'apps/news/lib/backgroundjob.php';
\OC::$CLASSPATH['OCA\News\Share_Backend_News_Item'] = 'apps/news/lib/share/item.php';
\OC::$CLASSPATH['OCA\News\Utils'] = 'apps/news/lib/utils.php';
\OC::$CLASSPATH['OCA\News\Security'] = 'apps/news/lib/security.php';
\OC::$CLASSPATH['OCA\News\API'] = 'apps/news/lib/api.php';
\OC::$CLASSPATH['OCA\News\Request'] = 'apps/news/lib/request.php';
\OC::$CLASSPATH['OCA\News\TemplateResponse'] = 'apps/news/lib/response.php';
\OC::$CLASSPATH['OCA\News\JSONResponse'] = 'apps/news/lib/response.php';
\OC::$CLASSPATH['OCA\News\TextDownloadResponse'] = 'apps/news/lib/response.php';
\OC::$CLASSPATH['OCA\News\Controller'] = 'apps/news/lib/controller.php';

\OC::$CLASSPATH['OCA\News\OPMLParser'] = 'apps/news/opmlparser.php';
\OC::$CLASSPATH['OCA\News\OPMLExporter'] = 'apps/news/opmlexporter.php';
\OC::$CLASSPATH['OCA\News\OPMLImporter'] = 'apps/news/opmlimporter.php';

\OC::$CLASSPATH['OCA\News\Enclosure'] = 'apps/news/db/enclosure.php';
\OC::$CLASSPATH['OCA\News\FeedMapper'] = 'apps/news/db/feedmapper.php';
\OC::$CLASSPATH['OCA\News\ItemMapper'] = 'apps/news/db/itemmapper.php';
\OC::$CLASSPATH['OCA\News\FolderMapper'] = 'apps/news/db/foldermapper.php';
\OC::$CLASSPATH['OCA\News\Folder'] = 'apps/news/db/folder.php';
\OC::$CLASSPATH['OCA\News\Feed'] = 'apps/news/db/feed.php';
\OC::$CLASSPATH['OCA\News\Item'] = 'apps/news/db/item.php';
\OC::$CLASSPATH['OCA\News\Collection'] = 'apps/news/db/collection.php';
\OC::$CLASSPATH['OCA\News\FeedType'] = 'apps/news/db/feedtype.php';
\OC::$CLASSPATH['OCA\News\StatusFlag'] = 'apps/news/db/statusflag.php';

\OC::$CLASSPATH['OCA\News\NewsController'] = 'apps/news/controller/news.controller.php';
\OC::$CLASSPATH['OCA\News\NewsAjaxController'] = 'apps/news/controller/news.ajax.controller.php';

\OC::$CLASSPATH['OCA\News\FolderBL'] = 'apps/news/folder.bl.php';

\OC::$CLASSPATH['OCA\News\API_Folder'] = 'apps/news/external_api/folder.php';


/**
 * @return a new DI container with prefilled values for the news app
 */
function createDIContainer(){
	$newsContainer = new \Pimple();

	/** 
	 * CONSTANTS
	 */
	$newsContainer['AppName'] = 'news';


	/** 
	 * CLASSES
	 */
	$newsContainer['API'] = $newsContainer->share(function($c){
		return new API($c['AppName']);
	});


	$newsContainer['Request'] = $newsContainer->share(function($c){
		return new Request($_GET, $_POST, $_FILES);
	});


	$newsContainer['Security'] = $newsContainer->share(function($c) {
		return new Security($c['AppName']);	
	});


	/** 
	 * MAPPERS
	 */
	$newsContainer['ItemMapper'] = $newsContainer->share(function($c){
		return new ItemMapper($c['API']->getUserId());
	});

	$newsContainer['FeedMapper'] = $newsContainer->share(function($c){
		return new FeedMapper($c['API']->getUserId());
	});

	$newsContainer['FolderMapper'] = $newsContainer->share(function($c){
		return new FolderMapper($c['API']->getUserId());
	});


	/** 
	 * CONTROLLERS
	 */
	$newsContainer['NewsController'] = function($c){
		return new NewsController($c['Request'], $c['API'], $c['FeedMapper'], 
									$c['FolderMapper']);
	};

	$newsContainer['NewsAjaxController'] = function($c){
		return new NewsAjaxController($c['Request'], $c['API'], $c['FeedMapper'], 
										$c['FolderMapper'], $c['ItemMapper']);
	};

	/** 
	 * BUSINESS LAYER OBJECTS
	 */
	$newsContainer['FolderBL'] = function($c){ 
		return new FolderBL($c['FolderMapper']);
	};

	return $newsContainer;
}