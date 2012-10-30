<?php
/**
* ownCloud - News app
*
* @author Alessandro Cosentino
* Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
*
* This file is licensed under the Affero General Public License version 3 or later.
* See the COPYING-README file
*
*/

OC::$CLASSPATH['OCA\News\StatusFlag'] = 'apps/news/lib/item.php';
OC::$CLASSPATH['OCA\News\Item'] = 'apps/news/lib/item.php';
OC::$CLASSPATH['OCA\News\Collection'] = 'apps/news/lib/collection.php';
OC::$CLASSPATH['OCA\News\Feed'] = 'apps/news/lib/feed.php';
OC::$CLASSPATH['OCA\News\Folder'] = 'apps/news/lib/folder.php';
OC::$CLASSPATH['OCA\News\FeedType'] = 'apps/news/lib/feedtypes.php';

OC::$CLASSPATH['OCA\News\FeedMapper'] = 'apps/news/lib/feedmapper.php';
OC::$CLASSPATH['OCA\News\ItemMapper'] = 'apps/news/lib/itemmapper.php';
OC::$CLASSPATH['OCA\News\FolderMapper'] = 'apps/news/lib/foldermapper.php';

OC::$CLASSPATH['OCA\News\Utils'] = 'apps/news/lib/utils.php';

OC::$CLASSPATH['OC_Search_Provider_News'] = 'apps/news/lib/search.php';

OC::$CLASSPATH['OCA\News\Backgroundjob'] = 'apps/news/lib/backgroundjob.php';
OCP\Backgroundjob::addRegularTask( 'OCA\News\Backgroundjob', 'run' );

OC::$CLASSPATH['OCA\News\Share_Backend_News_Item'] = 'apps/news/lib/share/item.php';

OCP\App::addNavigationEntry( array(
  'id' => 'news',
  'order' => 74,
  'href' => OC_Helper::linkTo( 'news', 'index.php' ),
  'icon' => OC_Helper::imagePath( 'news', 'icon.svg' ),
  'name' => OC_L10N::get('news')->t('News')
));

OC_Search::registerProvider('OC_Search_Provider_News');

OCP\Share::registerBackend('news_item', 'OCA\News\Share_Backend_News_Item');
