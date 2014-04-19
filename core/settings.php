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

namespace OCA\News\Core;

class Settings {

    protected $appName;
    protected $userId;

    public function __construct($appName, $userId) {
        $this->appName = $appName;
        $this->userId = $userId;
    }


    /**
     * Looks up a systemwide defined value
     * @param string $key the key of the value, under which it was saved
     * @return string the saved value
     */
    public function getSystemValue($key){
        return \OCP\Config::getSystemValue($key, '');
    }


    /**
     * Sets a new systemwide value
     * @param string $key the key of the value, under which will be saved
     * @param string $value the value that should be stored
     */
    public function setSystemValue($key, $value){
        return \OCP\Config::setSystemValue($key, $value);
    }


    /**
     * Looks up an appwide defined value
     * @param string $key the key of the value, under which it was saved
     * @return string the saved value
     */
    public function getAppValue($key, $appName=null){
        if($appName === null){
            $appName = $this->appName;
        }
        return \OCP\Config::getAppValue($appName, $key, '');
    }


    /**
     * Writes a new appwide value
     * @param string $key the key of the value, under which will be saved
     * @param string $value the value that should be stored
     */
    public function setAppValue($key, $value, $appName=null){
        if($appName === null){
            $appName = $this->appName;
        }
        return \OCP\Config::setAppValue($appName, $key, $value);
    }



    /**
     * Shortcut for setting a user defined value
     * @param string $key the key under which the value is being stored
     * @param string $value the value that you want to store
     * @param string $userId the userId of the user that we want to store the value under, defaults to the current one
     */
    public function setUserValue($key, $value, $userId=null){
        if($userId === null){
            $userId = $this->userId;
        }
        \OCP\Config::setUserValue($userId, $this->appName, $key, $value);
    }


    /**
     * Shortcut for getting a user defined value
     * @param string $key the key under which the value is being stored
     * @param string $userId the userId of the user that we want to store the value under, defaults to the current one
     */
    public function getUserValue($key, $userId=null){
        if($userId === null){
            $userId = $this->userId;
        }
        return \OCP\Config::getUserValue($userId, $this->appName, $key);
    }


}