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

namespace OCA\News\Config;

use OCA\News\AppInfo\Application;
use OCA\News\Utility\PsrLogger;
use OCP\Files\Folder;
use OCP\IConfig;
use Psr\Log\LoggerInterface;

class LegacyConfig
{

    private $logger;
    private $fileSystem;

    public $autoPurgeMinimumInterval;  // seconds, used to define how
                                        // long deleted folders and feeds
                                        // should still be kept for an
                                        // undo actions
    public $autoPurgeCount;  // number of allowed unread articles per feed
    public $maxRedirects;  // seconds
    public $feedFetcherTimeout;  // seconds
    public $useCronUpdates;  // turn off updates run by the cron
    public $maxSize;
    public $exploreUrl;
    public $updateInterval;

    public function __construct(
        ?Folder $fileSystem,
        LoggerInterface $logger
    ) {
        $this->fileSystem = $fileSystem;
        $this->logger = $logger;

        $this->autoPurgeMinimumInterval = 60;
        $this->autoPurgeCount = 200;
        $this->maxRedirects = 10;
        $this->maxSize = 100 * 1024 * 1024; // 100Mb
        $this->feedFetcherTimeout = 60;
        $this->useCronUpdates = true;
        $this->exploreUrl = '';
        $this->updateInterval = 3600;
    }

    /**
     * @param false $createIfNotExists
     *
     * @return void
     */
    public function read($configPath, bool $createIfNotExists = false)
    {
        if ($this->fileSystem === null) {
            return;
        }
        $content = $this->fileSystem->get($configPath)->getContent();
        $configValues = parse_ini_string($content);

        if ($configValues === false || count($configValues) === 0) {
            $this->logger->warning('Configuration invalid. Ignoring values.');
        } else {
            foreach ($configValues as $key => $value) {
                if (property_exists($this, $key)) {
                    $type = gettype($this->$key);
                    settype($value, $type);
                    $this->$key = $value;
                } else {
                    $this->logger->warning(
                        'Configuration value "' . $key .
                        '" does not exist. Ignored value.'
                    );
                }
            }
        }
    }
}
