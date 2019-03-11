<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Sean Molenaar <smillernl@me.com>
 * @copyright 2018 Sean Molenaar
 */

namespace OCA\News\Config;

use FeedIo\Adapter\ResponseInterface;
use GuzzleHttp\Message\ResponseInterface as GuzzleResponseInterface;

/**
 * Guzzle dependent HTTP Response
 */
class LegacyGuzzleResponse implements ResponseInterface
{
    const HTTP_LAST_MODIFIED = 'Last-Modified';

    /**
     * @var \GuzzleHttp\Message\ResponseInterface
     */
    protected $response;

    /**
     * @param \GuzzleHttp\Message\ResponseInterface
     */
    public function __construct(GuzzleResponseInterface $psrResponse)
    {
        $this->response = $psrResponse;
    }

    /**
     * @return boolean
     */
    public function isModified()
    {
        return $this->response->getStatusCode() !== 304 && $this->response->getBody()->getSize() > 0;
    }

    /**
     * @return \Psr\Http\Message\StreamInterface
     */
    public function getBody()
    {
        return $this->response->getBody();
    }

    /**
     * @return \DateTime|null
     */
    public function getLastModified()
    {
        if ($this->response->hasHeader(static::HTTP_LAST_MODIFIED)) {
            $lastModified = \DateTime::createFromFormat(
                \DateTime::RFC2822,
                $this->getHeader(static::HTTP_LAST_MODIFIED)
            );

            return false === $lastModified ? null : $lastModified;
        }

        return;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->response->getHeaders();
    }

    /**
     * @param  string       $name
     * @return string[]
     */
    public function getHeader($name)
    {
        return current($this->response->getHeader($name));
    }
}
