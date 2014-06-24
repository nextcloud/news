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

namespace OCA\News\Utility;

use \OCA\News\Db\Folder;
use \OCA\News\Db\Feed;


class OPMLExporterTest extends \PHPUnit_Framework_TestCase {

	private $exporter;
	private $feed1;
	private $feed2;

	protected function setUp() {
		$this->exporter = new OPMLExporter();
		$this->folder1 = new Folder();
		$this->folder1->setId(3);
		$this->folder1->setParentId(0);
		$this->folder1->setName('Örgendwas');
		$this->folder2 = new Folder();
		$this->folder2->setId(1);
		$this->folder2->setParentId(3);
		$this->folder2->setName('a ergendwas');
		$this->feed1 = new Feed();
		$this->feed1->setUrl('url 1');
		$this->feed1->setTitle('tötel');
		$this->feed1->setFolderId(0);
		$this->feed2 = new Feed();
		$this->feed2->setUrl('url');
		$this->feed2->setTitle('ttel df');
		$this->feed2->setLink('goooooogel');
		$this->feed2->setFolderId(1);
	}


	public function testBuildEmpty(){
		$result = $this->exporter->build([], []);
		$xpath = new \DOMXpath($result);

		$this->assertEquals(0, $xpath->query('//outline')->length);
	}


	public function testBuildReturnsFolders() {
		$result = $this->exporter->build([$this->folder1, $this->folder2], []);
		$xpath = new \DOMXpath($result);
		$elems = $xpath->query('/opml/body/outline');

		$this->assertEquals(2, $elems->length);
		$this->assertEquals($this->folder1->getName(), $elems->item(0)->getAttribute('title'));
		$this->assertEquals($this->folder1->getName(), $elems->item(0)->getAttribute('text'));
		$this->assertEquals($this->folder2->getName(), $elems->item(1)->getAttribute('title'));
		$this->assertEquals($this->folder2->getName(), $elems->item(1)->getAttribute('text'));
	}


	public function testBuildReturnsOnlyOneFeedIfParentFolderNotThere() {
		$result = $this->exporter->build([], [$this->feed1, $this->feed2]);
		$xpath = new \DOMXpath($result);
		$elems = $xpath->query('//outline');

		$this->assertEquals(1, $elems->length);
		$this->assertEquals($this->feed1->getTitle(), $elems->item(0)->getAttribute('title'));
		$this->assertEquals($this->feed1->getTitle(), $elems->item(0)->getAttribute('text'));
		$this->assertEquals($this->feed1->getUrl(), $elems->item(0)->getAttribute('xmlUrl'));
		$this->assertEquals('', $elems->item(0)->getAttribute('htmlUrl'));
	}


	public function testBuildReturnsFeedsAndFolders() {
		$result = $this->exporter->build(
			[$this->folder1, $this->folder2], 
			[$this->feed1, $this->feed2]
		);
		$xpath = new \DOMXpath($result);
		$elems = $xpath->query('/opml/body/outline');

		$this->assertEquals(3, $elems->length);


		$this->assertEquals($this->folder1->getName(), $elems->item(0)->getAttribute('title'));
		$this->assertEquals($this->folder2->getName(), $elems->item(1)->getAttribute('text'));
		$this->assertEquals($this->feed1->getUrl(), $elems->item(2)->getAttribute('xmlUrl'));
		$this->assertEquals($this->feed2->getLink(), $elems->item(1)->childNodes->item(0)->getAttribute('htmlUrl'));
	}


}