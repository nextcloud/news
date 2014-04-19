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
		$feedsDict = array();
		foreach($feeds as $feed) {
			$feedsDict['feed' . $feed->getId()] = $feed;
		}

		$articles = array();
		foreach($items as $item) {
			array_push($articles, $item->toExport($feedsDict));
		}
		
		$response = new JSONResponse($articles);
		$response->addHeader('Content-Disposition', 
			'attachment; filename="articles.json"');
		return $response;
	}


}