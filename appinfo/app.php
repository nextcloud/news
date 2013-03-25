<?php
/**
* ownCloud - News app
*
* @author Alessandro Cosentino
* @author Bernhard Posselt
* @copyright 2012 Alessandro Cosentino cosenal@gmail.com
* @copyright 2012 Bernhard Posselt nukeawhale@gmail.com
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
* License as published by the Free Software Foundation; either
* version 3 of the License, or any later version.
*
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*
* You should have received a copy of the GNU Affero General Public
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
*
*/

namespace OCA\News;

use \OCA\AppFramework\Core\API;


$api = new API('news');

$api->addNavigationEntry(array(

  // the string under which your app will be referenced in owncloud
  'id' => $api->getAppName(),

  // sorting weight for the navigation. The higher the number, the higher
  // will it be listed in the navigation
  'order' => 10,

  // the route that will be shown on startup
  'href' => $api->linkToRoute('news_index'),

  // the icon that will be shown in the navigation
  // this file needs to exist in img/example.png
  'icon' => $api->imagePath('news.svg'),

  // the title of your application. This will be used in the
  // navigation or on the settings page of your app
  'name' => $api->getTrans()->t('News')

));


\OCP\Backgroundjob::addRegularTask( 'OCA\News\Backgroundjob\Task', 'run');

/* TODO: fix this classes and uncomment these lines afterwards
\OC_Search::registerProvider('OCA\News\Search\Provider');
*/

