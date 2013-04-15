<?php

/**
* ownCloud - News
*
* @author Alessandro Cosentino
* @author Bernhard Posselt
* @copyright 2012 Alessandro Cosentino cosenal@gmail.com
* @copyright 2012 Bernhard Posselt nukeawhale@gmail.com
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

use \OCA\AppFramework\Controller\Controller;
use \OCA\AppFramework\Core\API;
use \OCA\AppFramework\Http\Request;
use \OCA\AppFramework\Http\TextDownloadResponse;

use \OCA\News\BusinessLayer\FeedBusinessLayer;
use \OCA\News\BusinessLayer\FolderBusinessLayer;
use \OCA\News\Utility\OPMLExporter;

class ExportController extends Controller {

	private $opmlExporter;
	private $folderBusinessLayer;
	private $feedBusinessLayer;

	public function __construct(API $api, Request $request,
	                            FeedBusinessLayer $feedBusinessLayer,
	                            FolderBusinessLayer $folderBusinessLayer,
	                            OPMLExporter $opmlExporter){
		parent::__construct($api, $request);
		$this->feedBusinessLayer = $feedBusinessLayer;
		$this->folderBusinessLayer = $folderBusinessLayer;
		$this->opmlExporter = $opmlExporter;
	}


	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @CSRFExemption
	 */
	public function opml(){
		$user = $this->api->getUserId();
		$feeds = $this->feedBusinessLayer->findAll($user);
		$folders = $this->folderBusinessLayer->findAll($user);
		$opml = $this->opmlExporter->build($folders, $feeds)->saveXML();
		return new TextDownloadResponse($opml, 'subscriptions.opml', 'text/xml');
	}


}