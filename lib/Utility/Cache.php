<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Benjamin Brahmer <info@b-brahmer.de>
 * @copyright 2023 Benjamin Brahmer
 */
namespace OCA\News\Utility;

use OCP\ITempManager;
use OCP\IConfig;

class Cache
{


    /**
     * @var ITempManager
     */
    private $ITempManager;

    /**
     * @var IConfig
     */
    private $IConfig;


    public function __construct(
        ITempManager $ITempManager,
        IConfig $IConfig
    ) {
        $this->ITempManager   = $ITempManager;
        $this->IConfig        = $IConfig;
    }

    /**
     * Get a news app cache directory
     *
     * @param String $name for the sub-directory, is created if not existing
     *
     * @return String $directory The path for the cache
     */
    public function getCache(String $name): String
    {
        $baseDir = $this->ITempManager->getTempBaseDir();
        $instanceID = $this->IConfig->getSystemValue('instanceid');

        $directory = join(DIRECTORY_SEPARATOR, [$baseDir, "news-" . $instanceID, 'cache', $name]);

        if (!is_dir($directory)) {
            mkdir($directory, 0770, true);
        }

        return $directory;
    }
}
