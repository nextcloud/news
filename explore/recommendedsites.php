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

namespace OCA\News\Explore;

class RecommendedSites {

    private $directory;

    /**
     * @param string $exploreDir the absolute path to where the recommendation
     * config files lie without a trailing slash
     */
    public function __construct($exploreDir) {
        $this->directory = $exploreDir;
    }


    public function forLanguage($languageCode, $default='en') {
        $file = $this->directory . '/sites.' . $languageCode . '.json';

        if (!file_exists($file)) {
            $file = $this->directory . '/sites.' . $default . '.json';
        }

        if (file_exists($file)) {
            return json_decode(file_get_contents($file), true);
        } else {
            return [];
        }

    }


}
