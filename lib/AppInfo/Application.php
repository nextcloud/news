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

use FeedIo\FeedIo;
use HTMLPurifier;
use HTMLPurifier_Config;
use Favicon\Favicon;

use OC\Encryption\Update;
use OCA\News\Config\LegacyConfig;
use OCA\News\Config\FetcherConfig;
use OCA\News\Db\FolderMapper;
use OCA\News\Service\FeedService;
use OCA\News\Service\FolderService;
use OCA\News\Service\ItemService;
use OCA\News\Utility\PsrLogger;

use OCA\News\Utility\Updater;
use OCP\IContainer;
use OCP\IConfig;
use OCP\ILogger;
use OCP\ITempManager;
use OCP\AppFramework\App;
use OCP\Files\IRootFolder;
use OCP\Files\Node;


use OCA\News\Db\MapperFactory;
use OCA\News\Db\ItemMapper;
use OCA\News\Fetcher\FeedFetcher;
use OCA\News\Fetcher\Fetcher;
use OCA\News\Fetcher\YoutubeFetcher;
use OCA\News\Scraper\Scraper;
use Psr\Log\LoggerInterface;

/**
 * Class Application
 *
 * @package OCA\News\AppInfo
 */
class Application extends App
{

    /**
     * App Name
     */
    public const NAME = 'news';

    /**
     * List of default settings
     */
    public const DEFAULT_SETTINGS = [
        'autoPurgeMinimumInterval' => 60,
        'autoPurgeCount'           => 200,
        'maxRedirects'             => 10,
        'feedFetcherTimeout'       => 60,
        'useCronUpdates'           => true,
        'exploreUrl'               => '',
        'updateInterval'           => 3600,
    ];

    /**
     * Application constructor.
     *
     * @param array $urlParams Parameters
     */
    public function __construct(array $urlParams = [])
    {
        parent::__construct(self::NAME, $urlParams);

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
        $container->registerService(ItemMapper::class, function (IContainer $c): ItemMapper {
            return $c->query(MapperFactory::class)->build();
        });

        /**
         * Core
         */
        $container->registerService('LoggerParameters', function (IContainer $c): array {
            return ['app' => $c->query('AppName')];
        });

        $container->registerService('ConfigView', function (IContainer $c): ?Node {
            /** @var IRootFolder $fs */
            $fs = $c->query(IRootFolder::class);
            $path = 'news/config';
            if ($fs->nodeExists($path)) {
                return $fs->get($path);
            } else {
                return null;
            }
        });

        $container->registerService(LegacyConfig::class, function (IContainer $c): LegacyConfig {
            $config = new LegacyConfig(
                $c->query('ConfigView'),
                $c->query(LoggerInterface::class),
                $c->query('LoggerParameters')
            );
            $config->read($c->query('configFile'), false);
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

            // Additionally to the defaults, allow the data URI scheme.
            // See http://htmlpurifier.org/live/configdoc/plain.html#URI.AllowedSchemes
            $config->set('URI.AllowedSchemes', [
                'http' => true,
                'https' => true,
                'data' => true,
                'mailto' => true,
                'ftp' => true,
                'nntp' => true,
                'news' => true,
                'tel' => true,
            ]);

            $def = $config->getHTMLDefinition(true);
            $def->addAttribute('iframe', 'allowfullscreen', 'Bool');
            return new HTMLPurifier($config);
        });

        /**
         * Fetchers
         */
        $container->registerService(FetcherConfig::class, function (IContainer $c): FetcherConfig {
            $fConfig = new FetcherConfig();
            $fConfig->setConfig($c->query(IConfig::class))
                    ->setProxy($c->query(IConfig::class));

            return $fConfig;
        });

        $container->registerService(FeedIo::class, function (IContainer $c): FeedIo {
            $config = $c->query(FetcherConfig::class);
            return new FeedIo($config->getClient(), $c->query(LoggerInterface::class));
        });

        $container->registerService(Favicon::class, function (IContainer $c): Favicon {
            $favicon = new Favicon();
            $tempManager = $c->query(ITempManager::class);
            $settings = ['dir' => $tempManager->getTempBaseDir()];
            $favicon->cache($settings);
            return $favicon;
        });

        $container->registerService(Fetcher::class, function (IContainer $c): Fetcher {
            $fetcher = new Fetcher();

            // register fetchers in order, the most generic fetcher should be
            // the last one
            $fetcher->registerFetcher($c->query(YoutubeFetcher::class));
            $fetcher->registerFetcher($c->query(FeedFetcher::class));
            return $fetcher;
        });

        /**
         * Scrapers
         */
        $container->registerService(Scraper::class, function (IContainer $c): Scraper {
            return new Scraper(
                $c->query(LoggerInterface::class)
            );
        });
    }
}
