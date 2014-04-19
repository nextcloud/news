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

namespace OCA\News\Core;

class Logger {

    protected $appName;

    public function __construct($appName) {
        $this->appName = $appName;
    }


    /**
     * Writes a function into the error log
     * @param string $msg the error message to be logged
     * @param int $level the error level
     */
    public function log($msg, $level=null){
        switch($level){
            case 'debug':
                $level = \OCP\Util::DEBUG;
                break;
            case 'info':
                $level = \OCP\Util::INFO;
                break;
            case 'warn':
                $level = \OCP\Util::WARN;
                break;
            case 'fatal':
                $level = \OCP\Util::FATAL;
                break;
            default:
                $level = \OCP\Util::ERROR;
                break;
        }
        \OCP\Util::writeLog($this->appName, $msg, $level);
    }


}
