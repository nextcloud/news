/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */

//angular
import angular from 'angular';
//configs
import Config from './Config';
import Run from './Run';
//controllers
import AppController from '../controller/AppController';
import ContentController from '../controller/ContentController';
import ExploreController from '../controller/ExploreController';
import NavigationController from '../controller/NavigationController';
import SettingsController from '../controller/SettingsController';
//directives
import {
    AppNavigationEntryUtils,
    AppNavigationEntryUtilsRun
} from '../directive/AppNavigationEntryUtils';
import newsAddFeed from '../directive/NewsAddFeed';
import newsArticleActions from '../directive/NewsArticleActions';
import newsAutoFocus from '../directive/NewsAutoFocus';
import newsBindUnsafeHtml from '../directive/NewsBindUnsafeHtml';
import newsDraggable from '../directive/NewsDraggable';
import newsDroppable from '../directive/NewsDroppable';
import newsFinishedTransition from '../directive/NewsFinishedTransition';
import newsFocus from '../directive/NewsFocus';
import newsInstantNotification from '../directive/NewsInstantNotification';
import newsOnActive from '../directive/NewsOnActive';
import newsPlayOne from '../directive/NewsPlayOne';
import newsReadFile from '../directive/NewsReadFile';
import newsRefreshMasonry from '../directive/NewsRefreshMasonry';
import newsScroll from '../directive/NewsScroll';
import newsSearch from '../directive/NewsSearch';
import newsStickyMenu from '../directive/NewsStickyMenu';
import newsStopPropagation from '../directive/NewsStopPropagation';
import newsTimeout from '../directive/NewsTimeout';
import newsTitleUnreadCount from '../directive/NewsTitleUnreadCount';
import newsToggleShow from '../directive/NewsToggleShow';
import newsTriggerClick from '../directive/NewsTriggerClick';
//filters
import trustUrl from '../filter/TrustUrl';
import unreadCountFormatter from '../filter/UnreadCountFormatter';
//services
import FeedResource from '../service/FeedResource';
import FolderResource from '../service/FolderResource';
import ItemResource from '../service/ItemResource';
import Loading from '../service/Loading';
import OPMLImporter from '../service/OPMLImporter';
import OPMLParser from '../service/OPMLParser';
import Publisher from '../service/Publisher';
import Resource from '../service/Resource';
import SettingsResource from '../service/SettingsResource';

//angular modules
import ngRoute from 'angular-route';
import ngSanitize from 'angular-sanitize';
import ngAnimate from 'angular-animate';

//masonry
import 'moment/min/moment-with-locales.min';
import Masonry from 'masonry-layout';

import jQueryBridget from 'jquery-bridget';
jQueryBridget('masonry', Masonry, jQuery);

//scripts
import '../gui/ExternSubscription';
import '../gui/Fixes';
import '../gui/KeyboardShortcuts';
import '../plugin/ArticleActionPlugin';

/* jshint unused: false */
angular.module('News', [ngRoute, ngSanitize, ngAnimate])
    //config
    .config(Config)
    .run(Run)
    //controllers
    .controller('AppController', AppController)
    .controller('ContentController', ContentController)
    .controller('ExploreController', ExploreController)
    .controller('NavigationController', NavigationController)
    .controller('SettingsController', SettingsController)
    //filters
    .filter('trustUrl', trustUrl)
    .filter('unreadCountFormatter', unreadCountFormatter)
    //services
    .factory('FeedResource', FeedResource)
    .factory('FolderResource', FolderResource)
    .factory('ItemResource', ItemResource)
    .service('Loading', Loading)
    .service('OPMLImporter', OPMLImporter)
    .service('OPMLParser', OPMLParser)
    .service('Publisher', Publisher)
    .factory('Resource', Resource)
    .service('SettingsResource', SettingsResource)
    //directives
    .run(AppNavigationEntryUtilsRun)
    .directive('appNavigationEntryUtils', AppNavigationEntryUtils)
    .directive('newsAddFeed', newsAddFeed)
    .directive('newsArticleActions', newsArticleActions)
    .directive('newsAutoFocus', newsAutoFocus)
    .directive('newsBindUnsafeHtml', newsBindUnsafeHtml)
    .directive('newsDraggable', newsDraggable)
    .directive('newsDroppable', newsDroppable)
    .directive('newsFinishedTransition', newsFinishedTransition)
    .directive('newsFocus', newsFocus)
    .directive('newsInstantNotification', newsInstantNotification)
    .directive('newsOnActive', newsOnActive)
    .directive('newsPlayOne', newsPlayOne)
    .directive('newsReadFile', newsReadFile)
    .directive('newsRefreshMasonry', newsRefreshMasonry)
    .directive('newsScroll', newsScroll)
    .directive('newsSearch', newsSearch)
    .directive('newsStickyMenu', newsStickyMenu)
    .directive('newsStopPropagation', newsStopPropagation)
    .directive('newsTimeout', newsTimeout)
    .directive('newsTitleUnreadCount', newsTitleUnreadCount)
    .directive('newsToggleShow', newsToggleShow)
    .directive('newsTriggerClick', newsTriggerClick)
;
