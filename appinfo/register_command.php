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

$newsApp = new OCA\News\AppInfo\Application();
$newsContainer = $newsApp->getContainer();
$newsCmd = $newsContainer->query('OCA\News\Command\GenerateSearchIndices');

$application->add($newsCmd);
