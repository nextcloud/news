<?php

/**
* ownCloud - News
*
* @author Alessandro Copyright
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
use \OCA\AppFramework\Db\DoesNotExistException;
use \OCA\AppFramework\Db\MultipleObjectsReturnedException;


class FolderController extends Controller {


	public function __construct(API $api, Request $request, $folderMapper){
		parent::__construct($api, $request);
		$this->folderMapper = $folderMapper;
	}


	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 *
	 * Returns all folders
	 */
	public function getAll(){
		$folders = $this->folderMapper->getAll();
		return $this->renderJSON($folders);
	}


	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 * @Ajax
	 *
	 * Collapses a folder
	 */
	public function collapse(){
		$folderId = (int) $this->params('folderId');

		try {
			$this->folderMapper->setCollapsed($folderId, true);
			return $this->renderJSON(array());
		} catch (DoesNotExistException $e) {
			return $this->renderJSON(array(), $e->getMessage());
                } catch(MultipleObjectsReturnedException $e){
                        return $this->renderJSON(array(), $e->getMessage());
		}
	}


}