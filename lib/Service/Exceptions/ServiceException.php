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
 * Class ServiceException
 *
 * @package OCA\News\Service\Exceptions
 */
abstract class ServiceException extends Exception
{

    /**
     * Constructor
     *
     * @param string         $msg the error message
     * @param int            $code
     * @param Exception|null $previous
     */
    final public function __construct(string $msg, int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($msg, $code, $previous);
    }

    /**
     * Create exception from Mapper exception.
     *
     * @param IMapperException $exception Existing exception
     *
     * @return static
     */
    abstract public static function from(IMapperException $exception): ServiceException;
}
