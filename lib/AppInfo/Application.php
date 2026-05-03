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

use OCA\News\Vendor\FeedIo\Explorer;
use OCA\News\Vendor\FeedIo\FeedIo;
use OCA\News\Vendor\FeedIo\FaviconIo\FaviconDiscovery;
use OCA\News\Vendor\GuzzleHttp\Psr7\HttpFactory;

use OCA\News\Config\FetcherConfig;
use OCA\News\Hooks\UserDeleteHook;
use OCA\News\Search\FeedSearchProvider;
use OCA\News\Search\FolderSearchProvider;
use OCA\News\Search\ItemSearchProvider;
use OCA\News\Listeners\AddMissingIndicesListener;
use OCA\News\Listeners\UserSettingsListener;
use OCA\News\SetupCheck\CronSetupCheck;
use OCA\News\Utility\Cache;
use OCA\News\Utility\HtmlSanitizer;
use OCA\News\Http\ScopedClient;
use OCA\News\Vendor\Symfony\Component\Cache\Adapter\FilesystemAdapter;
use OCA\News\Vendor\Symfony\Component\Cache\Psr16Cache;

use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\App;

use OCA\News\Fetcher\FeedFetcher;
use OCA\News\Fetcher\Fetcher;
use OCA\News\Notification\Notifier;
use OCP\User\Events\BeforeUserDeletedEvent;
use OCP\Config\BeforePreferenceDeletedEvent;
use OCP\Config\BeforePreferenceSetEvent;
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
        'useNextUpdateTime'        => false,
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

        $context->registerNotifierService(Notifier::class);


        $context->registerEventListener(BeforeUserDeletedEvent::class, UserDeleteHook::class);
        $context->registerEventListener(AddMissingIndicesEvent::class, AddMissingIndicesListener::class);
        $context->registerEventListener(BeforePreferenceDeletedEvent::class, UserSettingsListener::class);
        $context->registerEventListener(BeforePreferenceSetEvent::class, UserSettingsListener::class);

        $context->registerSetupCheck(CronSetupCheck::class);

        // parameters
        $context->registerParameter('exploreDir', __DIR__ . '/../Explore/feeds');

        $context->registerService(HtmlSanitizer::class, function (ContainerInterface $c): HtmlSanitizer {
            return new HtmlSanitizer(HtmlSanitizer::createSanitizer());
        });

        $context->registerService(FeedIo::class, function (ContainerInterface $c): FeedIo {
            $config = $c->get(FetcherConfig::class);
            return new FeedIo($config->getClient(), $c->get(LoggerInterface::class));
        });

        $context->registerService(Explorer::class, function (ContainerInterface $c): Explorer {
            $config = $c->get(FetcherConfig::class);
            return new Explorer($config->getClient(), $c->get(LoggerInterface::class));
        });

        $context->registerService(FaviconDiscovery::class, function (ContainerInterface $c): FaviconDiscovery {
            $config = $c->get(FetcherConfig::class);
            return new FaviconDiscovery(
                httpClient: new ScopedClient(
                    $config->getHttpClient(),
                    [
                        'timeout' => $config->getClientTimeout(),
                        'connect_timeout' => FetcherConfig::CONNECT_TIMEOUT,
                        'allow_redirects' => ['max' => $config->getMaxRedirects(), 'referer' => true],
                    ]
                ),
                requestFactory: new HttpFactory(),
                cache: new Psr16Cache(new FilesystemAdapter(directory: $c->get(Cache::class)->getCache('feedFavicon'))),
                logger: $c->get(LoggerInterface::class),
                userAgent: $config->getUserAgent(),
            );
        });
    }

    public function boot(IBootContext $context): void
    {
        //NO-OP
    }
}
