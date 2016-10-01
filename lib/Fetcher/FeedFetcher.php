<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Alessandro Cosentino <cosenal@gmail.com>
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Alessandro Cosentino 2012
 * @copyright Bernhard Posselt 2012, 2014
 */

namespace OCA\News\Fetcher;

use Exception;

use PicoFeed\Parser\MalFormedXmlException;
use PicoFeed\Reader\Reader;
use PicoFeed\Parser\Parser;
use PicoFeed\Reader\SubscriptionNotFoundException;
use PicoFeed\Reader\UnsupportedFeedFormatException;
use PicoFeed\Client\InvalidCertificateException;
use PicoFeed\Client\InvalidUrlException;
use PicoFeed\Client\MaxRedirectException;
use PicoFeed\Client\MaxSizeException;
use PicoFeed\Client\TimeoutException;
use PicoFeed\Client\ForbiddenException;
use PicoFeed\Client\UnauthorizedException;

use OCP\IL10N;

use OCA\News\Db\Item;
use OCA\News\Db\Feed;
use OCA\News\Utility\PicoFeedFaviconFactory;
use OCA\News\Utility\PicoFeedReaderFactory;
use OCA\News\Utility\Time;

class FeedFetcher implements IFeedFetcher {

    private $faviconFactory;
    private $reader;
    private $l10n;
    private $time;

    public function __construct(Reader $reader,
                                PicoFeedFaviconFactory $faviconFactory,
                                IL10N $l10n,
                                Time $time) {
        $this->faviconFactory = $faviconFactory;
        $this->reader = $reader;
        $this->time = $time;
        $this->l10n = $l10n;
    }


    /**
     * This fetcher handles all the remaining urls therefore always returns true
     */
    public function canHandle($url) {
        return true;
    }


    /**
     * Fetch a feed from remote
     * @param string $url remote url of the feed
     * @param boolean $getFavicon if the favicon should also be fetched,
     * defaults to true
     * @param string $lastModified a last modified value from an http header
     * defaults to false. If lastModified matches the http header from the feed
     * no results are fetched
     * @param string $etag an etag from an http header.
     * If lastModified matches the http header from the feed
     * no results are fetched
     * @param bool fullTextEnabled if true tells the fetcher to enhance the
     * articles by fetching custom enhanced content
     * @param string $basicAuthUser if given, basic auth is set for this feed
     * @param string $basicAuthPassword if given, basic auth is set for this
     * feed. Ignored if user is null or an empty string
     * @throws FetcherException if it fails
     * @return array an array containing the new feed and its items, first
     * element being the Feed and second element being an array of Items
     */
    public function fetch($url, $getFavicon = true, $lastModified = null,
                          $etag = null, $fullTextEnabled = false,
                          $basicAuthUser = null, $basicAuthPassword = null) {
        try {
            if ($basicAuthUser !== null && trim($basicAuthUser) !== '') {
                $resource = $this->reader->discover($url, $lastModified, $etag,
                    $basicAuthUser,
                    $basicAuthPassword);
            } else {
                $resource = $this->reader->discover($url, $lastModified, $etag);
            }

            if (!$resource->isModified()) {
                return [null, null];
            }

            $location = $resource->getUrl();
            $etag = $resource->getEtag();
            $content = $resource->getContent();
            $encoding = $resource->getEncoding();
            $lastModified = $resource->getLastModified();

            $parser = $this->reader->getParser($location, $content, $encoding);

            if ($fullTextEnabled) {
                $parser->enableContentGrabber();
            }

            $parsedFeed = $parser->execute();

            $feed = $this->buildFeed(
                $parsedFeed, $url, $getFavicon, $lastModified, $etag, $location
            );

            $items = [];
            foreach ($parsedFeed->getItems() as $item) {
                $items[] = $this->buildItem($item, $parsedFeed);
            }

            return [$feed, $items];

        } catch (Exception $ex) {
            $this->handleError($ex, $url);
        }

    }


    private function handleError(Exception $ex, $url) {
        $msg = $ex->getMessage();

        if ($ex instanceof MalFormedXmlException) {
            $msg = $this->l10n->t('Feed contains invalid XML');
        } else if ($ex instanceof SubscriptionNotFoundException) {
            $msg = $this->l10n->t('Feed not found: either the website ' .
                'does not provide a feed or blocks access. To rule out ' .
                'blocking, try to download the feed on your server\'s ' .
                'command line using curl: curl ' . $url);
        } else if ($ex instanceof UnsupportedFeedFormatException) {
            $msg = $this->l10n->t('Detected feed format is not supported');
        } else if ($ex instanceof InvalidCertificateException) {
            $msg = $this->buildCurlSslErrorMessage($ex->getCode());
        } else if ($ex instanceof InvalidUrlException) {
            $msg = $this->l10n->t('Website not found');
        } else if ($ex instanceof MaxRedirectException) {
            $msg = $this->l10n->t('More redirects than allowed, aborting');
        } else if ($ex instanceof MaxSizeException) {
            $msg = $this->l10n->t('Bigger than maximum allowed size');
        } else if ($ex instanceof TimeoutException) {
            $msg = $this->l10n->t('Request timed out');
        } else if ($ex instanceof UnauthorizedException) {
            $msg = $this->l10n->t('Required credentials for feed were ' .
                'either missing or incorrect');
        } else if ($ex instanceof ForbiddenException) {
            $msg = $this->l10n->t('Forbidden to access feed');
        }

        throw new FetcherException($msg);
    }

    private function buildCurlSslErrorMessage($errorCode) {
        switch ($errorCode) {
            case 35: // CURLE_SSL_CONNECT_ERROR
                return $this->l10n->t(
                    'Certificate error: A problem occurred ' .
                    'somewhere in the SSL/TLS handshake. Could be ' .
                    'certificates (file formats, paths, permissions), ' .
                    'passwords, and others.'
                );
            case 51: // CURLE_PEER_FAILED_VERIFICATION
                return $this->l10n->t(
                    'Certificate error: The remote server\'s SSL ' .
                    'certificate or SSH md5 fingerprint was deemed not OK.'
                );
            case 58: // CURLE_SSL_CERTPROBLEM
                return $this->l10n->t(
                    'Certificate error: Problem with the local client ' .
                    'certificate.'
                );
            case 59: // CURLE_SSL_CIPHER
                return $this->l10n->t(
                    'Certificate error: Couldn\'t use specified cipher.'
                );
            case 60: // CURLE_SSL_CACERT
                return $this->l10n->t(
                    'Certificate error: Peer certificate cannot be ' .
                    'authenticated with known CA certificates.'
                );
            case 64: // CURLE_USE_SSL_FAILED
                return $this->l10n->t(
                    'Certificate error: Requested FTP SSL level failed.'
                );
            case 66: // CURLE_SSL_ENGINE_INITFAILED
                return $this->l10n->t(
                    'Certificate error: Initiating the SSL Engine failed.'
                );
            case 77: // CURLE_SSL_CACERT_BADFILE
                return $this->l10n->t(
                    'Certificate error: Problem with reading the SSL CA ' .
                    'cert (path? access rights?)'
                );
            case 83: // CURLE_SSL_ISSUER_ERROR
                return $this->l10n->t(
                    'Certificate error: Issuer check failed'
                );
            default:
                return $this->l10n->t('Unknown SSL certificate error!');
        }
    }

    private function decodeTwice($string) {
        return html_entity_decode(
            html_entity_decode(
                $string, ENT_QUOTES | ENT_HTML5, 'UTF-8'
            ),
            ENT_QUOTES | ENT_HTML5, 'UTF-8'
        );
    }


    protected function determineRtl($parsedItem, $parsedFeed) {
        $itemLang = $parsedItem->getLanguage();
        $feedLang = $parsedFeed->getLanguage();

        if ($itemLang) {
            return Parser::isLanguageRTL($itemLang);
        } else {
            return Parser::isLanguageRTL($feedLang);
        }
    }


    protected function buildItem($parsedItem, $parsedFeed) {
        $item = new Item();
        $item->setUnread();
        $item->setUrl($parsedItem->getUrl());
        $item->setGuid($parsedItem->getId());
        $item->setGuidHash($item->getGuid());
        $item->setPubDate($parsedItem->getPublishedDate()->getTimestamp());
        $item->setUpdatedDate($parsedItem->getUpdatedDate()->getTimestamp());
        $item->setRtl($this->determineRtl($parsedItem, $parsedFeed));

        // unescape content because angularjs helps against XSS
        $item->setTitle($this->decodeTwice($parsedItem->getTitle()));
        $item->setAuthor($this->decodeTwice($parsedItem->getAuthor()));

        // purification is done in the service layer
        $body = $parsedItem->getContent();
        $body = mb_convert_encoding($body, 'HTML-ENTITIES',
            mb_detect_encoding($body));
        $item->setBody($body);

        $enclosureUrl = $parsedItem->getEnclosureUrl();
        if ($enclosureUrl) {
            $enclosureType = $parsedItem->getEnclosureType();
            if (stripos($enclosureType, 'audio/') !== false ||
                stripos($enclosureType, 'video/') !== false
            ) {
                $item->setEnclosureMime($enclosureType);
                $item->setEnclosureLink($enclosureUrl);
            }
        }

        $item->generateSearchIndex();

        return $item;
    }


    protected function buildFeed($parsedFeed, $url, $getFavicon, $modified,
                                 $etag, $location) {
        $feed = new Feed();

        $link = $parsedFeed->getSiteUrl();

        if (!$link) {
            $link = $location;
        }

        // unescape content because angularjs helps against XSS
        $title = strip_tags($this->decodeTwice($parsedFeed->getTitle()));
        $feed->setTitle($title);
        $feed->setUrl($url);  // the url used to add the feed
        $feed->setLocation($location);  // the url where the feed was found
        $feed->setLink($link);  // <link> attribute in the feed
        $feed->setHttpLastModified($modified);
        $feed->setHttpEtag($etag);
        $feed->setAdded($this->time->getTime());

        if ($getFavicon) {
            $faviconFetcher = $this->faviconFactory->build();
            $favicon = $faviconFetcher->find($feed->getLink());
            $feed->setFaviconLink($favicon);
        }

        return $feed;
    }

}
