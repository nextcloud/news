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

namespace OCA\News\AppInfo;

use OCA\News\Upgrade\Upgrade;

throw new \Exception('heheheo');
$app = new Application();
$app->getContainer()->query(Upgrade::class)->preUpgrade();
