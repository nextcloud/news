var $__app__ = (function() {
  "use strict";
  var __moduleName = "app";
  (function(window, document, angular, $, OC, csrfToken, undefined) {
    'use strict';
    var app = angular.module('News', ['ngRoute', 'ngSanitize', 'ngAnimate']);
    app.config(["$routeProvider", "$provide", "$httpProvider", function($routeProvider, $provide, $httpProvider) {
      'use strict';
      var feedType = {
        FEED: 0,
        FOLDER: 1,
        STARRED: 2,
        SUBSCRIPTIONS: 3,
        SHARED: 4
      };
      $provide.constant('REFRESH_RATE', 60);
      $provide.constant('ITEM_BATCH_SIZE', 50);
      $provide.constant('BASE_URL', OC.generateUrl('/apps/news'));
      $provide.constant('FEED_TYPE', feedType);
      $provide.factory('CSRFInterceptor', (["$q", "BASE_URL", function($q, BASE_URL) {
        return {request: (function(config) {
            if (config.url.indexOf(BASE_URL) === 0) {
              config.headers.requesttoken = csrfToken;
            }
            return config || $q.when(config);
          })};
      }]));
      $httpProvider.interceptors.push('CSRFInterceptor');
      var getResolve = (function(type) {
        return {data: ['$http', '$route', '$q', 'BASE_URL', 'ITEM_BATCH_SIZE', (function($http, $route, $q, BASE_URL, ITEM_BATCH_SIZE) {
            var parameters = {
              type: type,
              limit: ITEM_BATCH_SIZE
            };
            if ($route.current.params.id !== undefined) {
              parameters.id = $route.current.params.id;
            }
            var deferred = $q.defer();
            $http({
              url: (BASE_URL + "/items"),
              method: 'GET',
              params: parameters
            }).success((function(data) {
              deferred.resolve(data);
            }));
            return deferred.promise;
          })]};
      });
      $routeProvider.when('/items', {
        controller: 'ContentController as Content',
        templateUrl: 'content.html',
        resolve: getResolve(feedType.SUBSCRIPTIONS),
        type: feedType.SUBSCRIPTIONS
      }).when('/items/starred', {
        controller: 'ContentController as Content',
        templateUrl: 'content.html',
        resolve: getResolve(feedType.STARRED),
        type: feedType.STARRED
      }).when('/items/feeds/:id', {
        controller: 'ContentController as Content',
        templateUrl: 'content.html',
        resolve: getResolve(feedType.FEED),
        type: feedType.FEED
      }).when('/items/folders/:id', {
        controller: 'ContentController as Content',
        templateUrl: 'content.html',
        resolve: getResolve(feedType.FOLDER),
        type: feedType.FOLDER
      });
    }]);
    app.run((["$rootScope", "$location", "$http", "$q", "$interval", "Loading", "ItemResource", "FeedResource", "FolderResource", "SettingsResource", "Publisher", "BASE_URL", "FEED_TYPE", "REFRESH_RATE", function($rootScope, $location, $http, $q, $interval, Loading, ItemResource, FeedResource, FolderResource, SettingsResource, Publisher, BASE_URL, FEED_TYPE, REFRESH_RATE) {
      'use strict';
      Loading.setLoading('global', true);
      Publisher.subscribe(ItemResource).toChannels('items', 'newestItemId', 'starred');
      Publisher.subscribe(FolderResource).toChannels('folders');
      Publisher.subscribe(FeedResource).toChannels('feeds');
      Publisher.subscribe(SettingsResource).toChannels('settings');
      var settingsDeferred = $q.defer();
      $http.get((BASE_URL + "/settings")).success((function(data) {
        Publisher.publishAll(data);
        settingsDeferred.resolve();
      }));
      var activeFeedDeferred = $q.defer();
      var path = $location.path();
      $http.get((BASE_URL + "/feeds/active")).success((function(data) {
        var url;
        switch (data.activeFeed.type) {
          case FEED_TYPE.FEED:
            url = ("/items/feeds/" + data.activeFeed.id);
            break;
          case FEED_TYPE.FOLDER:
            url = ("/items/folders/" + data.activeFeed.id);
            break;
          case FEED_TYPE.STARRED:
            url = '/items/starred';
            break;
          default:
            url = '/items';
        }
        if (!/^\/items(\/(starred|feeds\/\d+|folders\/\d+))?\/?$/.test(path)) {
          $location.path(url);
        }
        activeFeedDeferred.resolve();
      }));
      var folderDeferred = $q.defer();
      $http.get((BASE_URL + "/folders")).success((function(data) {
        Publisher.publishAll(data);
        folderDeferred.resolve();
      }));
      var feedDeferred = $q.defer();
      $http.get((BASE_URL + "/feeds")).success((function(data) {
        Publisher.publishAll(data);
        feedDeferred.resolve();
      }));
      $q.all([settingsDeferred.promise, activeFeedDeferred.promise, feedDeferred.promise, folderDeferred.promise]).then((function() {
        Loading.setLoading('global', false);
      }));
      $interval((function() {
        $http.get((BASE_URL + "/feeds"));
        $http.get((BASE_URL + "/folders"));
      }), REFRESH_RATE * 1000);
      $rootScope.$on('$routeChangeStart', (function() {
        Loading.setLoading('content', true);
      }));
      $rootScope.$on('$routeChangeSuccess', (function() {
        Loading.setLoading('content', false);
      }));
      $rootScope.$on('$routeChangeError', (function() {
        $location.path('/items');
      }));
    }]));
    app.controller('AppController', ["Loading", "FeedResource", "FolderResource", function(Loading, FeedResource, FolderResource) {
      'use strict';
      this.loading = Loading;
      this.isFirstRun = (function() {
        return FeedResource.size() === 0 && FolderResource.size() === 0;
      });
    }]);
    app.controller('ContentController', ["Publisher", "FeedResource", "ItemResource", "SettingsResource", "data", "$route", "$routeParams", function(Publisher, FeedResource, ItemResource, SettingsResource, data, $route, $routeParams) {
      'use strict';
      var $__0 = this;
      ItemResource.clear();
      Publisher.publishAll(data);
      this.isAutoPagingEnabled = true;
      this.getItems = (function() {
        return ItemResource.getAll();
      });
      this.toggleStar = (function(itemId) {
        ItemResource.toggleStar(itemId);
      });
      this.markRead = (function(itemId) {
        var item = ItemResource.get(itemId);
        if (!item.keepUnread) {
          ItemResource.markItemRead(itemId);
          FeedResource.markItemOfFeedRead(item.feedId);
        }
      });
      this.getFeed = (function(feedId) {
        return FeedResource.getById(feedId);
      });
      this.toggleKeepUnread = (function(itemId) {
        var item = ItemResource.get(itemId);
        if (!item.unread) {
          FeedResource.markItemOfFeedUnread(item.feedId);
          ItemResource.markItemRead(itemId, false);
        }
        item.keepUnread = !item.keepUnread;
      });
      this.orderBy = (function() {
        if (SettingsResource.get('oldestFirst')) {
          return '-id';
        } else {
          return 'id';
        }
      });
      this.isCompactView = (function() {
        return SettingsResource.get('compact');
      });
      this.autoPagingEnabled = (function() {
        return $__0.isAutoPagingEnabled;
      });
      this.markReadEnabled = (function() {
        return !SettingsResource.get('preventReadOnScroll');
      });
      this.scrollRead = (function(itemIds) {
        var itemId$__9;
        var item$__10;
        var ids = [];
        var feedIds = [];
        for (var $__3 = itemIds[$traceurRuntime.toProperty(Symbol.iterator)](),
            $__4; !($__4 = $__3.next()).done; ) {
          itemId$__9 = $__4.value;
          {
            item$__10 = ItemResource.get(itemId$__9);
            if (!item$__10.keepUnread) {
              ids.push(itemId$__9);
              feedIds.push(item$__10.feedId);
            }
          }
        }
        FeedResource.markItemsOfFeedsRead(feedIds);
        ItemResource.markItemsRead(ids);
      });
      this.autoPage = (function() {
        $__0.isAutoPagingEnabled = false;
        var type = $route.current.$$route.type;
        var id = $routeParams.id;
        ItemResource.autoPage(type, id).success((function(data) {
          Publisher.publishAll(data);
          if (data.items.length > 0) {
            $__0.isAutoPagingEnabled = true;
          }
        })).error((function() {
          $__0.isAutoPagingEnabled = true;
        }));
      });
      this.getRelativeDate = (function(timestamp) {
        var languageCode$__11;
        var date$__12;
        if (timestamp !== undefined && timestamp !== '') {
          languageCode$__11 = SettingsResource.get('language');
          date$__12 = moment.unix(timestamp).locale(languageCode$__11).fromNow() + '';
          return date$__12;
        } else {
          return '';
        }
      });
    }]);
    app.controller('NavigationController', ["$route", "FEED_TYPE", "FeedResource", "FolderResource", "ItemResource", "SettingsResource", function($route, FEED_TYPE, FeedResource, FolderResource, ItemResource, SettingsResource) {
      'use strict';
      var $__0 = this;
      this.feedError = '';
      this.folderError = '';
      this.getFeeds = (function() {
        return FeedResource.getAll();
      });
      this.getFolders = (function() {
        return FolderResource.getAll();
      });
      this.markFolderRead = (function(folderId) {
        var feed$__13;
        FeedResource.markFolderRead(folderId);
        for (var $__3 = FeedResource.getByFolderId(folderId)[$traceurRuntime.toProperty(Symbol.iterator)](),
            $__4; !($__4 = $__3.next()).done; ) {
          feed$__13 = $__4.value;
          {
            ItemResource.markFeedRead(feed$__13.id);
          }
        }
      });
      this.markFeedRead = (function(feedId) {
        ItemResource.markFeedRead(feedId);
        FeedResource.markFeedRead(feedId);
      });
      this.markRead = (function() {
        ItemResource.markRead();
        FeedResource.markRead();
      });
      this.isShowAll = (function() {
        return SettingsResource.get('showAll');
      });
      this.getFeedsOfFolder = (function(folderId) {
        return FeedResource.getByFolderId(folderId);
      });
      this.getUnreadCount = (function() {
        return FeedResource.getUnreadCount();
      });
      this.getFeedUnreadCount = (function(feedId) {
        return FeedResource.getById(feedId).unreadCount;
      });
      this.getFolderUnreadCount = (function(folderId) {
        return FeedResource.getFolderUnreadCount(folderId);
      });
      this.getStarredCount = (function() {
        return ItemResource.getStarredCount();
      });
      this.toggleFolder = (function(folderName) {
        FolderResource.toggleOpen(folderName);
      });
      this.hasFeeds = (function(folderId) {
        return FeedResource.getFolderUnreadCount(folderId) !== undefined;
      });
      this.subFeedActive = (function(folderId) {
        var feed$__14;
        var type = $route.current.$$route.type;
        if (type === FEED_TYPE.FEED) {
          feed$__14 = FeedResource.getById($route.current.params.id);
          if (feed$__14.folderId === folderId) {
            return true;
          }
        }
        return false;
      });
      this.isSubscriptionsActive = (function() {
        return $route.current && $route.current.$$route.type === FEED_TYPE.SUBSCRIPTIONS;
      });
      this.isStarredActive = (function() {
        return $route.current && $route.current.$$route.type === FEED_TYPE.STARRED;
      });
      this.isFolderActive = (function(folderId) {
        var currentId = parseInt($route.current.params.id, 10);
        return $route.current && $route.current.$$route.type === FEED_TYPE.FOLDER && currentId === folderId;
      });
      this.isFeedActive = (function(feedId) {
        var currentId = parseInt($route.current.params.id, 10);
        return $route.current && $route.current.$$route.type === FEED_TYPE.FEED && currentId === feedId;
      });
      this.folderNameExists = (function(folderName) {
        return FolderResource.get(folderName) !== undefined;
      });
      this.isAddingFolder = (function() {
        return true;
      });
      this.createFolder = (function(folder) {
        console.log(folder.name);
        folder.name = '';
      });
      this.createFeed = (function(feed) {
        $__0.newFolder = false;
        console.log(feed.url + feed.folder);
        feed.url = '';
      });
      this.cancelRenameFolder = (function(folderId) {
        console.log(folderId);
      });
      this.renameFeed = (function(feed) {
        feed.editing = false;
      });
      this.cancelRenameFeed = (function(feedId) {
        console.log(feedId);
      });
      this.renameFolder = (function() {
        console.log('TBD');
      });
      this.deleteFeed = (function(feed) {
        feed.deleted = true;
      });
      this.undeleteFeed = (function(feed) {
        feed.deleted = false;
      });
      this.removeFeed = (function(feed) {
        console.log(feed);
      });
      this.deleteFolder = (function(folderName) {
        console.log(folderName);
      });
      this.moveFeed = (function(feedId, folderId) {
        console.log(feedId + folderId);
      });
    }]);
    app.controller('SettingsController', ["$route", "SettingsResource", "FeedResource", function($route, SettingsResource, FeedResource) {
      'use strict';
      var $__0 = this;
      this.importing = false;
      this.opmlImportError = false;
      this.articleImportError = false;
      var set = (function(key, value) {
        SettingsResource.set(key, value);
        if (['showAll', 'oldestFirst'].indexOf(key) >= 0) {
          $route.reload();
        }
      });
      this.toggleSetting = (function(key) {
        set(key, !$__0.getSetting(key));
      });
      this.getSetting = (function(key) {
        return SettingsResource.get(key);
      });
      this.feedSize = (function() {
        return FeedResource.size();
      });
      this.importOpml = (function(content) {
        console.log(content);
      });
      this.importArticles = (function(content) {
        console.log(content);
      });
    }]);
    app.filter('trustUrl', (["$sce", function($sce) {
      'use strict';
      return (function(url) {
        return $sce.trustAsResourceUrl(url);
      });
    }]));
    app.filter('unreadCountFormatter', (function() {
      'use strict';
      return (function(unreadCount) {
        if (unreadCount > 999) {
          return '999+';
        }
        return unreadCount;
      });
    }));
    app.factory('FeedResource', (["Resource", "$http", "BASE_URL", function(Resource, $http, BASE_URL) {
      'use strict';
      var FeedResource = function FeedResource($http, BASE_URL) {
        $traceurRuntime.superCall(this, $FeedResource.prototype, "constructor", [$http, BASE_URL, 'url']);
        this.ids = {};
        this.unreadCount = 0;
        this.folderUnreadCount = {};
        this.folderIds = {};
        this.deleted = null;
      };
      var $FeedResource = FeedResource;
      ($traceurRuntime.createClass)(FeedResource, {
        receive: function(data) {
          $traceurRuntime.superCall(this, $FeedResource.prototype, "receive", [data]);
          this.updateUnreadCache();
          this.updateFolderCache();
        },
        updateUnreadCache: function() {
          var $__56,
              $__57,
              $__58,
              $__59,
              $__60;
          var value$__15;
          this.unreadCount = 0;
          this.folderUnreadCount = {};
          for (var $__3 = this.values[$traceurRuntime.toProperty(Symbol.iterator)](),
              $__4; !($__4 = $__3.next()).done; ) {
            value$__15 = $__4.value;
            {
              if (value$__15.unreadCount) {
                this.unreadCount += value$__15.unreadCount;
              }
              if (value$__15.folderId !== undefined) {
                $traceurRuntime.setProperty(this.folderUnreadCount, value$__15.folderId, this.folderUnreadCount[$traceurRuntime.toProperty(value$__15.folderId)] || 0);
                ($__56 = this.folderUnreadCount, $__57 = value$__15.folderId, $__58 = value$__15.unreadCount, $__59 = $__56[$traceurRuntime.toProperty($__57)], $__60 = $__59 + $__58, $traceurRuntime.setProperty($__56, $__57, $__60), $__60);
              }
            }
          }
        },
        updateFolderCache: function() {
          var feed$__16;
          this.folderIds = {};
          for (var $__3 = this.values[$traceurRuntime.toProperty(Symbol.iterator)](),
              $__4; !($__4 = $__3.next()).done; ) {
            feed$__16 = $__4.value;
            {
              $traceurRuntime.setProperty(this.folderIds, feed$__16.folderId, this.folderIds[$traceurRuntime.toProperty(feed$__16.folderId)] || []);
              this.folderIds[$traceurRuntime.toProperty(feed$__16.folderId)].push(feed$__16);
            }
          }
        },
        add: function(value) {
          $traceurRuntime.superCall(this, $FeedResource.prototype, "add", [value]);
          if (value.id !== undefined) {
            $traceurRuntime.setProperty(this.ids, value.id, this.hashMap[$traceurRuntime.toProperty(value.url)]);
          }
        },
        delete: function(url) {
          var feed = this.get(url);
          this.deleted = feed;
          delete this.ids[$traceurRuntime.toProperty(feed.id)];
          $traceurRuntime.superCall(this, $FeedResource.prototype, "delete", [url]);
          this.updateUnreadCache();
          this.updateFolderCache();
          return this.http.delete((this.BASE_URL + "/feeds/" + feed.id));
        },
        markRead: function() {
          var feed$__17;
          for (var $__3 = this.values[$traceurRuntime.toProperty(Symbol.iterator)](),
              $__4; !($__4 = $__3.next()).done; ) {
            feed$__17 = $__4.value;
            {
              feed$__17.unreadCount = 0;
            }
          }
          this.unreadCount = 0;
          this.folderUnreadCount = {};
        },
        markFeedRead: function(feedId) {
          this.ids[$traceurRuntime.toProperty(feedId)].unreadCount = 0;
          this.updateUnreadCache();
        },
        markFolderRead: function(folderId) {
          var feed$__18;
          for (var $__3 = this.values[$traceurRuntime.toProperty(Symbol.iterator)](),
              $__4; !($__4 = $__3.next()).done; ) {
            feed$__18 = $__4.value;
            {
              if (feed$__18.folderId === folderId) {
                feed$__18.unreadCount = 0;
              }
            }
          }
          this.updateUnreadCache();
        },
        markItemOfFeedRead: function(feedId) {
          this.ids[$traceurRuntime.toProperty(feedId)].unreadCount -= 1;
          this.updateUnreadCache();
        },
        markItemsOfFeedsRead: function(feedIds) {
          var feedId$__19;
          for (var $__3 = feedIds[$traceurRuntime.toProperty(Symbol.iterator)](),
              $__4; !($__4 = $__3.next()).done; ) {
            feedId$__19 = $__4.value;
            {
              this.ids[$traceurRuntime.toProperty(feedId$__19)].unreadCount -= 1;
            }
          }
          this.updateUnreadCache();
        },
        markItemOfFeedUnread: function(feedId) {
          this.ids[$traceurRuntime.toProperty(feedId)].unreadCount += 1;
          this.updateUnreadCache();
        },
        getUnreadCount: function() {
          return this.unreadCount;
        },
        getFolderUnreadCount: function(folderId) {
          return this.folderUnreadCount[$traceurRuntime.toProperty(folderId)];
        },
        getByFolderId: function(folderId) {
          return this.folderIds[$traceurRuntime.toProperty(folderId)] || [];
        },
        getById: function(feedId) {
          return this.ids[$traceurRuntime.toProperty(feedId)];
        },
        rename: function(url, name) {
          var feed = this.get(url);
          feed.title = name;
          return this.http({
            method: 'POST',
            url: (this.BASE_URL + "/feeds/" + feed.id + "/rename"),
            data: {feedTitle: name}
          });
        },
        move: function(url, folderId) {
          var feed = this.get(url);
          feed.folderId = folderId;
          this.updateFolderCache();
          return this.http({
            method: 'POST',
            url: (this.BASE_URL + "/feeds/" + feed.id + "/move"),
            data: {parentFolderId: folderId}
          });
        },
        create: function(url, folderId) {
          var title = arguments[2] !== (void 0) ? arguments[2] : null;
          if (title) {
            title = title.toUpperCase();
          }
          var feed = {
            url: url,
            folderId: folderId,
            title: title,
            faviconLink: '../css/loading.gif'
          };
          if (!this.get(url)) {
            this.add(feed);
          }
          this.updateFolderCache();
          console.log(feed);
        },
        undoDelete: function() {
          if (this.deleted) {
            this.add(this.deleted);
            return this.http.post((this.BASE_URL + "/feeds/" + this.deleted.id + "/restore"));
          }
          this.updateFolderCache();
          this.updateUnreadCache();
        }
      }, {}, Resource);
      return new FeedResource($http, BASE_URL);
    }]));
    app.factory('FolderResource', (["Resource", "$http", "BASE_URL", function(Resource, $http, BASE_URL) {
      'use strict';
      var FolderResource = function FolderResource($http, BASE_URL) {
        $traceurRuntime.superCall(this, $FolderResource.prototype, "constructor", [$http, BASE_URL, 'name']);
        this.deleted = null;
      };
      var $FolderResource = FolderResource;
      ($traceurRuntime.createClass)(FolderResource, {
        delete: function(folderName) {
          var folder = this.get(folderName);
          this.deleted = folder;
          $traceurRuntime.superCall(this, $FolderResource.prototype, "delete", [folderName]);
          return this.http.delete((this.BASE_URL + "/folders/" + folder.id));
        },
        toggleOpen: function(folderName) {
          var folder = this.get(folderName);
          folder.opened = !folder.opened;
          return this.http({
            url: (this.BASE_URL + "/folders/" + folder.id + "/open"),
            method: 'POST',
            data: {
              folderId: folder.id,
              open: folder.opened
            }
          });
        },
        rename: function(folderName, toFolderName) {
          toFolderName = toFolderName.toUpperCase();
          var folder = this.get(folderName);
          if (!this.get(toFolderName)) {
            folder.name = toFolderName;
            delete this.hashMap[$traceurRuntime.toProperty(folderName)];
            $traceurRuntime.setProperty(this.hashMap, toFolderName, folder);
          }
          return this.http({
            url: (this.BASE_URL + "/folders/" + folder.id + "/rename"),
            method: 'POST',
            data: {folderName: toFolderName}
          });
        },
        create: function(folderName) {
          var folder$__20;
          folderName = folderName.toUpperCase();
          if (!this.get(folderName)) {
            folder$__20 = {name: folderName};
            this.add(folder$__20);
          }
          return this.http({
            url: (this.BASE_URL + "/folders"),
            method: 'POST',
            data: {folderName: folderName}
          });
        },
        undoDelete: function() {
          if (this.deleted) {
            this.add(this.deleted);
            return this.http.post((this.BASE_URL + "/folders/" + this.deleted.id + "/restore"));
          }
        }
      }, {}, Resource);
      return new FolderResource($http, BASE_URL);
    }]));
    app.factory('ItemResource', (["Resource", "$http", "BASE_URL", "ITEM_BATCH_SIZE", function(Resource, $http, BASE_URL, ITEM_BATCH_SIZE) {
      'use strict';
      var ItemResource = function ItemResource($http, BASE_URL, ITEM_BATCH_SIZE) {
        $traceurRuntime.superCall(this, $ItemResource.prototype, "constructor", [$http, BASE_URL]);
        this.starredCount = 0;
        this.batchSize = ITEM_BATCH_SIZE;
      };
      var $ItemResource = ItemResource;
      ($traceurRuntime.createClass)(ItemResource, {
        receive: function(value, channel) {
          switch (channel) {
            case 'newestItemId':
              this.newestItemId = value;
              break;
            case 'starred':
              this.starredCount = value;
              break;
            default:
              $traceurRuntime.superCall(this, $ItemResource.prototype, "receive", [value, channel]);
          }
        },
        getNewestItemId: function() {
          return this.newestItemId;
        },
        getStarredCount: function() {
          return this.starredCount;
        },
        star: function(itemId) {
          var isStarred = arguments[1] !== (void 0) ? arguments[1] : true;
          var it = this.get(itemId);
          var url = (this.BASE_URL + "/items/" + it.feedId + "/" + it.guidHash + "/star");
          it.starred = isStarred;
          if (isStarred) {
            this.starredCount += 1;
          } else {
            this.starredCount -= 1;
          }
          return this.http({
            url: url,
            method: 'POST',
            data: {isStarred: isStarred}
          });
        },
        toggleStar: function(itemId) {
          if (this.get(itemId).starred) {
            this.star(itemId, false);
          } else {
            this.star(itemId, true);
          }
        },
        markItemRead: function(itemId) {
          var isRead = arguments[1] !== (void 0) ? arguments[1] : true;
          this.get(itemId).unread = !isRead;
          return this.http({
            url: (this.BASE_URL + "/items/" + itemId + "/read"),
            method: 'POST',
            data: {isRead: isRead}
          });
        },
        markItemsRead: function(itemIds) {
          var itemId$__21;
          for (var $__3 = itemIds[$traceurRuntime.toProperty(Symbol.iterator)](),
              $__4; !($__4 = $__3.next()).done; ) {
            itemId$__21 = $__4.value;
            {
              this.get(itemId$__21).unread = false;
            }
          }
          return this.http({
            url: (this.BASE_URL + "/items/read/multiple"),
            method: 'POST',
            data: {itemIds: itemIds}
          });
        },
        markFeedRead: function(feedId) {
          var item$__22;
          var read = arguments[1] !== (void 0) ? arguments[1] : true;
          for (var $__3 = this.values.filter((function(i) {
            return i.feedId === feedId;
          }))[$traceurRuntime.toProperty(Symbol.iterator)](),
              $__4; !($__4 = $__3.next()).done; ) {
            item$__22 = $__4.value;
            {
              item$__22.unread = !read;
            }
          }
          return this.http.post((this.BASE_URL + "/feeds/" + feedId + "/read"));
        },
        markRead: function() {
          var item$__23;
          for (var $__3 = this.values[$traceurRuntime.toProperty(Symbol.iterator)](),
              $__4; !($__4 = $__3.next()).done; ) {
            item$__23 = $__4.value;
            {
              item$__23.unread = false;
            }
          }
          return this.http.post((this.BASE_URL + "/items/read"));
        },
        autoPage: function(type, id) {
          return this.http({
            url: (this.BASE_URL + "/items"),
            method: 'GET',
            params: {
              type: type,
              id: id,
              offset: this.size(),
              limit: this.batchSize
            }
          });
        }
      }, {}, Resource);
      return new ItemResource($http, BASE_URL, ITEM_BATCH_SIZE);
    }]));
    app.service('Loading', function() {
      'use strict';
      var $__0 = this;
      this.loading = {
        global: false,
        content: false,
        autopaging: false
      };
      this.setLoading = (function(area, isLoading) {
        $traceurRuntime.setProperty($__0.loading, area, isLoading);
      });
      this.isLoading = (function(area) {
        return $__0.loading[$traceurRuntime.toProperty(area)];
      });
    });
    app.service('Publisher', function() {
      'use strict';
      var $__0 = this;
      this.channels = {};
      this.subscribe = (function(obj) {
        return {toChannels: (function() {
            var channel$__24;
            for (var channels = [],
                $__7 = 0; $__7 < arguments.length; $__7++)
              $traceurRuntime.setProperty(channels, $__7, arguments[$traceurRuntime.toProperty($__7)]);
            for (var $__3 = channels[$traceurRuntime.toProperty(Symbol.iterator)](),
                $__4; !($__4 = $__3.next()).done; ) {
              channel$__24 = $__4.value;
              {
                $traceurRuntime.setProperty($__0.channels, channel$__24, $__0.channels[$traceurRuntime.toProperty(channel$__24)] || []);
                $__0.channels[$traceurRuntime.toProperty(channel$__24)].push(obj);
              }
            }
          })};
      });
      this.publishAll = (function(data) {
        var $__8$__25,
            channel$__26,
            messages$__27;
        var listener$__28;
        for (var $__5 = items(data)[$traceurRuntime.toProperty(Symbol.iterator)](),
            $__6; !($__6 = $__5.next()).done; ) {
          $__8$__25 = $__6.value;
          channel$__26 = $__8$__25[0];
          messages$__27 = $__8$__25[1];
          {
            if ($__0.channels[$traceurRuntime.toProperty(channel$__26)] !== undefined) {
              for (var $__3 = $__0.channels[$traceurRuntime.toProperty(channel$__26)][$traceurRuntime.toProperty(Symbol.iterator)](),
                  $__4; !($__4 = $__3.next()).done; ) {
                listener$__28 = $__4.value;
                {
                  listener$__28.receive(messages$__27, channel$__26);
                }
              }
            }
          }
        }
      });
    });
    app.factory('Resource', (function() {
      'use strict';
      var Resource = function Resource(http, BASE_URL) {
        var id = arguments[2] !== (void 0) ? arguments[2] : 'id';
        this.id = id;
        this.values = [];
        this.hashMap = {};
        this.http = http;
        this.BASE_URL = BASE_URL;
      };
      ($traceurRuntime.createClass)(Resource, {
        receive: function(objs) {
          var obj$__29;
          for (var $__3 = objs[$traceurRuntime.toProperty(Symbol.iterator)](),
              $__4; !($__4 = $__3.next()).done; ) {
            obj$__29 = $__4.value;
            {
              this.add(obj$__29);
            }
          }
        },
        add: function(obj) {
          var $__8$__30,
              key$__31,
              value$__32;
          var existing = this.hashMap[$traceurRuntime.toProperty(obj[$traceurRuntime.toProperty(this.id)])];
          if (existing === undefined) {
            this.values.push(obj);
            $traceurRuntime.setProperty(this.hashMap, obj[$traceurRuntime.toProperty(this.id)], obj);
          } else {
            for (var $__3 = items(obj)[$traceurRuntime.toProperty(Symbol.iterator)](),
                $__4; !($__4 = $__3.next()).done; ) {
              $__8$__30 = $__4.value;
              key$__31 = $__8$__30[0];
              value$__32 = $__8$__30[1];
              {
                $traceurRuntime.setProperty(existing, key$__31, value$__32);
              }
            }
          }
        },
        size: function() {
          return this.values.length;
        },
        get: function(id) {
          return this.hashMap[$traceurRuntime.toProperty(id)];
        },
        delete: function(id) {
          var $__0 = this;
          var deleteAtIndex = this.values.findIndex((function(e) {
            return e[$traceurRuntime.toProperty($__0.id)] === id;
          }));
          if (deleteAtIndex !== undefined) {
            this.values.splice(deleteAtIndex, 1);
          }
          if (this.hashMap[$traceurRuntime.toProperty(id)] !== undefined) {
            delete this.hashMap[$traceurRuntime.toProperty(id)];
          }
        },
        clear: function() {
          this.hashMap = {};
          while (this.values.length > 0) {
            this.values.pop();
          }
        },
        getAll: function() {
          return this.values;
        }
      }, {});
      return Resource;
    }));
    app.service('SettingsResource', ["$http", "BASE_URL", function($http, BASE_URL) {
      'use strict';
      var $__0 = this;
      this.settings = {
        language: 'en',
        showAll: false,
        compact: false,
        oldestFirst: false
      };
      this.defaultLanguageCode = 'en';
      this.supportedLanguageCodes = ['ar-ma', 'ar', 'bg', 'ca', 'cs', 'cv', 'da', 'de', 'el', 'en-ca', 'en-gb', 'eo', 'es', 'et', 'eu', 'fi', 'fr-ca', 'fr', 'gl', 'he', 'hi', 'hu', 'id', 'is', 'it', 'ja', 'ka', 'ko', 'lv', 'ms-my', 'nb', 'ne', 'nl', 'pl', 'pt-br', 'pt', 'ro', 'ru', 'sk', 'sl', 'sv', 'th', 'tr', 'tzm-la', 'tzm', 'uk', 'zh-cn', 'zh-tw'];
      this.receive = (function(data) {
        var $__8$__33,
            key$__34,
            value$__35;
        for (var $__3 = items(data)[$traceurRuntime.toProperty(Symbol.iterator)](),
            $__4; !($__4 = $__3.next()).done; ) {
          $__8$__33 = $__4.value;
          key$__34 = $__8$__33[0];
          value$__35 = $__8$__33[1];
          {
            if (key$__34 === 'language') {
              value$__35 = $__0.processLanguageCode(value$__35);
            }
            $traceurRuntime.setProperty($__0.settings, key$__34, value$__35);
          }
        }
      });
      this.get = (function(key) {
        return $__0.settings[$traceurRuntime.toProperty(key)];
      });
      this.set = (function(key, value) {
        $traceurRuntime.setProperty($__0.settings, key, value);
        var data = {};
        $traceurRuntime.setProperty(data, key, value);
        return $http({
          url: (BASE_URL + "/settings"),
          method: 'POST',
          data: data
        });
      });
      this.processLanguageCode = (function(languageCode) {
        languageCode = languageCode.replace('_', '-').toLowerCase();
        if ($__0.supportedLanguageCodes.indexOf(languageCode) < 0) {
          languageCode = languageCode.split('-')[0];
        }
        if ($__0.supportedLanguageCodes.indexOf(languageCode) < 0) {
          languageCode = $__0.defaultLanguageCode;
        }
        return languageCode;
      });
    }]);
    (function(window, document, $) {
      'use strict';
      var scrollArea = $('#app-content');
      var noInputFocused = (function(element) {
        return !(element.is('input') || element.is('select') || element.is('textarea') || element.is('checkbox'));
      });
      var noModifierKey = (function(event) {
        return !(event.shiftKey || event.altKey || event.ctrlKey || event.metaKey);
      });
      var scrollToItem = (function(item, scrollArea) {
        scrollArea.scrollTop(item.offset().top - scrollArea.offset().top + scrollArea.scrollTop());
      });
      var scrollToNextItem = (function(scrollArea) {
        var item$__36;
        var items = scrollArea.find('.item');
        for (var $__3 = items[$traceurRuntime.toProperty(Symbol.iterator)](),
            $__4; !($__4 = $__3.next()).done; ) {
          item$__36 = $__4.value;
          {
            item$__36 = $(item$__36);
            if (item$__36.position().top > 1) {
              scrollToItem(scrollArea, item$__36);
              return;
            }
          }
        }
        scrollArea.scrollTop(scrollArea.prop('scrollHeight'));
      });
      var scrollToPreviousItem = (function(scrollArea) {
        var item$__37;
        var previous$__38;
        var items = scrollArea.find('.item');
        for (var $__3 = items[$traceurRuntime.toProperty(Symbol.iterator)](),
            $__4; !($__4 = $__3.next()).done; ) {
          item$__37 = $__4.value;
          {
            item$__37 = $(item$__37);
            if (item$__37.position().top >= 0) {
              previous$__38 = item$__37.prev();
              if (previous$__38.length > 0) {
                scrollToItem(scrollArea, previous$__38);
              }
              return;
            }
          }
        }
        if (items.length > 0) {
          scrollToItem(scrollArea, items.last());
        }
      });
      var getActiveItem = (function(scrollArea) {
        var item$__39;
        var items = scrollArea.find('.item');
        for (var $__3 = items[$traceurRuntime.toProperty(Symbol.iterator)](),
            $__4; !($__4 = $__3.next()).done; ) {
          item$__39 = $__4.value;
          {
            item$__39 = $(item$__39);
            if ((item$__39.height() + item$__39.position().top) > 30) {
              return item$__39;
            }
          }
        }
      });
      var toggleUnread = (function(scrollArea) {
        var item = getActiveItem(scrollArea);
        item.find('.keep_unread').trigger('click');
      });
      var toggleStar = (function(scrollArea) {
        var item = getActiveItem(scrollArea);
        item.find('.item_utils .star').trigger('click');
      });
      var expandItem = (function(scrollArea) {
        var item = getActiveItem(scrollArea);
        item.find('.item_heading a').trigger('click');
      });
      var openLink = (function(scrollArea) {
        var item = getActiveItem(scrollArea).find('.item_title a');
        item.trigger('click');
        window.open(item.attr('href'), '_blank');
      });
      $(document).keyup((function(event) {
        var keyCode = event.keyCode;
        if (noInputFocused($(':focus')) && noModifierKey(event)) {
          if ([74, 78, 34].indexOf(keyCode) >= 0) {
            event.preventDefault();
            scrollToNextItem(scrollArea);
          } else if ([75, 80, 37].indexOf(keyCode) >= 0) {
            event.preventDefault();
            scrollToPreviousItem(scrollArea);
          } else if ([85].indexOf(keyCode) >= 0) {
            event.preventDefault();
            toggleUnread(scrollArea);
          } else if ([69].indexOf(keyCode) >= 0) {
            event.preventDefault();
            expandItem(scrollArea);
          } else if ([73, 83, 76].indexOf(keyCode) >= 0) {
            event.preventDefault();
            toggleStar(scrollArea);
          } else if ([72].indexOf(keyCode) >= 0) {
            event.preventDefault();
            toggleStar(scrollArea);
            scrollToNextItem(scrollArea);
          } else if ([79].indexOf(keyCode) >= 0) {
            event.preventDefault();
            openLink(scrollArea);
          }
        }
      }));
    }(window, document, jQuery));
    var call = Function.prototype.call.bind(Function.prototype.call);
    var hasOwn = Object.prototype.hasOwnProperty;
    window.items = function(obj) {
      'use strict';
      var $__2;
      return ($__2 = {}, Object.defineProperty($__2, Symbol.iterator, {
        value: function() {
          return ($traceurRuntime.initGeneratorFunction(function $__51() {
            var $__52,
                $__53,
                $__54,
                $__55,
                x$__40;
            return $traceurRuntime.createGeneratorInstance(function($ctx) {
              while (true)
                switch ($ctx.state) {
                  case 0:
                    $__52 = [];
                    $__53 = obj;
                    for ($__54 in $__53)
                      $__52.push($__54);
                    $ctx.state = 15;
                    break;
                  case 15:
                    $__55 = 0;
                    $ctx.state = 13;
                    break;
                  case 13:
                    $ctx.state = ($__55 < $__52.length) ? 9 : -2;
                    break;
                  case 4:
                    $__55++;
                    $ctx.state = 13;
                    break;
                  case 9:
                    x$__40 = $__52[$traceurRuntime.toProperty($__55)];
                    $ctx.state = 10;
                    break;
                  case 10:
                    $ctx.state = (!($traceurRuntime.toProperty(x$__40) in $__53)) ? 4 : 7;
                    break;
                  case 7:
                    $ctx.state = (call(hasOwn, obj, x$__40)) ? 1 : 4;
                    break;
                  case 1:
                    $ctx.state = 2;
                    return [x$__40, obj[$traceurRuntime.toProperty(x$__40)]];
                  case 2:
                    $ctx.maybeThrow();
                    $ctx.state = 4;
                    break;
                  default:
                    return $ctx.end();
                }
            }, $__51, this);
          }))();
        },
        configurable: true,
        enumerable: true,
        writable: true
      }), $__2);
    };
    window.enumerate = function(list) {
      'use strict';
      var $__2;
      return ($__2 = {}, Object.defineProperty($__2, Symbol.iterator, {
        value: function() {
          return ($traceurRuntime.initGeneratorFunction(function $__51() {
            var counter$__41;
            return $traceurRuntime.createGeneratorInstance(function($ctx) {
              while (true)
                switch ($ctx.state) {
                  case 0:
                    counter$__41 = 0;
                    $ctx.state = 7;
                    break;
                  case 7:
                    $ctx.state = (counter$__41 < list.length) ? 1 : -2;
                    break;
                  case 4:
                    counter$__41 += 1;
                    $ctx.state = 7;
                    break;
                  case 1:
                    $ctx.state = 2;
                    return [counter$__41, list[$traceurRuntime.toProperty(counter$__41)]];
                  case 2:
                    $ctx.maybeThrow();
                    $ctx.state = 4;
                    break;
                  default:
                    return $ctx.end();
                }
            }, $__51, this);
          }))();
        },
        configurable: true,
        enumerable: true,
        writable: true
      }), $__2);
    };
    window.reverse = function(list) {
      'use strict';
      var $__2;
      return ($__2 = {}, Object.defineProperty($__2, Symbol.iterator, {
        value: function() {
          return ($traceurRuntime.initGeneratorFunction(function $__51() {
            var counter$__42;
            return $traceurRuntime.createGeneratorInstance(function($ctx) {
              while (true)
                switch ($ctx.state) {
                  case 0:
                    counter$__42 = list.length;
                    $ctx.state = 7;
                    break;
                  case 7:
                    $ctx.state = (counter$__42 >= 0) ? 1 : -2;
                    break;
                  case 4:
                    counter$__42 -= 1;
                    $ctx.state = 7;
                    break;
                  case 1:
                    $ctx.state = 2;
                    return list[$traceurRuntime.toProperty(counter$__42)];
                  case 2:
                    $ctx.maybeThrow();
                    $ctx.state = 4;
                    break;
                  default:
                    return $ctx.end();
                }
            }, $__51, this);
          }))();
        },
        configurable: true,
        enumerable: true,
        writable: true
      }), $__2);
    };
    app.run((["$document", "$rootScope", function($document, $rootScope) {
      'use strict';
      $document.click((function(event) {
        $rootScope.$broadcast('documentClicked', event);
      }));
    }]));
    app.directive('appNavigationEntryUtils', (function() {
      'use strict';
      return {
        restrict: 'C',
        link: (function(scope, elm) {
          var menu = elm.siblings('.app-navigation-entry-menu');
          var button = $(elm).find('.app-navigation-entry-utils-menu-button button');
          button.click((function() {
            menu.toggleClass('open');
          }));
          scope.$on('documentClicked', (function(scope, event) {
            if (event.target !== button[0]) {
              menu.removeClass('open');
            }
          }));
        })
      };
    }));
    app.directive('newsAudio', (function() {
      'use strict';
      return {
        restrict: 'E',
        scope: {
          src: '@',
          type: '@'
        },
        transclude: true,
        template: '' + '<audio controls="controls" preload="none" ng-hide="cantPlay()">' + '<source ng-src="{{ src|trustUrl }}">' + '</audio>' + '<a ng-href="{{ src|trustUrl }}" class="button" ng-show="cantPlay()" ' + 'ng-transclude></a>',
        link: (function(scope, elm) {
          var source = elm.children().children('source')[0];
          var cantPlay = false;
          source.addEventListener('error', (function() {
            scope.$apply((function() {
              cantPlay = true;
            }));
          }));
          scope.cantPlay = (function() {
            return cantPlay;
          });
        })
      };
    }));
    app.directive('newsAutoFocus', (function() {
      'use strict';
      return (function(scope, elem, attrs) {
        if (attrs.newsAutofocus) {
          $(attrs.newsAutofocus).focus();
        } else {
          elem.focus();
        }
      });
    }));
    app.directive('newsBindHtmlUnsafe', (function() {
      'use strict';
      return (function(scope, elem, attr) {
        scope.$watch(attr.newsBindHtmlUnsafe, (function() {
          elem.html(scope.$eval(attr.newsBindHtmlUnsafe));
        }));
      });
    }));
    app.directive('newsDraggable', (function() {
      'use strict';
      return (function(scope, elem, attr) {
        var options = scope.$eval(attr.newsDraggable);
        if (angular.isDefined(options)) {
          elem.draggable(options);
        } else {
          elem.draggable();
        }
      });
    }));
    app.directive('newsDroppable', (["$rootScope", function($rootScope) {
      'use strict';
      return (function(scope, elem, attr) {
        var details = {
          accept: '.feed',
          hoverClass: 'drag-and-drop',
          greedy: true,
          drop: (function(event, ui) {
            $('.drag-and-drop').removeClass('drag-and-drop');
            var data = {
              folderId: parseInt(elem.data('id'), 10),
              feedId: parseInt($(ui.draggable).data('id'), 10)
            };
            $rootScope.$broadcast('moveFeedToFolder', data);
            scope.$apply(attr.droppable);
          })
        };
        elem.droppable(details);
      });
    }]));
    app.directive('newsFocus', (["$timeout", "$interpolate", function($timeout, $interpolate) {
      'use strict';
      return (function(scope, elem, attrs) {
        elem.click((function() {
          var toReadd = $($interpolate(attrs.newsFocus)(scope));
          $timeout((function() {
            toReadd.focus();
          }), 500);
        }));
      });
    }]));
    app.directive('newsReadFile', (function() {
      'use strict';
      return (function(scope, elem, attr) {
        elem.change((function() {
          var file = elem[0].files[0];
          var reader = new FileReader();
          reader.onload = (function(event) {
            elem[0].value = 0;
            scope.$fileContent = event.target.result;
            scope.$apply(attr.newsReadFile);
          });
          reader.readAsText(file);
        }));
      });
    }));
    app.directive('newsScroll', (["$timeout", function($timeout) {
      'use strict';
      var autoPage = (function(enabled, limit, elem, scope) {
        var counter$__43;
        var articles$__44;
        var item$__46;
        if (enabled) {
          counter$__43 = 0;
          articles$__44 = elem.find('.item');
          for (var i$__45 = articles$__44.length - 1; i$__45 >= 0; i$__45 -= 1) {
            item$__46 = $(articles$__44[$traceurRuntime.toProperty(i$__45)]);
            if (counter$__43 >= limit) {
              break;
            }
            if (item$__46.position().top < 0) {
              scope.$apply(scope.newsScrollAutoPage);
              break;
            }
            counter$__43 += 1;
          }
        }
      });
      var markRead = (function(enabled, elem, scope) {
        var ids$__47;
        var articles$__48;
        var item$__50;
        if (enabled) {
          ids$__47 = [];
          articles$__48 = elem.find('.item:not(.read)');
          for (var i$__49 = 0; i$__49 < articles$__48.length; i$__49 += 1) {
            item$__50 = $(articles$__48[$traceurRuntime.toProperty(i$__49)]);
            if (item$__50.position().top <= -50) {
              ids$__47.push(parseInt(item$__50.data('id'), 10));
            } else {
              break;
            }
          }
          scope.itemIds = ids$__47;
          scope.$apply(scope.newsScrollMarkRead);
        }
      });
      return {
        restrict: 'A',
        scope: {
          'newsScrollAutoPage': '&',
          'newsScrollMarkRead': '&',
          'newsScrollEnabledMarkRead': '=',
          'newsScrollEnabledAutoPage': '=',
          'newsScrollMarkReadTimeout': '@',
          'newsScrollTimeout': '@',
          'newsScrollAutoPageWhenLeft': '@'
        },
        link: (function(scope, elem) {
          var allowScroll = true;
          var scrollTimeout = scope.newsScrollTimeout || 1;
          var markReadTimeout = scope.newsScrollMarkReadTimeout || 1;
          var autoPageLimit = scope.newsScrollAutoPageWhenLeft || 50;
          var scrollHandler = (function() {
            if (allowScroll) {
              allowScroll = false;
              $timeout((function() {
                allowScroll = true;
              }), scrollTimeout * 1000);
              autoPage(scope.newsScrollEnabledAutoPage, autoPageLimit, elem, scope);
              $timeout((function() {
                markRead(scope.newsScrollEnabledMarkRead, elem, scope);
              }), markReadTimeout * 1000);
            }
          });
          elem.on('scroll', scrollHandler);
          scope.$on('$destroy', (function() {
            elem.off('scroll', scrollHandler);
          }));
        })
      };
    }]));
    app.directive('newsTitleUnreadCount', (["$window", function($window) {
      'use strict';
      var baseTitle = $window.document.title;
      return {
        restrict: 'E',
        scope: {unreadCount: '@'},
        link: (function(scope, elem, attrs) {
          attrs.$observe('unreadCount', (function(value) {
            var titles = baseTitle.split('-');
            if (value !== '0') {
              $window.document.title = titles[0] + '(' + value + ') - ' + titles[1];
            }
          }));
        })
      };
    }]));
    app.directive('newsTriggerClick', (function() {
      'use strict';
      return (function(scope, elm, attr) {
        elm.click((function() {
          $(attr.newsTriggerClick).trigger('click');
        }));
      });
    }));
  })(window, document, angular, jQuery, OC, oc_requesttoken);
  return {};
})();
