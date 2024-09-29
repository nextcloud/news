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

use OCA\News\Config\FetcherConfig;
use OCA\News\Hooks\UserDeleteHook;
use OCA\News\Search\FeedSearchProvider;
use OCA\News\Search\FolderSearchProvider;
use OCA\News\Search\ItemSearchProvider;
use OCA\News\Listeners\AddMissingIndicesListener;
use OCA\News\Utility\Cache;

use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\App;

use OCA\News\Fetcher\FaviconDataAccess;
use OCA\News\Fetcher\FeedFetcher;
use OCA\News\Fetcher\Fetcher;
use OCP\User\Events\BeforeUserDeletedEvent;
use OCP\DB\Events\AddMissingIndicesEvent;
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
        'autoPurgeCount'           => 200,
        'purgeUnread'              => false,
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
            $fetcher->registerFetcher($container->get(FeedFetcher::class));
            return $fetcher;
        });

        $context->registerSearchProvider(FolderSearchProvider::class);
        $context->registerSearchProvider(FeedSearchProvider::class);
        $context->registerSearchProvider(ItemSearchProvider::class);


        $context->registerEventListener(BeforeUserDeletedEvent::class, UserDeleteHook::class);
        $context->registerEventListener(AddMissingIndicesEvent::class, AddMissingIndicesListener::class);

        // parameters
        $context->registerParameter('exploreDir', __DIR__ . '/../Explore/feeds');

        $context->registerService(HTMLPurifier::class, function (ContainerInterface $c): HTMLPurifier {
            $config = HTMLPurifier_Config::createDefault();
            $config->set('HTML.ForbiddenAttributes', 'class');
            $config->set('Cache.SerializerPath', $c->get(Cache::class)->getCache("purifier"));
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

        $context->registerService(FaviconDataAccess::class, function (ContainerInterface $c): FaviconDataAccess {
            $config = $c->get(FetcherConfig::class);
            return new FaviconDataAccess($config);
        });

        $context->registerService(Favicon::class, function (ContainerInterface $c): Favicon {
            $favicon = new Favicon();
            $favicon->cache(['dir' => $c->get(Cache::class)->getCache("feedFavicon")]);
            $favicon->setDataAccess($c->get(FaviconDataAccess::class));
            return $favicon;
        });
    }

    public function boot(IBootContext $context): void
    {
        //NO-OP
    }
}
