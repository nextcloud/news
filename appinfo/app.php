<?php
/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Alessandro Cosentino <cosenal@gmail.com>
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Alessandro Cosentino 2012
 * @copyright Bernhard Posselt 2012, 2014
 */

namespace OCA\News;


\OCP\App::addNavigationEntry(array(

	// the string under which your app will be referenced in owncloud
	'id' => 'news',

	// sorting weight for the navigation. The higher the number, the higher
	// will it be listed in the navigation
	'order' => 10,

	// the route that will be shown on startup
	'href' => \OCP\Util::linkToRoute('news.page.index'),

	// the icon that will be shown in the navigation
	// this file needs to exist in img/example.png
	'icon' => \OCP\Util::imagePath('news', 'app.svg'),

	// the title of your application. This will be used in the
	// navigation or on the settings page of your app
	'name' => \OC_L10N::get('news')->t('News')

));

\OCP\Backgroundjob::addRegularTask('OCA\News\Backgroundjob\Task', 'run');
\OCP\Util::connectHook('OC_User', 'pre_deleteUser', 'OCA\News\Hooks\User', 'deleteUser');
