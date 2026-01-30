<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Gioele Falcetti <thegio.f@gmail.com>
 * @copyright 2019 Gioele Falcetti
 */

namespace OCA\News\Scraper;

use OCA\News\Vendor\fivefilters\Readability\Readability;
use OCA\News\Vendor\fivefilters\Readability\Configuration;
use OCA\News\Vendor\fivefilters\Readability\ParseException;
use OCA\News\Vendor\League\Uri\Exceptions\SyntaxError;
use Psr\Log\LoggerInterface;
use OCA\News\Config\FetcherConfig;

class Scraper implements IScraper
{
    private $logger;
    private $config;
    private $readability;
    private $curl_opts;
    private $fetcherConfig;

    public function __construct(LoggerInterface $logger, FetcherConfig $fetcherConfig)
    {
        $this->logger = $logger;
        $this->fetcherConfig = $fetcherConfig;
        $this->config = new Configuration([
            'FixRelativeURLs' => true,
            'SummonCthulhu' => true, // Remove <script>
        ]);
        $this->readability = null;

        $this->curl_opts = array(
            CURLOPT_RETURNTRANSFER => true,     // return web page
            CURLOPT_HEADER         => false,    // do not return headers
            CURLOPT_FOLLOWLOCATION => true,     // follow redirects
            CURLOPT_USERAGENT      => $this->fetcherConfig->getUserAgent(), // who am i
            CURLOPT_AUTOREFERER    => true,     // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
            CURLOPT_TIMEOUT        => 120,      // timeout on response
            CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
        );

        $proxy = $this->fetcherConfig->getProxy();
        if (!is_null($proxy) && $proxy !== '') {
            $this->curl_opts[CURLOPT_PROXY] = $proxy;
        }
    }

    private function getHTTPContent(string $url): array
    {
        $handler = curl_init($url);
        curl_setopt_array($handler, $this->curl_opts);
        $content = curl_exec($handler);
        $header  = curl_getinfo($handler);
        curl_close($handler);

        $charset = null;
        // check if charset is set in http header
        if (isset($header['content_type']) &&
            preg_match('/charset\s*=\s*"?([\w\-]+)"?/i', $header['content_type'], $m)) {
            $charset = strtoupper($m[1]);
        }
        // search content for meta tag with charset
        if ($charset === null &&
            preg_match('/<meta[^>]+charset\s*=\s*["\']?([\w\-]+)["\']?[^>]+>/i', $content, $m)) {
            $charset = strtoupper($m[1]);
        }
        // try to detect encoding
        if ($charset === null) {
            $encodingList = ['UTF-8', 'ISO-8859-1', 'Windows-1252', 'ASCII', 'UTF-16', 'UTF-16BE', 'UTF-16LE'];
            $charset = mb_detect_encoding($content, $encodingList, true);
        }
        // convert to utf-8 if necessary
        if ($charset !== null && $charset !== 'UTF-8') {
            $convertedContent = mb_convert_encoding($content, 'UTF-8', $charset);
            if ($convertedContent !== false) {
                $content = $convertedContent;
            } else {
                $this->logger->warning(
                    'Failed to convert encoding from {from} to UTF-8 for feed item',
                    ['from' => $charset]
                );
            }
        }

        // Update the url after the redirects has been followed
        $url = $header['url'];
        return array($content, $header['url']);
    }

    public function scrape(string $url): bool
    {
        list($content, $redirected_url) = $this->getHTTPContent($url);
        if ($content === false) {
            $this->logger->error('Unable to receive content from {url}', [
                 'url' => $url,
            ]);
            $this->readability = null;
            return false;
        }

        // Update URL used to convert relative URLs
        $this->config->setOriginalURL($redirected_url);
        $this->readability = new Readability($this->config);

        try {
            $this->readability->parse($content);
        } catch (ParseException | SyntaxError $e) {
            $this->logger->error('Unable to parse content from {url}', [
                 'url' => $url,
            ]);
            $this->logger->debug('Error during parsing of {url} ran into {error}', [
                'url' => $url,
                'error' => $e,
            ]);
        }
        return true;
    }

    public function getContent(): ?string
    {
        if ($this->readability === null) {
            return null;
        }
        return $this->readability->getContent();
    }

    public function getRTL(bool $default = false): bool
    {
        if ($this->readability === null) {
            return $default;
        }

        $RTL = $this->readability->getDirection();
        if ($RTL === null) {
            return $default;
        }
        return $RTL === "rtl";
    }
}
