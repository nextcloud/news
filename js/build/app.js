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
            controller: 'ContentController',
            templateUrl: 'content.html',
            resolve: getResolve(feedType.SUBSCRIPTIONS)
          }).when('/items/starred', {
            controller: 'ContentController',
            templateUrl: 'content.html',
            resolve: getResolve(feedType.STARRED)
          }).when('/items/feeds/:id', {
            controller: 'ContentController',
            templateUrl: 'content.html',
            resolve: getResolve(feedType.FEED)
          }).when('/items/folders/:id', {
            controller: 'ContentController',
            templateUrl: 'content.html',
            resolve: getResolve(feedType.FOLDER)
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
        'Settings',
        'Publisher',
        'BASE_URL',
        'FEED_TYPE',
        'REFRESH_RATE',
        function ($rootScope, $location, $http, $q, $interval, Loading, ItemResource, FeedResource, FolderResource, Settings, Publisher, BASE_URL, FEED_TYPE, REFRESH_RATE) {
          'use strict';
          Loading.setLoading('global', true);
          Publisher.subscribe(ItemResource).toChannels('items', 'newestItemId', 'starred');
          Publisher.subscribe(FolderResource).toChannels('folders');
          Publisher.subscribe(FeedResource).toChannels('feeds');
          Publisher.subscribe(Settings).toChannels('settings');
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
              url = '/items/feeds/' + data.id;
              break;
            case FEED_TYPE.FOLDER:
              url = '/items/folders/' + data.id;
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
        'data',
        function (Publisher, FeedResource, ItemResource, data) {
          'use strict';
          Publisher.publishAll(data);
          this.getItems = function () {
            return ItemResource.getAll();
          };
          this.getFeeds = function () {
            return FeedResource.getAll();
          };
        }
      ]);
      app.controller('NavigationController', function () {
        'use strict';
        console.log('here');
      });
      app.controller('SettingsController', function () {
        'use strict';
        console.log('here');
      });
      app.factory('FeedResource', [
        'Resource',
        '$http',
        function (Resource, $http) {
          'use strict';
          var FeedResource = function FeedResource($http) {
            $traceurRuntime.superCall(this, $FeedResource.prototype, 'constructor', [
              'url',
              $http
            ]);
          };
          var $FeedResource = FeedResource;
          $traceurRuntime.createClass(FeedResource, {}, {}, Resource);
          return new FeedResource($http);
        }
      ]);
      app.factory('FolderResource', [
        'Resource',
        '$http',
        function (Resource, $http) {
          'use strict';
          var FolderResource = function FolderResource($http) {
            $traceurRuntime.superCall(this, $FolderResource.prototype, 'constructor', [
              'name',
              $http
            ]);
          };
          var $FolderResource = FolderResource;
          $traceurRuntime.createClass(FolderResource, {}, {}, Resource);
          return new FolderResource($http);
        }
      ]);
      app.factory('ItemResource', [
        'Resource',
        '$http',
        function (Resource, $http) {
          'use strict';
          var ItemResource = function ItemResource($http) {
            $traceurRuntime.superCall(this, $ItemResource.prototype, 'constructor', [
              'id',
              $http
            ]);
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
            }
          }, {}, Resource);
          return new ItemResource($http);
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
              for (var channels = [], $__4 = 0; $__4 < arguments.length; $__4++)
                $traceurRuntime.setProperty(channels, $__4, arguments[$traceurRuntime.toProperty($__4)]);
              for (var $__2 = channels[$traceurRuntime.toProperty(Symbol.iterator)](), $__3; !($__3 = $__2.next()).done;) {
                try {
                  throw undefined;
                } catch (channel) {
                  channel = $__3.value;
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
          for (var $channel in data) {
            try {
              throw undefined;
            } catch (channel) {
              channel = $channel;
              if ($__0.channels[$traceurRuntime.toProperty(channel)] !== undefined) {
                for (var $__2 = $__0.channels[$traceurRuntime.toProperty(channel)][$traceurRuntime.toProperty(Symbol.iterator)](), $__3; !($__3 = $__2.next()).done;) {
                  try {
                    throw undefined;
                  } catch (listener) {
                    listener = $__3.value;
                    {
                      listener.receive(data[$traceurRuntime.toProperty(channel)], channel);
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
        var Resource = function Resource(id, http) {
          this.id = id;
          this.values = [];
          this.hashMap = {};
          this.http = http;
        };
        $traceurRuntime.createClass(Resource, {
          receive: function (values) {
            var $__0 = this;
            values.forEach(function (value) {
              $__0.add(value);
            });
          },
          add: function (value) {
            var existing = this.hashMap[$traceurRuntime.toProperty(value[$traceurRuntime.toProperty(this.id)])];
            if (existing === undefined) {
              this.values.push(value);
              $traceurRuntime.setProperty(this.hashMap, value[$traceurRuntime.toProperty(this.id)], value);
            } else {
              for (var $key in value) {
                try {
                  throw undefined;
                } catch (key) {
                  key = $key;
                  if (value.hasOwnProperty(key)) {
                    $traceurRuntime.setProperty(existing, key, value[$traceurRuntime.toProperty(key)]);
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
            var deleteAtIndex;
            {
              try {
                throw undefined;
              } catch ($i) {
                $i = 0;
                for (; $i < this.values.length; $i += 1) {
                  try {
                    throw undefined;
                  } catch (i) {
                    i = $i;
                    try {
                      if (this.values[$traceurRuntime.toProperty(i)][$traceurRuntime.toProperty(this.id)] === id) {
                        deleteAtIndex = i;
                        break;
                      }
                    } finally {
                      $i = i;
                    }
                  }
                }
              }
            }
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
      app.service('Settings', function () {
        'use strict';
        var $__0 = this;
        this.settings = {};
        this.receive = function (data) {
          for (var $key in data) {
            try {
              throw undefined;
            } catch (key) {
              key = $key;
              $traceurRuntime.setProperty($__0.settings, key, data[$traceurRuntime.toProperty(key)]);
            }
          }
        };
        this.get = function (key) {
          return $__0.settings[$traceurRuntime.toProperty(key)];
        };
        this.set = function (key, value) {
          $traceurRuntime.setProperty($__0.settings, key, value);
        };
      });
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
          for (var $__2 = items[$traceurRuntime.toProperty(Symbol.iterator)](), $__3; !($__3 = $__2.next()).done;) {
            try {
              throw undefined;
            } catch (item) {
              item = $__3.value;
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
          for (var $__2 = items[$traceurRuntime.toProperty(Symbol.iterator)](), $__3; !($__3 = $__2.next()).done;) {
            try {
              throw undefined;
            } catch (item) {
              item = $__3.value;
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
          for (var $__2 = items[$traceurRuntime.toProperty(Symbol.iterator)](), $__3; !($__3 = $__2.next()).done;) {
            try {
              throw undefined;
            } catch (item) {
              item = $__3.value;
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
    }(window, document, angular, jQuery, OC, oc_requesttoken));
    return {};
  }();