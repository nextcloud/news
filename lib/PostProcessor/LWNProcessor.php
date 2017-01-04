<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Robin Appelman <robin@icewind.nl>
 */

namespace OCA\News\PostProcessor;

use GuzzleHttp\Cookie\CookieJar;
use OCP\Http\Client\IClientService;
use PicoFeed\Parser\Feed;
use PicoFeed\Parser\Item;
use PicoFeed\Processor\ItemProcessorInterface;
use PicoFeed\Scraper\RuleParser;

class LWNProcessor implements ItemProcessorInterface {
    private $user;

    private $password;

    private $clientService;

    private $cookieJar;

    /**
     * @param $user
     * @param $password
     */
    public function __construct($user, $password, IClientService $clientService) {
        $this->user = $user;
        $this->password = $password;
        $this->clientService = $clientService;
        $this->cookieJar = new CookieJar();
    }

    private function login() {
        if ($this->cookieJar->count() > 0) {
            return true;
        }
        if (!$this->user || !$this->password) {
            return false;
        }

        $client = $this->clientService->newClient();
        $response = $client->post('https://lwn.net/login', [
            'cookies' => $this->cookieJar,
            'body' => [
                'Username' => $this->user,
                'Password' => $this->password,
                'target' => '/'
            ]
        ]);
        return ($response->getStatusCode() === 200 && $this->cookieJar->count() > 0);
    }

    private function getBody($url) {
        $client = $this->clientService->newClient();
        $response = $client->get($url, [
            'cookies' => $this->cookieJar
        ]);
        $parser = new RuleParser($response->getBody(), [
            'body' => array(
                '//div[@class="ArticleText"]',
            ),
            'strip' => array(
                '//div[@class="FeatureByline"]'
            )
        ]);
        $articleBody = $parser->execute();
        // make all links absolute
        return str_replace('href="/', 'href="https://lwn.net/', $articleBody);
    }

    private function canHandle($url) {
        $regex = '%(?:https?://|//)?(?:www.)?lwn.net%';

        return (bool)preg_match($regex, $url);
    }

    /**
     * Execute Item Processor
     *
     * @access public
     * @param  Feed $feed
     * @param  Item $item
     * @return bool
     */
    public function execute(Feed $feed, Item $item) {
        if ($this->canHandle($item->getUrl())) {
            $loggedIn = $this->login();

            $item->setUrl(str_replace('/rss', '', $item->getUrl()));
            if ($loggedIn) {
                $item->setContent($this->getBody($item->getUrl()));
            }
        }
    }
}
