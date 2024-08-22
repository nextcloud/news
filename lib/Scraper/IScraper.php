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

interface IScraper
{
    /**
     * Scrape feed url
     *
     * @param string $url
     *
     * @return bool False if failed
     *
     */
    public function scrape(string $url): bool;

    /**
     * Get the scraped content
     *
     * @return string|null
     *
     */
    public function getContent(): ?string;

    /**
     * Get the RTL (right-to-left) information
     *
     * @param  bool $default Return this value if the scraper is unable to determine it
     *
     * @return bool
     *
     */
    public function getRTL(bool $default = false): bool;
}
