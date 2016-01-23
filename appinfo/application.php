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

namespace OCA\News\AppInfo;

use HTMLPurifier;
use HTMLPurifier_Config;

use PicoFeed\Config\Config as PicoFeedConfig;
use PicoFeed\Reader\Reader as PicoFeedReader;

use OCP\ILogger;
use OCP\INavigationManager;
use OCP\IURLGenerator;
use OCP\IConfig;
use OCP\IDBConnection;
use OCP\AppFramework\App;
use OCP\Files\IRootFolder;

use OCA\News\Config\AppConfig;
use OCA\News\Config\Config;
use OCA\News\Service\FeedService;
use OCA\News\Db\MapperFactory;
use OCA\News\Db\ItemMapper;
use OCA\News\Fetcher\Fetcher;
use OCA\News\Fetcher\FeedFetcher;
use OCA\News\Fetcher\YoutubeFetcher;
use OCA\News\Explore\RecommendedSites;
use OCA\News\Utility\ProxyConfigParser;


class Application extends App {

    public function __construct(array $urlParams=[]) {
        parent::__construct('news', $urlParams);

        // files
        $this->registerFileContents('checksums', 'checksum.json');
        $this->registerFileContents('info', 'info.xml');

        // parameters
        $this->registerParameter('exploreDir', __DIR__ . '/../explore/feeds');
        $this->registerParameter('configFile', 'config.ini');

        // factories
        $this->registerFactory(ItemMapper::class, MapperFactory::class);


        /**
         * App config parser
         */
        $this->registerService(AppConfig::class, function($c) {
            $config = new AppConfig(
                $c->query(INavigationManager::class),
                $c->query(IURLGenerator::class)
            );

            $config->loadConfig($c->query('info'));

            return $config;
        });

        /**
         * Core
         */
        $this->registerService('LoggerParameters', function($c) {
            return ['app' => $c->query('AppName')];
        });

        $this->registerService('databaseType', function($c) {
            return $c->query(IConfig::class)->getSystemValue('dbtype');
        });

        $this->registerService('ConfigView', function($c) {
            $fs = $c->query(IRootFolder::class);
            $path = 'news/config';
            if ($fs->nodeExists($path)) {
                return $fs->get($path);
            } else {
                return $fs->newFolder($path);
            }
        });


        $this->registerService(Config::class, function($c) {
            $config = new Config(
                $c->query('ConfigView'),
                $c->query(ILogger::class),
                $c->query('LoggerParameters')
            );
            $config->read($c->query('configFile'), true);
            return $config;
        });

        $this->registerService(HTMLPurifier::class, function($c) {
            $directory = $c->query(IConfig::class)
                ->getSystemValue('datadirectory') . '/news/cache/purifier';

            if(!is_dir($directory)) {
                mkdir($directory, 0770, true);
            }

            $config = HTMLPurifier_Config::createDefault();
            $config->set('HTML.ForbiddenAttributes', 'class');
            $config->set('Cache.SerializerPath', $directory);
            $config->set('HTML.SafeIframe', true);
            $config->set('URI.SafeIframeRegexp',
                '%^https://(?:www\.)?(' .
                'youtube(?:-nocookie)?.com/embed/|' .
                'player.vimeo.com/video/)%'); //allow YouTube and Vimeo
            $def = $config->getHTMLDefinition(true);
            $def->addAttribute('iframe', 'allowfullscreen', 'Bool');
            return new HTMLPurifier($config);
        });

        /**
         * Fetchers
         */
        $this->registerService(PicoFeedConfig::class, function($c) {
            // FIXME: move this into a separate class for testing?
            $config = $c->query(Config::class);
            $appConfig = $c->query(AppConfig::class);
            $proxy =  $c->query(ProxyConfigParser::class);

            $userAgent = 'ownCloud News/' . $appConfig->getConfig('version') .
                         ' (+https://owncloud.org/; 1 subscriber;)';

            $pico = new PicoFeedConfig();
            $pico->setClientUserAgent($userAgent)
                ->setClientTimeout($config->getFeedFetcherTimeout())
                ->setMaxRedirections($config->getMaxRedirects())
                ->setMaxBodySize($config->getMaxSize())
                ->setParserHashAlgo('md5');

            // proxy settings
            $proxySettings = $proxy->parse();
            $host = $proxySettings['host'];
            $port = $proxySettings['port'];
            $user = $proxySettings['user'];
            $password = $proxySettings['password'];

            if ($host) {
                $pico->setProxyHostname($host);

                if ($port) {
                    $pico->setProxyPort($port);
                }
            }

            if ($user) {
                $pico->setProxyUsername($user)
                    ->setProxyPassword($password);
            }

            return $pico;
        });

        $this->registerService(Fetcher::class, function($c) {
            $fetcher = new Fetcher();

            // register fetchers in order, the most generic fetcher should be
            // the last one
            $fetcher->registerFetcher($c->query(YoutubeFetcher::class));
            $fetcher->registerFetcher($c->query(FeedFetcher::class));

            return $fetcher;
        });


    }

    /**
     * Registers the content of a file under a key
     * @param string $key
     * @param string $file path relative to this file, __DIR__ will be prepended
     */
    private function registerFileContents($key, $file) {
        $this->registerService($key, function () use ($file) {
            return file_get_contents(__DIR__ . '/' . $file);
        });
    }

    /**
     * Shortcut for registering a service
     * @param string $key
     * @param closure $factory
     * @param boolean $shared
     */
    private function registerService($key, $factory, $shared=true) {
        $this->getContainer()->registerService($key, $factory, $shared);
    }

    /**
     * Shortcut for registering a parameter
     * @param string $key
     * @param mixed $value
     */
    private function registerParameter($key, $value) {
        $this->getContainer()->registerParameter($key, $value);
    }

    /**
     * Register a class containing the app construction logic instead of the
     * inlining everything in this class to enhance testability
     * @param string $key fully qualified class name
     * @param string $factory fully qualified factory class name
     */
    private function registerFactory($key, $factory) {
        $this->registerService($key, function ($c) use ($factory) {
            return $c->query($factory)->build();
        });
    }

    /**
     * Register the additional config parameters found in the info.xml
     */
    public function registerConfig() {
        $this->getContainer()->query(AppConfig::class)->registerAll();
    }

}
