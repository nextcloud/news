var $__build_47_app__ = function () {
    'use strict';
    var __moduleName = 'build/app';
    (function (window, document, angular, $, OC, csrfToken, undefined) {
      'use strict';
      var app = angular.module('News', [
          'ngRoute',
          'ngSanitize',
          'ngAnimate'
        ]);
      app.config([
        '$routeProvider',
        '$provide',
        '$httpProvider',
        function ($routeProvider, $provide, $httpProvider) {
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
          $provide.factory('CSRFInterceptor', function ($q, BASE_URL) {
            return {
              request: function (config) {
                if (config.url.indexOf(BASE_URL) === 0) {
                  config.headers.requesttoken = csrfToken;
                }
                return config || $q.when(config);
              }
            };
          });
          $httpProvider.interceptors.push('CSRFInterceptor');
          var getResolve = function (type) {
            return {
              data: [
                '$http',
                '$route',
                '$q',
                'BASE_URL',
                'ITEM_BATCH_SIZE',
                function ($http, $route, $q, BASE_URL, ITEM_BATCH_SIZE) {
                  var parameters = {
                      type: type,
                      limit: ITEM_BATCH_SIZE
                    };
                  if ($route.current.params.id !== undefined) {
                    parameters.id = $route.current.params.id;
                  }
                  var deferred = $q.defer();
                  $http({
                    url: BASE_URL + '/items',
                    method: 'GET',
                    params: parameters
                  }).success(function (data) {
                    deferred.resolve(data);
                  });
                  return deferred.promise;
                }
              ]
            };
          };
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
          }).otherwise({ redirectTo: '/items' });
        }
      ]);
      app.run([
        '$rootScope',
        '$location',
        '$http',
        '$q',
        '$interval',
        'Loading',
        'ItemResource',
        'FeedResource',
        'FolderResource',
        'SettingsResource',
        'Publisher',
        'BASE_URL',
        'FEED_TYPE',
        'REFRESH_RATE',
        function ($rootScope, $location, $http, $q, $interval, Loading, ItemResource, FeedResource, FolderResource, SettingsResource, Publisher, BASE_URL, FEED_TYPE, REFRESH_RATE) {
          'use strict';
          Loading.setLoading('global', true);
          Publisher.subscribe(ItemResource).toChannels('items', 'newestItemId', 'starred');
          Publisher.subscribe(FolderResource).toChannels('folders');
          Publisher.subscribe(FeedResource).toChannels('feeds');
          Publisher.subscribe(SettingsResource).toChannels('settings');
          var settingsDeferred = $q.defer();
          $http.get(BASE_URL + '/settings').success(function (data) {
            Publisher.publishAll(data);
            settingsDeferred.resolve();
          });
          var activeFeedDeferred = $q.defer();
          $http.get(BASE_URL + '/feeds/active').success(function (data) {
            var url;
            switch (data.type) {
            case FEED_TYPE.FEED:
              url = '/items/feeds/${data.id}';
              break;
            case FEED_TYPE.FOLDER:
              url = '/items/folders/${data.id}';
              break;
            case FEED_TYPE.STARRED:
              url = '/items/starred';
              break;
            default:
              url = '/items';
            }
            $location.path(url);
            activeFeedDeferred.resolve();
          });
          var folderDeferred = $q.defer();
          $http.get(BASE_URL + '/folders').success(function (data) {
            Publisher.publishAll(data);
            folderDeferred.resolve();
          });
          var feedDeferred = $q.defer();
          $http.get(BASE_URL + '/feeds').success(function (data) {
            Publisher.publishAll(data);
            feedDeferred.resolve();
          });
          $q.all([
            settingsDeferred.promise,
            activeFeedDeferred.promise,
            feedDeferred.promise,
            folderDeferred.promise
          ]).then(function () {
            Loading.setLoading('global', false);
          });
          $interval(function () {
            $http.get(BASE_URL + '/feeds');
            $http.get(BASE_URL + '/folders');
          }, REFRESH_RATE * 1000);
          $rootScope.$on('$routeChangeStart', function () {
            Loading.setLoading('content', true);
          });
          $rootScope.$on('$routeChangeSuccess', function () {
            Loading.setLoading('content', false);
          });
          $rootScope.$on('$routeChangeError', function () {
            $location.path('/items');
          });
        }
      ]);
      app.controller('AppController', [
        'Loading',
        'FeedResource',
        'FolderResource',
        function (Loading, FeedResource, FolderResource) {
          'use strict';
          this.loading = Loading;
          this.isFirstRun = function () {
            return FeedResource.size() === 0 && FolderResource.size() === 0;
          };
        }
      ]);
      app.controller('ContentController', [
        'Publisher',
        'FeedResource',
        'ItemResource',
        'SettingsResource',
        'data',
        '$route',
        '$routeParams',
        function (Publisher, FeedResource, ItemResource, SettingsResource, data, $route, $routeParams) {
          'use strict';
          var $__0 = this;
          this.isAutoPagingEnabled = true;
          ItemResource.clear();
          Publisher.publishAll(data);
          this.getItems = function () {
            return ItemResource.getAll();
          };
          this.toggleStar = function (itemId) {
            ItemResource.toggleStar(itemId);
          };
          this.markRead = function (itemId) {
            var item = ItemResource.get(itemId);
            if (!item.keepUnread) {
              ItemResource.markItemRead(itemId);
              FeedResource.markItemOfFeedRead(item.feedId);
            }
          };
          this.getFeed = function (feedId) {
            return FeedResource.getById(feedId);
          };
          this.toggleKeepUnread = function (itemId) {
            var item = ItemResource.get(itemId);
            if (!item.unread) {
              FeedResource.markItemOfFeedUnread(item.feedId);
              ItemResource.markItemRead(itemId, false);
            }
            item.keepUnread = !item.keepUnread;
          };
          this.orderBy = function () {
            if (SettingsResource.get('oldestFirst')) {
              return '-id';
            } else {
              return 'id';
            }
          };
          this.isCompactView = function () {
            return SettingsResource.get('compact');
          };
          this.autoPagingEnabled = function () {
            return $__0.isAutoPagingEnabled;
          };
          this.markReadEnabled = function () {
            return !SettingsResource.get('preventReadOnScroll');
          };
          this.scrollRead = function (itemIds) {
            var ids = [];
            for (var $__3 = itemIds[$traceurRuntime.toProperty(Symbol.iterator)](), $__4; !($__4 = $__3.next()).done;) {
              try {
                throw undefined;
              } catch (itemId) {
                itemId = $__4.value;
                {
                  try {
                    throw undefined;
                  } catch (item) {
                    item = ItemResource.get(itemId);
                    if (!item.keepUnread) {
                      ids.push(itemId);
                      FeedResource.markItemOfFeedRead(item.feedId);
                    }
                  }
                }
              }
            }
            ItemResource.markItemsRead(ids);
          };
          this.autoPage = function () {
            $__0.isAutoPagingEnabled = false;
            var type = $route.current.$$route.type;
            var id = $routeParams.id;
            ItemResource.autoPage(type, id).success(function (data) {
              Publisher.publishAll(data);
              if (data.items.length > 0) {
                $__0.isAutoPagingEnabled = true;
              }
            }).error(function () {
              $__0.isAutoPagingEnabled = true;
            });
          };
          this.getRelativeDate = function (timestamp) {
            console.log(timestamp);
          };
        }
      ]);
      app.controller('NavigationController', [
        'FeedResource',
        'FolderResource',
        'ItemResource',
        'SettingsResource',
        function (FeedResource, FolderResource, ItemResource, SettingsResource) {
          'use strict';
          this.getFeeds = function () {
            return FeedResource.getAll();
          };
          this.getFolders = function () {
            return FolderResource.getAll();
          };
          this.markFolderRead = function (folderId) {
            FeedResource.markFolderRead(folderId);
            for (var $__3 = FeedResource.getByFolderId(folderId)[$traceurRuntime.toProperty(Symbol.iterator)](), $__4; !($__4 = $__3.next()).done;) {
              try {
                throw undefined;
              } catch (feed) {
                feed = $__4.value;
                {
                  ItemResource.markFeedRead(feed.id);
                }
              }
            }
          };
          this.markFeedRead = function (feedId) {
            ItemResource.markFeedRead(feedId);
            FeedResource.markFeedRead(feedId);
          };
          this.markRead = function () {
            ItemResource.markRead();
            FeedResource.markRead();
          };
          this.isShowAll = function () {
            return SettingsResource.get('showAll');
          };
          this.createFeed = function () {
            console.log('TBD');
          };
          this.createFolder = function () {
            console.log('TBD');
          };
          this.renameFeed = function () {
            console.log('TBD');
          };
          this.renameFolder = function () {
            console.log('TBD');
          };
          this.deleteFeed = function () {
            console.log('TBD');
          };
          this.deleteFolder = function () {
            console.log('TBD');
          };
          this.moveFeed = function () {
            console.log('TBD');
          };
          this.isActive = function () {
            console.log('TBD');
          };
          this.isVisible = function () {
            console.log('TBD');
          };
        }
      ]);
      app.controller('SettingsController', [
        '$route',
        'SettingsResource',
        'FeedResource',
        function ($route, SettingsResource, FeedResource) {
          'use strict';
          var $__0 = this;
          this.importing = false;
          this.opmlImportError = false;
          this.articleImportError = false;
          var set = function (key, value) {
            SettingsResource.set(key, value);
            if ([
                'showAll',
                'oldestFirst'
              ].indexOf(key) >= 0) {
              $route.reload();
            }
          };
          this.toggleSetting = function (key) {
            set(key, !$__0.getSetting(key));
          };
          this.getSetting = function (key) {
            return SettingsResource.get(key);
          };
          this.feedSize = function () {
            return FeedResource.size();
          };
          this.importOpml = function (content) {
            console.log(content);
          };
          this.importArticles = function (content) {
            console.log(content);
          };
        }
      ]);
      app.factory('FeedResource', [
        'Resource',
        '$http',
        'BASE_URL',
        function (Resource, $http, BASE_URL) {
          'use strict';
          var FeedResource = function FeedResource($http, BASE_URL) {
            $traceurRuntime.superCall(this, $FeedResource.prototype, 'constructor', [
              $http,
              BASE_URL,
              'url'
            ]);
            this.ids = {};
            this.unreadCount = 0;
            this.folderUnreadCount = {};
          };
          var $FeedResource = FeedResource;
          $traceurRuntime.createClass(FeedResource, {
            receive: function (data) {
              $traceurRuntime.superCall(this, $FeedResource.prototype, 'receive', [data]);
              this.updateUnreadCache();
            },
            updateUnreadCache: function () {
              var $__14, $__15, $__16, $__17, $__18;
              this.unreadCount = 0;
              this.folderUnreadCount = {};
              for (var $__3 = this.values[$traceurRuntime.toProperty(Symbol.iterator)](), $__4; !($__4 = $__3.next()).done;) {
                try {
                  throw undefined;
                } catch (value) {
                  value = $__4.value;
                  {
                    if (value.unreadCount) {
                      this.unreadCount += value.unreadCount;
                    }
                    if (value.folderId !== undefined) {
                      $traceurRuntime.setProperty(this.folderUnreadCount, value.folderId, this.folderUnreadCount[$traceurRuntime.toProperty(value.folderId)] || 0);
                      $__14 = this.folderUnreadCount, $__15 = value.folderId, $__16 = value.unreadCount, $__17 = $__14[$traceurRuntime.toProperty($__15)], $__18 = $__17 + $__16, $traceurRuntime.setProperty($__14, $__15, $__18), $__18;
                    }
                  }
                }
              }
            },
            add: function (value) {
              $traceurRuntime.superCall(this, $FeedResource.prototype, 'add', [value]);
              if (value.id !== undefined) {
                $traceurRuntime.setProperty(this.ids, value.id, this.hashMap[$traceurRuntime.toProperty(value.url)]);
              }
            },
            delete: function (id) {
              var feed = this.get(id);
              delete this.ids[$traceurRuntime.toProperty(feed.id)];
              $traceurRuntime.superCall(this, $FeedResource.prototype, 'delete', [id]);
            },
            markRead: function () {
              for (var $__3 = this.values[$traceurRuntime.toProperty(Symbol.iterator)](), $__4; !($__4 = $__3.next()).done;) {
                try {
                  throw undefined;
                } catch (feed) {
                  feed = $__4.value;
                  {
                    feed.unreadCount = 0;
                  }
                }
              }
              this.unreadCount = 0;
              this.folderUnreadCount = {};
            },
            markFeedRead: function (feedId) {
              this.ids[$traceurRuntime.toProperty(feedId)].unreadCount = 0;
              this.updateUnreadCache();
            },
            markFolderRead: function (folderId) {
              for (var $__3 = this.values[$traceurRuntime.toProperty(Symbol.iterator)](), $__4; !($__4 = $__3.next()).done;) {
                try {
                  throw undefined;
                } catch (feed) {
                  feed = $__4.value;
                  {
                    if (feed.folderId === folderId) {
                      feed.unreadCount = 0;
                    }
                  }
                }
              }
              this.updateUnreadCache();
            },
            markItemOfFeedRead: function (feedId) {
              this.ids[$traceurRuntime.toProperty(feedId)].unreadCount -= 1;
              this.updateUnreadCache();
            },
            markItemOfFeedUnread: function (feedId) {
              this.ids[$traceurRuntime.toProperty(feedId)].unreadCount += 1;
              this.updateUnreadCache();
            },
            getUnreadCount: function () {
              return this.unreadCount;
            },
            getFolderUnreadCount: function (folderId) {
              return this.folderUnreadCount[$traceurRuntime.toProperty(folderId)] || 0;
            },
            getByFolderId: function (folderId) {
              return this.values.filter(function (v) {
                return v.folderId === folderId;
              });
            },
            getById: function (feedId) {
              return this.ids[$traceurRuntime.toProperty(feedId)];
            }
          }, {}, Resource);
          return new FeedResource($http, BASE_URL);
        }
      ]);
      app.factory('FolderResource', [
        'Resource',
        '$http',
        'BASE_URL',
        function (Resource, $http, BASE_URL) {
          'use strict';
          var FolderResource = function FolderResource($http, BASE_URL) {
            $traceurRuntime.superCall(this, $FolderResource.prototype, 'constructor', [
              $http,
              BASE_URL,
              'name'
            ]);
          };
          var $FolderResource = FolderResource;
          $traceurRuntime.createClass(FolderResource, {}, {}, Resource);
          return new FolderResource($http, BASE_URL);
        }
      ]);
      app.factory('ItemResource', [
        'Resource',
        '$http',
        'BASE_URL',
        'ITEM_BATCH_SIZE',
        function (Resource, $http, BASE_URL, ITEM_BATCH_SIZE) {
          'use strict';
          var ItemResource = function ItemResource($http, BASE_URL, ITEM_BATCH_SIZE) {
            $traceurRuntime.superCall(this, $ItemResource.prototype, 'constructor', [
              $http,
              BASE_URL
            ]);
            this.starredCount = 0;
            this.batchSize = ITEM_BATCH_SIZE;
          };
          var $ItemResource = ItemResource;
          $traceurRuntime.createClass(ItemResource, {
            receive: function (value, channel) {
              switch (channel) {
              case 'newestItemId':
                this.newestItemId = value;
                break;
              case 'starred':
                this.starredCount = value;
                break;
              default:
                $traceurRuntime.superCall(this, $ItemResource.prototype, 'receive', [
                  value,
                  channel
                ]);
              }
            },
            getNewestItemId: function () {
              return this.newestItemId;
            },
            getStarredCount: function () {
              return this.starredCount;
            },
            star: function (itemId) {
              var isStarred = arguments[1] !== void 0 ? arguments[1] : true;
              var it = this.get(itemId);
              var url = this.BASE_URL + '/items/' + it.feedId + '/' + it.guidHash + '/star';
              it.starred = isStarred;
              if (isStarred) {
                this.starredCount += 1;
              } else {
                this.starredCount -= 1;
              }
              return this.http({
                url: url,
                method: 'POST',
                data: { isStarred: isStarred }
              });
            },
            toggleStar: function (itemId) {
              if (this.get(itemId).starred) {
                this.star(itemId, false);
              } else {
                this.star(itemId, true);
              }
            },
            markItemRead: function (itemId) {
              var isRead = arguments[1] !== void 0 ? arguments[1] : true;
              this.get(itemId).unread = !isRead;
              return this.http({
                url: this.BASE_URL + '/items/' + itemId + '/read',
                method: 'POST',
                data: { isRead: isRead }
              });
            },
            markItemsRead: function (itemIds) {
              for (var $__3 = itemIds[$traceurRuntime.toProperty(Symbol.iterator)](), $__4; !($__4 = $__3.next()).done;) {
                try {
                  throw undefined;
                } catch (itemId) {
                  itemId = $__4.value;
                  {
                    this.get(itemId).unread = false;
                  }
                }
              }
              return this.http({
                url: this.BASE_URL + '/items/read/multiple',
                method: 'POST',
                data: { itemIds: itemIds }
              });
            },
            markFeedRead: function (feedId) {
              var read = arguments[1] !== void 0 ? arguments[1] : true;
              for (var $__3 = this.values.filter(function (i) {
                    return i.feedId === feedId;
                  })[$traceurRuntime.toProperty(Symbol.iterator)](), $__4; !($__4 = $__3.next()).done;) {
                try {
                  throw undefined;
                } catch (item) {
                  item = $__4.value;
                  {
                    item.unread = !read;
                  }
                }
              }
              return this.http.post(this.BASE_URL + '/feeds/' + feedId + '/read');
            },
            markRead: function () {
              for (var $__3 = this.values[$traceurRuntime.toProperty(Symbol.iterator)](), $__4; !($__4 = $__3.next()).done;) {
                try {
                  throw undefined;
                } catch (item) {
                  item = $__4.value;
                  {
                    item.unread = false;
                  }
                }
              }
              return this.http.post(this.BASE_URL + '/items/read');
            },
            autoPage: function (type, id) {
              return this.http({
                url: this.BASE_URL + '/items',
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
        }
      ]);
      app.service('Loading', function () {
        'use strict';
        var $__0 = this;
        this.loading = {
          global: false,
          content: false,
          autopaging: false
        };
        this.setLoading = function (area, isLoading) {
          $traceurRuntime.setProperty($__0.loading, area, isLoading);
        };
        this.isLoading = function (area) {
          return $__0.loading[$traceurRuntime.toProperty(area)];
        };
      });
      app.service('Publisher', function () {
        'use strict';
        var $__0 = this;
        this.channels = {};
        this.subscribe = function (obj) {
          return {
            toChannels: function () {
              for (var channels = [], $__7 = 0; $__7 < arguments.length; $__7++)
                $traceurRuntime.setProperty(channels, $__7, arguments[$traceurRuntime.toProperty($__7)]);
              for (var $__3 = channels[$traceurRuntime.toProperty(Symbol.iterator)](), $__4; !($__4 = $__3.next()).done;) {
                try {
                  throw undefined;
                } catch (channel) {
                  channel = $__4.value;
                  {
                    $traceurRuntime.setProperty($__0.channels, channel, $__0.channels[$traceurRuntime.toProperty(channel)] || []);
                    $__0.channels[$traceurRuntime.toProperty(channel)].push(obj);
                  }
                }
              }
            }
          };
        };
        this.publishAll = function (data) {
          for (var $__5 = items(data)[$traceurRuntime.toProperty(Symbol.iterator)](), $__6; !($__6 = $__5.next()).done;) {
            try {
              throw undefined;
            } catch (messages) {
              try {
                throw undefined;
              } catch (channel) {
                try {
                  throw undefined;
                } catch ($__8) {
                  {
                    $__8 = $traceurRuntime.assertObject($__6.value);
                    channel = $__8[0];
                    messages = $__8[1];
                  }
                  {
                    if ($__0.channels[$traceurRuntime.toProperty(channel)] !== undefined) {
                      for (var $__3 = $__0.channels[$traceurRuntime.toProperty(channel)][$traceurRuntime.toProperty(Symbol.iterator)](), $__4; !($__4 = $__3.next()).done;) {
                        try {
                          throw undefined;
                        } catch (listener) {
                          listener = $__4.value;
                          {
                            listener.receive(messages, channel);
                          }
                        }
                      }
                    }
                  }
                }
              }
            }
          }
        };
      });
      app.factory('Resource', function () {
        'use strict';
        var Resource = function Resource(http, BASE_URL) {
          var id = arguments[2] !== void 0 ? arguments[2] : 'id';
          this.id = id;
          this.values = [];
          this.hashMap = {};
          this.http = http;
          this.BASE_URL = BASE_URL;
        };
        $traceurRuntime.createClass(Resource, {
          receive: function (objs) {
            for (var $__3 = objs[$traceurRuntime.toProperty(Symbol.iterator)](), $__4; !($__4 = $__3.next()).done;) {
              try {
                throw undefined;
              } catch (obj) {
                obj = $__4.value;
                {
                  this.add(obj);
                }
              }
            }
          },
          add: function (obj) {
            var existing = this.hashMap[$traceurRuntime.toProperty(obj[$traceurRuntime.toProperty(this.id)])];
            if (existing === undefined) {
              this.values.push(obj);
              $traceurRuntime.setProperty(this.hashMap, obj[$traceurRuntime.toProperty(this.id)], obj);
            } else {
              for (var $__3 = items(obj)[$traceurRuntime.toProperty(Symbol.iterator)](), $__4; !($__4 = $__3.next()).done;) {
                try {
                  throw undefined;
                } catch (value) {
                  try {
                    throw undefined;
                  } catch (key) {
                    try {
                      throw undefined;
                    } catch ($__8) {
                      {
                        $__8 = $traceurRuntime.assertObject($__4.value);
                        key = $__8[0];
                        value = $__8[1];
                      }
                      {
                        $traceurRuntime.setProperty(existing, key, value);
                      }
                    }
                  }
                }
              }
            }
          },
          size: function () {
            return this.values.length;
          },
          get: function (id) {
            return this.hashMap[$traceurRuntime.toProperty(id)];
          },
          delete: function (id) {
            var $__0 = this;
            var deleteAtIndex = this.values.findIndex(function (e) {
                return e[$traceurRuntime.toProperty($__0.id)] === id;
              });
            if (deleteAtIndex !== undefined) {
              this.values.splice(deleteAtIndex, 1);
            }
            if (this.hashMap[$traceurRuntime.toProperty(id)] !== undefined) {
              delete this.hashMap[$traceurRuntime.toProperty(id)];
            }
          },
          clear: function () {
            this.hashMap = {};
            while (this.values.length > 0) {
              this.values.pop();
            }
          },
          getAll: function () {
            return this.values;
          }
        }, {});
        return Resource;
      });
      app.service('SettingsResource', [
        '$http',
        'BASE_URL',
        function ($http, BASE_URL) {
          'use strict';
          var $__0 = this;
          this.settings = {};
          this.receive = function (data) {
            for (var $__3 = items(data)[$traceurRuntime.toProperty(Symbol.iterator)](), $__4; !($__4 = $__3.next()).done;) {
              try {
                throw undefined;
              } catch (value) {
                try {
                  throw undefined;
                } catch (key) {
                  try {
                    throw undefined;
                  } catch ($__8) {
                    {
                      $__8 = $traceurRuntime.assertObject($__4.value);
                      key = $__8[0];
                      value = $__8[1];
                    }
                    {
                      $traceurRuntime.setProperty($__0.settings, key, value);
                    }
                  }
                }
              }
            }
          };
          this.get = function (key) {
            return $__0.settings[$traceurRuntime.toProperty(key)];
          };
          this.set = function (key, value) {
            $traceurRuntime.setProperty($__0.settings, key, value);
            var data = {};
            $traceurRuntime.setProperty(data, key, value);
            return $http({
              url: BASE_URL + '/settings',
              method: 'POST',
              data: data
            });
          };
        }
      ]);
      (function (window, document, $) {
        'use strict';
        var scrollArea = $('#app-content');
        var noInputFocused = function (element) {
          return !(element.is('input') && element.is('select') && element.is('textarea') && element.is('checkbox'));
        };
        var noModifierKey = function (event) {
          return !(event.shiftKey || event.altKey || event.ctrlKey || event.metaKey);
        };
        var scrollToItem = function (item, scrollArea) {
          scrollArea.scrollTop(item.offset().top - scrollArea.offset().top + scrollArea.scrollTop());
        };
        var scrollToNextItem = function (scrollArea) {
          var items = scrollArea.find('.feed_item');
          for (var $__3 = items[$traceurRuntime.toProperty(Symbol.iterator)](), $__4; !($__4 = $__3.next()).done;) {
            try {
              throw undefined;
            } catch (item) {
              item = $__4.value;
              {
                item = $(item);
                if (item.position().top > 1) {
                  scrollToItem(scrollArea, item);
                  return;
                }
              }
            }
          }
          scrollArea.scrollTop(scrollArea.prop('scrollHeight'));
        };
        var scrollToPreviousItem = function (scrollArea) {
          var items = scrollArea.find('.feed_item');
          for (var $__3 = items[$traceurRuntime.toProperty(Symbol.iterator)](), $__4; !($__4 = $__3.next()).done;) {
            try {
              throw undefined;
            } catch (item) {
              item = $__4.value;
              {
                item = $(item);
                if (item.position().top >= 0) {
                  try {
                    throw undefined;
                  } catch (previous) {
                    previous = item.prev();
                    if (previous.length > 0) {
                      scrollToItem(scrollArea, previous);
                    }
                    return;
                  }
                }
              }
            }
          }
          if (items.length > 0) {
            scrollToItem(scrollArea, items.last());
          }
        };
        var getActiveItem = function (scrollArea) {
          var items = scrollArea.find('.feed_item');
          for (var $__3 = items[$traceurRuntime.toProperty(Symbol.iterator)](), $__4; !($__4 = $__3.next()).done;) {
            try {
              throw undefined;
            } catch (item) {
              item = $__4.value;
              {
                item = $(item);
                if (item.height() + item.position().top > 30) {
                  return item;
                }
              }
            }
          }
        };
        var toggleUnread = function (scrollArea) {
          var item = getActiveItem(scrollArea);
          item.find('.keep_unread').trigger('click');
        };
        var toggleStar = function (scrollArea) {
          var item = getActiveItem(scrollArea);
          item.find('.item_utils .star').trigger('click');
        };
        var expandItem = function (scrollArea) {
          var item = getActiveItem(scrollArea);
          item.find('.item_heading a').trigger('click');
        };
        var openLink = function (scrollArea) {
          var item = getActiveItem(scrollArea).find('.item_title a');
          item.trigger('click');
          window.open(item.attr('href'), '_blank');
        };
        $(document).keyup(function (event) {
          var keyCode = event.keyCode;
          if (noInputFocused($(':focus')) && noModifierKey(event)) {
            if ([
                74,
                78,
                34
              ].indexOf(keyCode) >= 0) {
              event.preventDefault();
              scrollToNextItem(scrollArea);
            } else if ([
                75,
                80,
                37
              ].indexOf(keyCode) >= 0) {
              event.preventDefault();
              scrollToPreviousItem(scrollArea);
            } else if ([85].indexOf(keyCode) >= 0) {
              event.preventDefault();
              toggleUnread(scrollArea);
            } else if ([69].indexOf(keyCode) >= 0) {
              event.preventDefault();
              expandItem(scrollArea);
            } else if ([
                73,
                83,
                76
              ].indexOf(keyCode) >= 0) {
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
        });
      }(window, document, jQuery));
      var call = Function.prototype.call.bind(Function.prototype.call);
      var hasOwn = Object.prototype.hasOwnProperty;
      window.items = function (obj) {
        'use strict';
        var $__2;
        return $__2 = {}, Object.defineProperty($__2, Symbol.iterator, {
          value: function () {
            return $traceurRuntime.initGeneratorFunction(function $__9() {
              var $__10, $__11, $__12, $__13, $x, x;
              return $traceurRuntime.createGeneratorInstance(function ($ctx) {
                while (true)
                  switch ($ctx.state) {
                  case 0:
                    $__10 = [];
                    $__11 = obj;
                    for ($__12 in $__11)
                      $__10.push($__12);
                    $ctx.state = 26;
                    break;
                  case 26:
                    $__13 = 0;
                    $ctx.state = 24;
                    break;
                  case 24:
                    $ctx.state = $__13 < $__10.length ? 20 : -2;
                    break;
                  case 15:
                    $__13++;
                    $ctx.state = 24;
                    break;
                  case 20:
                    $x = $__10[$traceurRuntime.toProperty($__13)];
                    $ctx.state = 21;
                    break;
                  case 21:
                    $ctx.state = !($traceurRuntime.toProperty($x) in $__11) ? 15 : 18;
                    break;
                  case 18:
                    $ctx.pushTry(8, null);
                    $ctx.state = 11;
                    break;
                  case 11:
                    throw undefined;
                    $ctx.state = 13;
                    break;
                  case 13:
                    $ctx.popTry();
                    $ctx.state = 15;
                    break;
                  case 8:
                    $ctx.popTry();
                    x = $ctx.storedException;
                    $ctx.state = 6;
                    break;
                  case 6:
                    x = $x;
                    $ctx.state = 7;
                    break;
                  case 7:
                    $ctx.state = call(hasOwn, obj, x) ? 1 : 15;
                    break;
                  case 1:
                    $ctx.state = 2;
                    return [
                      x,
                      obj[$traceurRuntime.toProperty(x)]
                    ];
                  case 2:
                    $ctx.maybeThrow();
                    $ctx.state = 15;
                    break;
                  default:
                    return $ctx.end();
                  }
              }, $__9, this);
            })();
          },
          configurable: true,
          enumerable: true,
          writable: true
        }), $__2;
      };
      window.enumerate = function (list) {
        'use strict';
        var $__2;
        return $__2 = {}, Object.defineProperty($__2, Symbol.iterator, {
          value: function () {
            return $traceurRuntime.initGeneratorFunction(function $__9() {
              var counter, $counter;
              return $traceurRuntime.createGeneratorInstance(function ($ctx) {
                while (true)
                  switch ($ctx.state) {
                  case 0:
                    $ctx.pushTry(28, null);
                    $ctx.state = 31;
                    break;
                  case 31:
                    throw undefined;
                    $ctx.state = 33;
                    break;
                  case 33:
                    $ctx.popTry();
                    $ctx.state = -2;
                    break;
                  case 28:
                    $ctx.popTry();
                    $counter = $ctx.storedException;
                    $ctx.state = 26;
                    break;
                  case 26:
                    $counter = 0;
                    $ctx.state = 27;
                    break;
                  case 27:
                    $ctx.state = $counter < list.length ? 17 : -2;
                    break;
                  case 22:
                    $counter += 1;
                    $ctx.state = 27;
                    break;
                  case 17:
                    $ctx.pushTry(15, null);
                    $ctx.state = 18;
                    break;
                  case 18:
                    throw undefined;
                    $ctx.state = 20;
                    break;
                  case 20:
                    $ctx.popTry();
                    $ctx.state = 22;
                    break;
                  case 15:
                    $ctx.popTry();
                    counter = $ctx.storedException;
                    $ctx.state = 13;
                    break;
                  case 13:
                    counter = $counter;
                    $ctx.state = 14;
                    break;
                  case 14:
                    $ctx.pushTry(null, 6);
                    $ctx.state = 8;
                    break;
                  case 8:
                    $ctx.state = 2;
                    return [
                      counter,
                      list[$traceurRuntime.toProperty(counter)]
                    ];
                  case 2:
                    $ctx.maybeThrow();
                    $ctx.state = 22;
                    break;
                  case 6:
                    $ctx.popTry();
                    $ctx.state = 12;
                    break;
                  case 12:
                    $counter = counter;
                    $ctx.state = 10;
                    break;
                  default:
                    return $ctx.end();
                  }
              }, $__9, this);
            })();
          },
          configurable: true,
          enumerable: true,
          writable: true
        }), $__2;
      };
      window.reverse = function (list) {
        'use strict';
        var $__2;
        return $__2 = {}, Object.defineProperty($__2, Symbol.iterator, {
          value: function () {
            return $traceurRuntime.initGeneratorFunction(function $__9() {
              var counter, $counter;
              return $traceurRuntime.createGeneratorInstance(function ($ctx) {
                while (true)
                  switch ($ctx.state) {
                  case 0:
                    $ctx.pushTry(28, null);
                    $ctx.state = 31;
                    break;
                  case 31:
                    throw undefined;
                    $ctx.state = 33;
                    break;
                  case 33:
                    $ctx.popTry();
                    $ctx.state = -2;
                    break;
                  case 28:
                    $ctx.popTry();
                    $counter = $ctx.storedException;
                    $ctx.state = 26;
                    break;
                  case 26:
                    $counter = list.length;
                    $ctx.state = 27;
                    break;
                  case 27:
                    $ctx.state = $counter >= 0 ? 17 : -2;
                    break;
                  case 22:
                    $counter -= 1;
                    $ctx.state = 27;
                    break;
                  case 17:
                    $ctx.pushTry(15, null);
                    $ctx.state = 18;
                    break;
                  case 18:
                    throw undefined;
                    $ctx.state = 20;
                    break;
                  case 20:
                    $ctx.popTry();
                    $ctx.state = 22;
                    break;
                  case 15:
                    $ctx.popTry();
                    counter = $ctx.storedException;
                    $ctx.state = 13;
                    break;
                  case 13:
                    counter = $counter;
                    $ctx.state = 14;
                    break;
                  case 14:
                    $ctx.pushTry(null, 6);
                    $ctx.state = 8;
                    break;
                  case 8:
                    $ctx.state = 2;
                    return list[$traceurRuntime.toProperty(counter)];
                  case 2:
                    $ctx.maybeThrow();
                    $ctx.state = 22;
                    break;
                  case 6:
                    $ctx.popTry();
                    $ctx.state = 12;
                    break;
                  case 12:
                    $counter = counter;
                    $ctx.state = 10;
                    break;
                  default:
                    return $ctx.end();
                  }
              }, $__9, this);
            })();
          },
          configurable: true,
          enumerable: true,
          writable: true
        }), $__2;
      };
      app.directive('newsAutoFocus', function () {
        'use strict';
        return function (scope, elem, attrs) {
          $(attrs.newsAutofocus).focus();
        };
      });
      app.directive('newsBindHtmlUnsafe', function () {
        'use strict';
        return function (scope, elem, attr) {
          scope.$watch(attr.newsBindHtmlUnsafe, function () {
            elem.html(scope.$eval(attr.newsBindHtmlUnsafe));
          });
        };
      });
      app.directive('newsReadFile', function () {
        'use strict';
        return function (scope, elem, attr) {
          elem.change(function () {
            var file = elem[0].files[0];
            var reader = new FileReader();
            reader.onload = function (event) {
              elem[0].value = 0;
              scope.$fileContent = event.target.result;
              scope.$apply(attr.newsReadFile);
            };
            reader.readAsText(file);
          });
        };
      });
      app.directive('newsScroll', [
        '$timeout',
        function ($timeout) {
          'use strict';
          var autoPage = function (enabled, limit, callback, elem) {
            if (enabled) {
              try {
                throw undefined;
              } catch (counter) {
                counter = 0;
                for (var $__3 = reverse(elem.find('.feed_item'))[$traceurRuntime.toProperty(Symbol.iterator)](), $__4; !($__4 = $__3.next()).done;) {
                  try {
                    throw undefined;
                  } catch (item) {
                    item = $__4.value;
                    {
                      item = $(item);
                      if (counter >= limit) {
                        break;
                      }
                      if (item.position().top < 0) {
                        callback();
                        break;
                      }
                      counter += 1;
                    }
                  }
                }
              }
            }
          };
          var markRead = function (enabled, callback, elem) {
            if (enabled) {
              try {
                throw undefined;
              } catch (ids) {
                ids = [];
                for (var $__3 = elem.find('.feed_item:not(.read)')[$traceurRuntime.toProperty(Symbol.iterator)](), $__4; !($__4 = $__3.next()).done;) {
                  try {
                    throw undefined;
                  } catch (item) {
                    item = $__4.value;
                    {
                      item = $(item);
                      if (item.position().top <= -50) {
                        ids.push(parseInt(item.data('id'), 10));
                      } else {
                        break;
                      }
                    }
                  }
                }
                callback(ids);
              }
            }
          };
          return {
            restrict: 'A',
            scope: {
              'newsScrollAutoPage': '&',
              'newsScrollMarkRead': '&',
              'newsScrollEnabledMarkRead': '=',
              'newsScrollEnableAutoPage': '=',
              'newsScrollMarkReadTimeout': '@',
              'newsScrollTimeout': '@',
              'newsScrollAutoPageWhenLeft': '@'
            },
            link: function (scope, elem) {
              var allowScroll = true;
              scope.newsScrollTimeout = scope.newsScrollTimeout || 1;
              scope.newsScrollMarkReadTimeout = scope.newsScrollMarkReadTimeout || 1;
              scope.newsScrollAutoPageWhenLeft = scope.newsScrollAutoPageWhenLeft || 50;
              var scrollHandler = function () {
                if (allowScroll) {
                  allowScroll = false;
                  $timeout(function () {
                    allowScroll = true;
                  }, scope.newsScrollTimeout * 1000);
                  autoPage(scope.newsScrollEnableAutoPage, scope.newsScrollAutoPageWhenLeft, scope.newsScrollAutoPage, elem);
                  $timeout(function () {
                    markRead(scope.newsScrollEnabledMarkRead, scope.newsScrollMarkRead, elem);
                  }, scope.newsScrollMarkReadTimeout * 1000);
                }
              };
              elem.on('scroll', scrollHandler);
              scope.$on('$destroy', function () {
                elem.off('scroll', scrollHandler);
              });
            }
          };
        }
      ]);
      app.directive('newsTooltip', function () {
        'use strict';
        return function (scope, elem) {
          elem.tooltip();
        };
      });
      app.directive('newsTriggerClick', function () {
        'use strict';
        return function (scope, elm, attr) {
          elm.click(function () {
            $(attr.newsTriggerClick).trigger('click');
          });
        };
      });
    }(window, document, angular, jQuery, OC, oc_requesttoken));
    return {};
  }();