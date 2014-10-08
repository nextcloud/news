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

namespace OCA\News\Config;


class AppConfigTest extends \PHPUnit_Framework_TestCase {

	private $nav;
	private $config;
	private $url;

	public function setUp() {
		$this->nav = $this->getMockBuilder('\OCP\INavigationManager')
			->disableOriginalConstructor()
			->getMock();
		$this->l10n = $this->getMockBuilder('\OCP\IL10N')
			->disableOriginalConstructor()
			->getMock();
		$this->url = $this->getMockBuilder('\OCP\IURLGenerator')
			->disableOriginalConstructor()
			->getMock();
		$phpVersion = '5.3';
		$ownCloudVersion = '6.0.3';
		$installedExtensions = ['curl' => '4.3'];
		$databaseType = 'oracle';

		$this->config = new AppConfig($this->nav, $this->l10n,
			$this->url, $phpVersion, $ownCloudVersion,
			$installedExtensions, $databaseType);
	}

	public function testGetId() {
		$this->config->loadConfig(__DIR__ . '/../../../appinfo/app.json');
		$this->assertEquals('news', $this->config->getConfig('id'));
	}


	public function testNoNavigation() {
		$this->config->loadConfig([]);

		$this->nav->expects($this->never())
			->method('add');
	}


	public function testDefaultNavigation() {
		$expected = [
			'id' => 'news',
			'href' => 'news.page.index',
			'order' => 10,
			'icon' => 'app.svg',
			'name' => 'News'
		];

		$this->l10n->expects($this->once())
			->method('t')
			->with($this->equalTo('News'))
			->will($this->returnValue('News'));

		$this->url->expects($this->once())
			->method('linkToRoute')
			->with($this->equalTo('news.page.index'))
			->will($this->returnValue('news.page.index'));

		$this->url->expects($this->once())
			->method('imagePath')
			->with($this->equalTo('news'),
				$this->equalTo('app.svg'))
			->will($this->returnValue('app.svg'));

		$this->nav->expects($this->once())
			->method('add')
			->with($this->equalTo($expected));

		$this->config->loadConfig([
			'id' => 'news',
			'name' => 'News',
			'navigation' => []
		]);
		$this->config->registerNavigation();
	}


	public function testCustomNavigation() {
		$expected = [
			'id' => 'abc',
			'href' => 'abc.page.index',
			'order' => 1,
			'icon' => 'test.svg',
			'name' => 'haha'
		];

		$this->l10n->expects($this->once())
			->method('t')
			->with($this->equalTo('haha'))
			->will($this->returnValue('haha'));

		$this->url->expects($this->once())
			->method('linkToRoute')
			->with($this->equalTo('abc.page.index'))
			->will($this->returnValue('abc.page.index'));

		$this->url->expects($this->once())
			->method('imagePath')
			->with($this->equalTo('abc'),
				$this->equalTo('test.svg'))
			->will($this->returnValue('test.svg'));

		$this->nav->expects($this->once())
			->method('add')
			->with($this->equalTo($expected));

		$this->config->loadConfig([
			'id' => 'abc',
			'name' => 'News',
			'navigation' => $expected
		]);
		$this->config->registerNavigation();
	}


	/**
	 * @expectedException \OCA\News\Config\DependencyException
	 */
	public function testPHPVersion() {
		$this->config->loadConfig([
			'dependencies' => [
				'php' => '5.7'
			]
		]);
		$this->config->testDependencies();
	}


	/**
	 * @expectedException \OCA\News\Config\DependencyException
	 */
	public function testLibsVersion() {
		$this->config->loadConfig([
			'dependencies' => [
				'libs' => [
					'curl' => '>=4.3,<=4.3'
				]
			]
		]);
		$this->config->testDependencies();
	}


	/**
	 * @expectedException \OCA\News\Config\DependencyException
	 */
	public function testLibsExistence() {
		$this->config->loadConfig([
			'dependencies' => [
				'libs' => [
					'dope' => '>=4.3,<=4.3'
				]
			]
		]);
		$this->config->testDependencies();
	}



	/**
	 * @expectedException \OCA\News\Config\DependencyException
	 */
	public function testSupportedDb() {
		$this->config->loadConfig([
			'databases' => [
				'pgsql', 'sqlite'
			]
		]);
		$this->config->testDependencies();
	}
}