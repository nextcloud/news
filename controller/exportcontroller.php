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

namespace OCA\News\Controller;

use \OCP\IRequest;
use \OCP\AppFramework\Controller;
use \OCP\AppFramework\Http;
use \OCP\AppFramework\Http\JSONResponse;
use \OCP\AppFramework\Http\Response;

use \OCA\News\Http\TextDownloadResponse;
use \OCA\News\BusinessLayer\FeedBusinessLayer;
use \OCA\News\BusinessLayer\FolderBusinessLayer;
use \OCA\News\BusinessLayer\ItemBusinessLayer;
use \OCA\News\Utility\OPMLExporter;

class ExportController extends Controller {

	private $opmlExporter;
	private $folderBusinessLayer;
	private $feedBusinessLayer;
	private $itemBusinessLayer;
	private $userId;

	public function __construct($appName, 
	                            IRequest $request,
	                            FeedBusinessLayer $feedBusinessLayer,
	                            FolderBusinessLayer $folderBusinessLayer,
	                            ItemBusinessLayer $itemBusinessLayer,
	                            OPMLExporter $opmlExporter,
	                            $userId){
		parent::__construct($appName, $request);
		$this->feedBusinessLayer = $feedBusinessLayer;
		$this->folderBusinessLayer = $folderBusinessLayer;
		$this->opmlExporter = $opmlExporter;
		$this->itemBusinessLayer = $itemBusinessLayer;
		$this->userId = $userId;
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function opml(){
		$feeds = $this->feedBusinessLayer->findAll($this->userId);
		$folders = $this->folderBusinessLayer->findAll($this->userId);
		$opml = $this->opmlExporter->build($folders, $feeds)->saveXML();
		return new TextDownloadResponse($opml, 'subscriptions.opml', 'text/xml');
	}


	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function articles(){
		$feeds = $this->feedBusinessLayer->findAll($this->userId);
		$items = $this->itemBusinessLayer->getUnreadOrStarred($this->userId);

		// build assoc array for fast access
		$feedsDict = [];
		foreach($feeds as $feed) {
			$feedsDict['feed' . $feed->getId()] = $feed;
		}

		$articles = [];
		foreach($items as $item) {
			$articles[] = $item->toExport($feedsDict);
		}
		
		$response = new JSONResponse($articles);
		$response->addHeader('Content-Disposition', 
			'attachment; filename="articles.json"');
		return $response;
	}


}