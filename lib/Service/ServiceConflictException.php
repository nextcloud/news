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

namespace OCA\News\Service;


class ServiceConflictException extends ServiceException
{

    /**
     * Constructor
     *
     * @param string $msg the error message
     */
    public function __construct($msg)
    {
        parent::__construct($msg);
    }

}