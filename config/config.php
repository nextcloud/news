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

namespace OCA\News\Config;

use OCP\ILogger;
use OCP\Files\Folder;


class Config {

    private $fileSystem;
    private $autoPurgeMinimumInterval;  // seconds, used to define how
                                        // long deleted folders and feeds
                                        // should still be kept for an
                                        // undo actions
    private $autoPurgeCount;  // number of allowed unread articles per feed
    private $maxRedirects;  // seconds
    private $feedFetcherTimeout;  // seconds
    private $useCronUpdates;  // turn off updates run by owncloud cronjob
    private $logger;
    private $loggerParams;
    private $maxSize;
    private $exploreUrl;
    private $proxyHost;
    private $proxyPort;
    private $proxyUser;
    private $proxyPassword;


    public function __construct(Folder $fileSystem,
                                ILogger $logger,
                                $LoggerParameters) {
        $this->fileSystem = $fileSystem;
        $this->autoPurgeMinimumInterval = 60;
        $this->autoPurgeCount = 200;
        $this->maxRedirects = 10;
        $this->maxSize = 100*1024*1024; // 100Mb
        $this->feedFetcherTimeout = 60;
        $this->useCronUpdates = true;
        $this->logger = $logger;
        $this->proxyHost = '';
        $this->proxyPort = 8080;
        $this->proxyUser = '';
        $this->proxyPassword = '';
        $this->exploreUrl = '';
        $this->loggerParams = $LoggerParameters;
    }

    public function getProxyPort() {
        return $this->proxyPort;
    }

    public function getProxyHost() {
        return $this->proxyHost;
    }

    public function getProxyAuth() {
        if($this->proxyUser === '') {
            return null;
        } else {
            return $this->proxyUser . ':' . $this->proxyPassword;
        }
    }

    public function getProxyUser() {
        return $this->proxyUser;
    }

    public function getProxyPassword() {
        return $this->proxyPassword;
    }

    public function getAutoPurgeMinimumInterval() {
        if ($this->autoPurgeMinimumInterval > 60) {
            return $this->autoPurgeMinimumInterval;
        } else {
            return 60;
        }
    }


    public function getAutoPurgeCount() {
        return $this->autoPurgeCount;
    }


    public function getMaxRedirects() {
        return $this->maxRedirects;
    }


    public function getFeedFetcherTimeout() {
        return $this->feedFetcherTimeout;
    }


    public function getUseCronUpdates() {
        return $this->useCronUpdates;
    }


    public function getMaxSize() {
        return $this->maxSize;
    }


    public function getExploreUrl() {
        return $this->exploreUrl;
    }


    public function setAutoPurgeMinimumInterval($value) {
        $this->autoPurgeMinimumInterval = $value;
    }


    public function setAutoPurgeCount($value) {
        $this->autoPurgeCount = $value;
    }


    public function setMaxRedirects($value) {
        $this->maxRedirects = $value;
    }


    public function setFeedFetcherTimeout($value) {
        $this->feedFetcherTimeout = $value;
    }


    public function setUseCronUpdates($value) {
        $this->useCronUpdates = $value;
    }


    public function setProxyPort($value) {
        $this->proxyPort = $value;
    }


    public function setProxyHost($value) {
        $this->proxyHost = $value;
    }


    public function setProxyUser($value) {
        $this->proxyUser = $value;
    }


    public function setProxyPassword($value) {
        $this->proxyPassword = $value;
    }


    public function setMaxSize($value) {
        $this->maxSize = $value;
    }


    public function setExploreUrl($value) {
        $this->exploreUrl = $value;
    }


    public function read($configPath, $createIfNotExists=false) {
        if($createIfNotExists && !$this->fileSystem->nodeExists($configPath)) {
            $this->fileSystem->newFile($configPath);
            $this->write($configPath);

        } else {

            $content = $this->fileSystem->get($configPath)->getContent();
            $configValues = parse_ini_string($content);

            if($configValues === false || count($configValues) === 0) {
                $this->logger->warning(
                    'Configuration invalid. Ignoring values.',
                    $this->loggerParams
                );
            } else {

                foreach($configValues as $key => $value) {
                    if(property_exists($this, $key)) {
                        $type = gettype($this->$key);
                        settype($value, $type);
                        $this->$key = $value;
                    } else {
                        $this->logger->warning(
                            'Configuration value "' . $key .
                            '" does not exist. Ignored value.' ,
                            $this->loggerParams
                        );
                    }
                }

            }
        }
    }


    public function write($configPath) {
        $ini =
            'autoPurgeMinimumInterval = ' .
                $this->autoPurgeMinimumInterval . "\n" .
            'autoPurgeCount = ' .
                $this->autoPurgeCount . "\n" .
            'maxRedirects = ' .
                $this->maxRedirects . "\n" .
            'maxSize = ' .
                $this->maxSize . "\n" .
            'exploreUrl = ' .
                $this->exploreUrl . "\n" .
            'feedFetcherTimeout = ' .
                $this->feedFetcherTimeout . "\n" .
            'useCronUpdates = ' .
                var_export($this->useCronUpdates, true);
        ;

        $this->fileSystem->get($configPath)->putContent($ini);
    }


}
