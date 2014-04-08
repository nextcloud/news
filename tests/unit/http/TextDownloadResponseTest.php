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


namespace OCA\News\Http;


require_once(__DIR__ . "/../../classloader.php");
require_once(__DIR__ . "/DownloadResponseTest.php");


class TextDownloadResponseTest extends DownloadResponseTest {


	protected function setUp() {
		$this->response = new TextDownloadResponse('sometext', 'file', 'content');
	}


	public function testRender() {
		$this->assertEquals('sometext', $this->response->render());
	}

}