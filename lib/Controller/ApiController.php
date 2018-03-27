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

namespace OCA\News\Controller;

use OCP\IRequest;
use OCP\AppFramework\ApiController as BaseApiController;

/**
 * Class ApiController
 *
 * @package OCA\News\Controller
 */
class ApiController extends BaseApiController
{
    /**
     * ApiController constructor.
     *
     * @param string   $appName The name of the app
     * @param IRequest $request The request
     */
    public function __construct($appName, IRequest $request)
    {
        parent::__construct($appName, $request);
    }

    /**
     * Indication of the API levels
     *
     * @PublicPage
     * @NoCSRFRequired
     * @CORS
     *
     * @return array
     */
    public function index()
    {
        return [
            'apiLevels' => ['v1-2']
        ];
    }

}
