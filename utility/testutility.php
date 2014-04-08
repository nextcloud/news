<?php

/**
 * ownCloud - News
 *
 * @author Bernhard Posselt
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


namespace OCA\News\Utility;

/**
 * Simple utility class for testing anything using an api
 */
abstract class TestUtility extends \PHPUnit_Framework_TestCase {


	/**
	 * Boilerplate function for getting an API Mock class
	 * @param string $apiClass the class inclusive namespace of the api that we
	 *                          want to use
	 * @param array $constructor constructor parameters of the api class
	 */
	protected function getAPIMock($apiClass='OCA\News\Core\API',
									array $constructor=array('appname')){
		$methods = get_class_methods($apiClass);
		return $this->getMock($apiClass, $methods, $constructor);
	}


}