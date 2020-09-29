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

use FeedIo\Explorer;
use FeedIo\FeedIo;
use HTMLPurifier;
use HTMLPurifier_Config;
use Favicon\Favicon;

use OCA\News\Config\LegacyConfig;
use OCA\News\Config\FetcherConfig;
use OCA\News\Hooks\UserDeleteHook;

use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\ITempManager;
use OCP\AppFramework\App;
use OCP\Files\IRootFolder;
use OCP\Files\Node;


use OCA\News\Db\MapperFactory;
use OCA\News\Db\ItemMapper;
use OCA\News\Fetcher\FeedFetcher;
use OCA\News\Fetcher\Fetcher;
use OCA\News\Fetcher\YoutubeFetcher;
use OCP\User\Events\BeforeUserDeletedEvent;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Application
 *
 * @package OCA\News\AppInfo
 */
class Application extends App implements IBootstrap
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

    public function __construct(array $urlParams = [])
    {
        parent::__construct(self::NAME, $urlParams);
    }

    public function register(IRegistrationContext $context): void
    {
        @include_once __DIR__ . '/../../vendor/autoload.php';

        $context->registerService(Fetcher::class, function (ContainerInterface $container): Fetcher {
            $fetcher = new Fetcher();

            // register fetchers in order, the most generic fetcher should be
            // the last one
            $fetcher->registerFetcher($container->get(YoutubeFetcher::class));
            $fetcher->registerFetcher($container->get(FeedFetcher::class));
            return $fetcher;
        });

        $context->registerEventListener(BeforeUserDeletedEvent::class, UserDeleteHook::class);

        // parameters
        $context->registerParameter('exploreDir', __DIR__ . '/../Explore/feeds');
        $context->registerParameter('configFile', 'config.ini');

        // factories
        $context->registerService(ItemMapper::class, function (ContainerInterface $c): ItemMapper {
            return $c->get(MapperFactory::class)->build();
        });

        $context->registerService(HTMLPurifier::class, function (ContainerInterface $c): HTMLPurifier {
            $directory = $c->get(ITempManager::class)->getTempBaseDir() . '/news/cache/purifier';

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

        $context->registerService(FeedIo::class, function (ContainerInterface $c): FeedIo {
            $config = $c->get(FetcherConfig::class);
            return new FeedIo($config->getClient(), $c->get(LoggerInterface::class));
        });

        $context->registerService(Explorer::class, function (ContainerInterface $c): Explorer {
            $config = $c->get(FetcherConfig::class);
            return new Explorer($config->getClient(), $c->get(LoggerInterface::class));
        });

        $context->registerService(Favicon::class, function (ContainerInterface $c): Favicon {
            $favicon = new Favicon();
            $favicon->cache(['dir' => $c->get(ITempManager::class)->getTempBaseDir()]);
            return $favicon;
        });

        //TODO: Remove code after 15.1
        $context->registerService('ConfigView', function (ContainerInterface $c): ?Node {
            /** @var IRootFolder $fs */
            $fs = $c->get(IRootFolder::class);
            $path = 'news/config';
            if ($fs->nodeExists($path)) {
                return $fs->get($path);
            } else {
                return null;
            }
        });

        //TODO: Remove code after 15.1
        $context->registerService(LegacyConfig::class, function (ContainerInterface $c): LegacyConfig {
            $config = new LegacyConfig(
                $c->get('ConfigView'),
                $c->get(LoggerInterface::class)
            );
            $config->read($c->get('configFile'), false);
            return $config;
        });
    }

    public function boot(IBootContext $context): void
    {
        //NO-OP
    }
}
