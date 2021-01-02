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

namespace OCA\News\Service\Exceptions;

use Exception;
use OCP\AppFramework\Db\IMapperException;

/**
 * Class ServiceNotFoundException
 *
 * @package OCA\News\Service\Exceptions
 */
class ServiceNotFoundException extends ServiceException
{
    /**
     * @inheritDoc
     */
    public static function from(IMapperException $exception): ServiceException
    {
        return new self($exception->getMessage(), $exception->getCode(), $exception);
    }
}
