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

use \PicoFeed\Config;
use \PicoFeed\Favicon;

class PicoFeedFaviconFactory {

    private $config;

    public function __construct(Config $config) {
        $this->config = $config;
    }


    /**
     * Returns a new instance of an PicoFeed Http client
     * @return \PicoFeed\Favicon instance
     */
    public function build() {
        return new Favicon($this->config);
    }


}