<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 */

namespace OCA\News\Http;

use OCA\News\Vendor\Psr\Http\Client\RequestExceptionInterface;
use OCA\News\Vendor\Psr\Http\Message\RequestInterface;

class ScopedRequestException extends \RuntimeException implements RequestExceptionInterface
{
    public function __construct(
        private readonly RequestInterface $request,
        string $message,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
