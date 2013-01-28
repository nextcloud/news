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
 * This class is a simple object with getters and setters and allows
 * finegrained controll over security checks
 * All security checks are enabled by default
 */
class Security {

	private $csrfCheck;
	private $loggedInCheck;
	private $appEnabledCheck;
	private $isAdminCheck;
	private $appName;

	/**
	 * @param string $appName: the name of the app
	 */
	public function __construct($appName){
		$this->appName = $appName;

		// enable all checks by default
		$this->csrfCheck = true;
		$this->loggedInCheck = true;
		$this->appEnabledCheck = true;
		$this->isAdminCheck = true;
	}


	public function setCSRFCheck($csrfCheck){
		$this->csrfCheck = $csrfCheck;
	}

	public function setLoggedInCheck($loggedInCheck){
		$this->loggedInCheck = $loggedInCheck;
	}

	public function setAppEnabledCheck($appEnabledCheck){
		$this->appEnabledCheck = $appEnabledCheck;
	}

	public function setIsAdminCheck($isAdminCheck){
		$this->isAdminCheck = $isAdminCheck;
	}


	/**
	 * Runs all security checks
	 */
	public function runChecks() {

		if($this->loggedInCheck){
			\OCP\JSON::checkLoggedIn();
		}

		if($this->appEnabledCheck){
			\OCP\JSON::checkAppEnabled($this->appName);
		}

		if($this->isAdminCheck){
			\OCP\JSON::checkAdminUser();
		}

	}


	/**
	 * Runs all the security checks for AJAX requests
	 */
	public function runAjaxChecks(){
		if($this->csrfCheck){
			\OCP\JSON::callCheck();
		}

		if($this->loggedInCheck){
			\OCP\JSON::checkLoggedIn();
		}

		if($this->appEnabledCheck){
			\OCP\JSON::checkAppEnabled($this->appName);
		}

		if($this->isAdminCheck){
			\OCP\JSON::checkAdminUser();
		}

	}


}