<?php
/**
* ownCloud - News app
*
* @author Bernhard Posselt
* Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
*
* This file is licensed under the Affero General Public License version 3 or later.
* See the COPYING-README file
*
*/

namespace OCA\News;


/**
 * Encapsulates user id, $_GET and $_POST arrays for better testability
 */
class Request {

	private $get;
	private $post;
	private $userId;
	private $files;

	/**
	 * @param string $userId: the id of the current user
	 * @param array $get: the $_GET array
	 * @param array $post: the $_POST array
	 * @param array $files the $_FILES array
	 */
	public function __construct($userId, $get=array(), $post=array(), $files=array()) {
		$this->get = $get;
		$this->post = $post;
		$this->userId = $userId;
		$this->files = $files;
	}


	/**
	 * Returns the get value or the default if not found
	 * @param string $key: the array key that should be looked up
	 * @param string $default: if the key is not found, return this value
	 * @return the value of the stored array
	 */
	public function getGET($key, $default=null){
		if(isset($this->get[$key])){
			return $this->get[$key];
		} else {
			return $default;
		}
	}


	/**
	 * Returns the get value of the files array
	 * @param string $key: the array key that should be looked up
	 * @return the value of the stored array
	 */
	public function getFILES($key){
		if(isset($this->files[$key])){
			return $this->files[$key];
		} else {
			return null;
		}
	}


	/**
	 * Returns the get value or the default if not found
	 * @param string $key: the array key that should be looked up
	 * @param string $default: if the key is not found, return this value
	 * @return the value of the stored array
	 */
	public function getPOST($key, $default=null){
		if(isset($this->post[$key])){
			return $this->post[$key];
		} else {
			return $default;
		}
	}

}
