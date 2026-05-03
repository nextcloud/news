<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 */

namespace OCA\News\Http;

use OCA\News\Vendor\Psr\Http\Client\NetworkExceptionInterface;

class ScopedNetworkException extends ScopedRequestException implements NetworkExceptionInterface
{
}
