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

use OCA\News\Command\GenerateSearchIndices;
use OCA\News\Command\VerifyInstall;

$newsApp = new OCA\News\AppInfo\Application();
$newsContainer = $newsApp->getContainer();
$newsCmd = $newsContainer->query(GenerateSearchIndices::class);
$verifyCmd = $newsContainer->query(VerifyInstall::class);

$application->add($newsCmd);
$application->add($verifyCmd);
