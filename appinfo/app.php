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

namespace OCA\News;

require_once \OC_App::getAppPath('news') . '/appinfo/bootstrap.php';


\OCP\App::addNavigationEntry( array(
  'id' => 'news',
  'order' => 74,
  'href' => \OC_Helper::linkToRoute('news_index'),
  'icon' => \OC_Helper::imagePath( 'news', 'news.svg' ),
  'name' => \OC_L10N::get('news')->t('News')
));

\OC_Search::registerProvider('OC_Search_Provider_News');

\OCP\Backgroundjob::addRegularTask( 'OCA\News\Backgroundjob', 'run' );
\OCP\Share::registerBackend('news_item', 'OCA\News\Share_Backend_News_Item');


