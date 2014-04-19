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


require_once(__DIR__ . "/../../classloader.php");


class MethodAnnotationReaderTest extends \PHPUnit_Framework_TestCase {


	/**
	 * @Annotation
	 */
	public function testReadAnnotation(){
		$reader = new MethodAnnotationReader('\OCA\News\Utility\MethodAnnotationReaderTest',
				'testReadAnnotation');

		$this->assertTrue($reader->hasAnnotation('Annotation'));
	}


	/**
	 * @Annotation
	 * @param test
	 */
	public function testReadAnnotationNoLowercase(){
		$reader = new MethodAnnotationReader('\OCA\News\Utility\MethodAnnotationReaderTest',
				'testReadAnnotationNoLowercase');

		$this->assertTrue($reader->hasAnnotation('Annotation'));
		$this->assertFalse($reader->hasAnnotation('param'));
	}


}