<?php
/**
 * Nextcloud - News
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

use \OCP\IConfig;


class ProxyConfigParser {

    private $config;

    public function __construct(IConfig $config) {
        $this->config = $config;
    }


    /**
     * Parses the config and splits up the port + url
     * @return array
     */
    public function parse() {
        $proxy = $this->config->getSystemValue('proxy');
        $userpasswd = $this->config->getSystemValue('proxyuserpwd');

        $result = [
            'host' => null,
            'port' => null,
            'user' => null,
            'password' => null
        ];

        // we need to filter out the port -.-
        $url = new \Net_URL2($proxy);
        $port = $url->getPort();

        $url->setPort(false);
        $host = $url->getUrl();


        $result['host'] = $host;
        $result['port'] = $port;

        if ($userpasswd) {
            $auth = explode(':', $userpasswd, 2);
            $result['user'] = $auth[0];
            $result['password'] = $auth[1];
        }

        return $result;
    }


}