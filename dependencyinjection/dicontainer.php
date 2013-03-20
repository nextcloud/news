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

namespace OCA\News\DependencyInjection;

use OCA\AppFramework\DependencyInjection\DIContainer as BaseContainer;

use OCA\News\Controller\FolderController;
use OCA\News\Bl\FolderBl;
use OCA\News\Db\FolderMapper;


class DIContainer extends BaseContainer {


	/**
	 * Define your dependencies in here
	 */
	public function __construct(){
		// tell parent container about the app name
		parent::__construct('news');


		/** 
		 * CONTROLLERS
		 */
		$this['FolderController'] = $this->share(function($c){
			return new FolderController($c['API'], $c['Request'], $c['FolderBl']);
		});

		/**
		 * Business
		 */
		$this['FolderBl'] = $this->share(function($c){
			return new FolderBl($c['FolderMapper']);
		});

		/**
		 * MAPPERS
		 */
		$this['FolderMapper'] = $this->share(function($c){
			return new FolderMapper($c['API']);
		});


	}
}

