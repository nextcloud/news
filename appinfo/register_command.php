<?php
/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2016
 */

namespace OCA\News\AppInfo;

use OCA\News\Command\Updater\UpdateFeed;
use OCA\News\Command\Updater\AllFeeds;
use OCA\News\Command\Updater\BeforeUpdate;
use OCA\News\Command\Updater\AfterUpdate;

$app = new Application();
$container = $app->getContainer();
$application->add($container->query(AllFeeds::class));
$application->add($container->query(UpdateFeed::class));
$application->add($container->query(BeforeUpdate::class));
$application->add($container->query(AfterUpdate::class));
