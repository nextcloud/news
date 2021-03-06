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

namespace OCA\News\Explore;

use OCA\News\Explore\Exceptions\RecommendedSiteNotFoundException;

class RecommendedSites
{

    private $directory;

    /**
     * @param string $exploreDir the absolute path to where the recommendation
     *                           config files lie without a trailing slash
     */
    public function __construct(string $exploreDir)
    {
        $this->directory = $exploreDir;
    }


    /**
     * @param string $languageCode
     *
     * @return array
     *
     * @throws RecommendedSiteNotFoundException
     */
    public function forLanguage(string $languageCode): array
    {
        $file = $this->directory . '/feeds.' . $languageCode . '.json';

        if (file_exists($file)) {
            return json_decode(file_get_contents($file), true);
        } else {
            $msg = 'No recommended sites found for language code ' .
                $languageCode;
            throw new RecommendedSiteNotFoundException($msg);
        }
    }
}
