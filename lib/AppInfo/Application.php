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

namespace OCA\News\AppInfo;

use HTMLPurifier;
use HTMLPurifier_Config;
use OCA\News\Config\Config;
use OCA\News\Db\ItemMapper;
use OCA\News\Db\MapperFactory;
use OCA\News\Fetcher\FeedFetcher;
use OCA\News\Fetcher\Fetcher;
use OCA\News\Fetcher\YoutubeFetcher;
use OCA\News\Utility\ProxyConfigParser;
use OCP\AppFramework\App;
use OCP\Files\IRootFolder;
use OCP\Files\Node;
use OCP\IConfig;
use OCP\IContainer;
use OCP\ILogger;
use PicoFeed\Config\Config as PicoFeedConfig;
use PicoFeed\Reader\Reader as PicoFeedReader;


class Application extends App
{

    public function __construct(array $urlParams = [])
    {
        parent::__construct('news', $urlParams);

        $container = $this->getContainer();

        // files
        $container->registerService('checksums', function () {
            return file_get_contents(__DIR__ . '/checksum.json');
        });
        $container->registerService('info', function () {
            return file_get_contents(__DIR__ . '/../../appinfo/info.xml');
        });

        // parameters
        $container->registerParameter('exploreDir', __DIR__ . '/../Explore/feeds');
        $container->registerParameter('configFile', 'config.ini');

        // factories
        $container->registerService(ItemMapper::class, function (IContainer $c) {
            return $c->query(MapperFactory::class)->build();
        });

        /**
         * Core
         */
        $container->registerService('LoggerParameters', function (IContainer $c): array {
            return ['app' => $c->query('AppName')];
        });

        $container->registerService('databaseType', function (IContainer $c) {
            return $c->query(IConfig::class)->getSystemValue('dbtype');
        });

        $container->registerService('ConfigView', function (IContainer $c): Node {
            /** @var IRootFolder $fs */
            $fs = $c->query(IRootFolder::class);
            $path = 'news/config';
            if ($fs->nodeExists($path)) {
                return $fs->get($path);
            } else {
                return $fs->newFolder($path);
            }
        });


        $container->registerService(Config::class, function (IContainer $c): Config {
            $config = new Config(
                $c->query('ConfigView'),
                $c->query(ILogger::class),
                $c->query('LoggerParameters')
            );
            $config->read($c->query('configFile'), true);
            return $config;
        });

        $container->registerService(HTMLPurifier::class, function (IContainer $c): HTMLPurifier {
            $directory = $c->query(IConfig::class)->getSystemValue('datadirectory') . '/news/cache/purifier';

            if (!is_dir($directory)) {
                mkdir($directory, 0770, true);
            }

            $config = HTMLPurifier_Config::createDefault();
            $config->set('HTML.ForbiddenAttributes', 'class');
            $config->set('Cache.SerializerPath', $directory);
            $config->set('HTML.SafeIframe', true);
            $config->set(
                'URI.SafeIframeRegexp',
                '%^https://(?:www\.)?(' .
                'youtube(?:-nocookie)?.com/embed/|' .
                'player.vimeo.com/video/|' .
                'vk.com/video_ext.php)%'
            ); //allow YouTube and Vimeo
            $def = $config->getHTMLDefinition(true);
            $def->addAttribute('iframe', 'allowfullscreen', 'Bool');
            return new HTMLPurifier($config);
        });

        /**
         * Fetchers
         */
        $container->registerService(PicoFeedConfig::class, function (IContainer $c): PicoFeedConfig {
            // FIXME: move this into a separate class for testing?
            $config = $c->query(Config::class);
            $proxy = $c->query(ProxyConfigParser::class);

            $userAgent = 'NextCloud-News/1.0';

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

        $container->registerService(PicoFeedReader::class, function (IContainer $c): PicoFeedReader {
            return new PicoFeedReader($c->query(PicoFeedConfig::class));
        });

        $container->registerService(Fetcher::class, function (IContainer $c): Fetcher {
            $fetcher = new Fetcher();

            // register fetchers in order, the most generic fetcher should be
            // the last one
            $fetcher->registerFetcher($c->query(YoutubeFetcher::class));
            $fetcher->registerFetcher($c->query(FeedFetcher::class));

            return $fetcher;
        });


    }

}
