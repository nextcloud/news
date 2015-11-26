<?php
/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2015
 */

namespace OCA\News\Upgrade;

use OCP\IConfig;
use OCA\News\Service\ItemService;

class Upgrade {

    /** @var IConfig */
    private $config;

    /** @var ItemService */
    private $itemService;

    private $appName;

    /**
     * Upgrade constructor.
     * @param IConfig $config
     * @param $appName
     */
    public function __construct(IConfig $config, ItemService $itemService,
                                $appName) {
        $this->config = $config;
        $this->appName = $appName;
        $this->itemService = $itemService;
    }

    public function upgrade() {
        $previousVersion = $this->config->getAppValue(
            $this->appName, 'installed_version'
        );

        if (version_compare($previousVersion, '7', '<')) {
            $this->itemService->generateSearchIndices();
        }
    }

}
