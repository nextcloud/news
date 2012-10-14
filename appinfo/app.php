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

OC::$CLASSPATH['OCA\News\FeedMapper'] = 'apps/news/lib/feedmapper.php';
OC::$CLASSPATH['OCA\News\ItemMapper'] = 'apps/news/lib/itemmapper.php';
OC::$CLASSPATH['OCA\News\FolderMapper'] = 'apps/news/lib/foldermapper.php';

OC::$CLASSPATH['OCA\News\Utils'] = 'apps/news/lib/utils.php';

OC::$CLASSPATH['OCA\News\Backgroundjob'] = 'apps/news/lib/backgroundjob.php';
OCP\Backgroundjob::addRegularTask( 'OCA\News\Backgroundjob', 'run' );

$l = new OC_l10n('news');

OCP\App::addNavigationEntry( array(
  'id' => 'news',
  'order' => 74,
  'href' => OC_Helper::linkTo( 'news', 'index.php' ),
  'icon' => OC_Helper::imagePath( 'news', 'icon.svg' ),
  'name' => $l->t('News')
));
