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

use OCA\News\Utility\PsrLogger;

use andreskrey\Readability\Readability;
use andreskrey\Readability\Configuration;
use andreskrey\Readability\ParseException;

class Scraper implements IScraper
{
    private $logger;
    private $config;
    private $readability;
    private $curl_opts;

    public function __construct(PsrLogger $logger)
    {
        $this->logger = $logger;
        $this->config = new Configuration([
            'FixRelativeURLs' => true,
            'SummonCthulhu' => true, // Remove <script>
        ]);
        $this->readability = null;

        $this->curl_opts = array(
            CURLOPT_RETURNTRANSFER => true,     // return web page
            CURLOPT_HEADER         => false,    // do not return headers
            CURLOPT_FOLLOWLOCATION => true,     // follow redirects
            //CURLOPT_USERAGENT    => "php-news", // who am i
            CURLOPT_AUTOREFERER    => true,     // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
            CURLOPT_TIMEOUT        => 120,      // timeout on response
            CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
        );
    }

    private function getHTTPContent(string $url): array
    {
        $handler = curl_init($url);
        curl_setopt_array($handler, $this->curl_opts);
        $content = curl_exec($handler);
        $header  = curl_getinfo($handler);
        curl_close($handler);

        // Update the url after the redirects has been followed
        $url = $header['url'];
        return array($content, $header['url']);
    }

    public function scrape(string $url): bool
    {
        list($content, $redirected_url) = $this->getHTTPContent($url);
        if ($content === false) {
            $this->logger->error('Unable to recive content from {url}', [
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
        } catch (ParseException $e) {
            $this->logger->error('Unable to parse content from {url}', [
                 'url' => $url,
            ]);
        }
        return true;
    }

    public function getContent(): string
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
