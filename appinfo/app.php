<?php

/**
* ownCloud - News app (feed reader)
*
* @author Alessandro Cosentino
* @copyright 2012 Alessandro Cosentino cosenal@gmail.com
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
* You should have received a copy of the GNU Lesser General Public 
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
* 
*/

OC::$CLASSPATH['OC_News_Item'] = 'apps/news/lib/item.php';
OC::$CLASSPATH['OC_News_Feed'] = 'apps/news/lib/feed.php';
OC::$CLASSPATH['OC_News_FeedMapper'] = 'apps/news/lib/feedmapper.php';
OC::$CLASSPATH['OC_News_ItemMapper'] = 'apps/news/lib/itemmapper.php';


$l = new OC_l10n('news');

OCP\App::registerAdmin('news','settings');

OCP\App::register( array( 
  'order' => 70, 
  'id' => 'news', 
  'name' => 'News' 
));

OCP\App::addNavigationEntry( array( 
  'id' => 'news', 
  'order' => 74, 
  'href' => OC_Helper::linkTo( 'news', 'index.php' ), 
  'icon' => OC_Helper::imagePath( 'news', 'icon.svg' ), 
  'name' => $l->t('News')
));

