<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Alessandro Cosentino <cosenal@gmail.com>
 * @author    Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright 2012 Alessandro Cosentino
 * @copyright 2012-2014 Bernhard Posselt
 */


namespace OCA\News\Utility;

use \PicoFeed\Config\Config;
use \PicoFeed\Reader\Favicon;

class PicoFeedFaviconFactory
{

    private $config;

    public function __construct(Config $config) 
    {
        $this->config = $config;
    }


    /**
     * Returns a new instance of an PicoFeed Http client
     *
     * @return \PicoFeed\Favicon instance
     */
    public function build() 
    {
        return new Favicon($this->config);
    }


}