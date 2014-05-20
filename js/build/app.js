(function(angular, $, OC, oc_requesttoken, undefined){

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
    var getResolve, feedType;
    feedType = {
      FEED: 0,
      FOLDER: 1,
      STARRED: 2,
      SUBSCRIPTIONS: 3,
      SHARED: 4
    };
    // constants
    $provide.constant('REFRESH_RATE', 60);
    // seconds, how often feeds and folders shoudl be refreshed
    $provide.constant('ITEM_BATCH_SIZE', 50);
    // how many items to autopage by
    $provide.constant('BASE_URL', OC.generateUrl('/apps/news'));
    $provide.constant('FEED_TYPE', feedType);
    // make sure that the CSRF header is only sent to the ownCloud domain
    $provide.factory('CSRFInterceptor', function ($q, BASE_URL) {
      return {
        request: function (config) {
          if (config.url.indexOf(BASE_URL) === 0) {
            config.headers.requesttoken = oc_requesttoken;
          }
          return config || $q.when(config);
        }
      };
    });
    $httpProvider.interceptors.push('CSRFInterceptor');
    // routing
    getResolve = function (type) {
      return {
        data: [
          '$http',
          '$route',
          '$q',
          'BASE_URL',
          'ITEM_BATCH_SIZE',
          function ($http, $route, $q, BASE_URL, ITEM_BATCH_SIZE) {
            var parameters, deferred;
            parameters = {
              type: type,
              limit: ITEM_BATCH_SIZE
            };
            if ($route.current.params.id !== undefined) {
              parameters.id = $route.current.params.id;
            }
            deferred = $q.defer();
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
    // show Loading screen
    Loading.setLoading('global', true);
    // listen to keys in returned queries to automatically distribute the
    // incoming values to models
    Publisher.subscribe(ItemResource).toChannels('items', 'newestItemId', 'starred');
    Publisher.subscribe(FolderResource).toChannels('folders');
    Publisher.subscribe(FeedResource).toChannels('feeds');
    Publisher.subscribe(Settings).toChannels('settings');
    // load feeds, settings and last read feed
    var settingsDeferred, activeFeedDeferred, folderDeferred, feedDeferred;
    settingsDeferred = $q.defer();
    $http.get(BASE_URL + '/settings').success(function (data) {
      Publisher.publishAll(data);
      settingsDeferred.resolve();
    });
    activeFeedDeferred = $q.defer();
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
    folderDeferred = $q.defer();
    $http.get(BASE_URL + '/folders').success(function (data) {
      Publisher.publishAll(data);
      folderDeferred.resolve();
    });
    feedDeferred = $q.defer();
    $http.get(BASE_URL + '/feeds').success(function (data) {
      Publisher.publishAll(data);
      feedDeferred.resolve();
    });
    // disable loading if all initial requests finished
    $q.all([
      settingsDeferred.promise,
      activeFeedDeferred.promise,
      feedDeferred.promise,
      folderDeferred.promise
    ]).then(function () {
      Loading.setLoading('global', false);
    });
    // refresh feeds and folders
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
    // in case of wrong id etc show all items
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
    // distribute data to models based on key
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
    var FeedResource = function ($http) {
      Resource.call(this, 'url', $http);
    };
    FeedResource.prototype = Object.create(Resource.prototype);
    return new FeedResource($http);
  }
]);
app.factory('FolderResource', [
  'Resource',
  '$http',
  function (Resource, $http) {
    'use strict';
    var FolderResource = function ($http) {
      Resource.call(this, 'name', $http);
    };
    FolderResource.prototype = Object.create(Resource.prototype);
    return new FolderResource($http);
  }
]);
app.factory('ItemResource', [
  'Resource',
  '$http',
  function (Resource, $http) {
    'use strict';
    var ItemResource = function ($http) {
      Resource.call(this, 'id', $http);
    };
    ItemResource.prototype = Object.create(Resource.prototype);
    ItemResource.prototype.receive = function (value, channel) {
      switch (channel) {
      case 'newestItemId':
        this.newestItemId = value;
        break;
      case 'starred':
        this.starredCount = value;
        break;
      default:
        Resource.prototype.receive.call(this, value, channel);
      }
    };
    ItemResource.prototype.getNewestItemId = function () {
      return this.newestItemId;
    };
    ItemResource.prototype.getStarredCount = function () {
      return this.starredCount;
    };
    return new ItemResource($http);
  }
]);
app.service('Loading', function () {
  'use strict';
  this.loading = {
    global: false,
    content: false,
    autopaging: false
  };
  this.setLoading = function (area, isLoading) {
    this.loading[area] = isLoading;
  };
  this.isLoading = function (area) {
    return this.loading[area];
  };
});
app.service('Publisher', function () {
  'use strict';
  var self = this;
  this.channels = {};
  this.subscribe = function (object) {
    return {
      toChannels: function () {
        var counter, channel;
        for (counter = 0; counter < arguments.length; counter += 1) {
          channel = arguments[counter];
          self.channels[channel] = self.channels[channel] || [];
          self.channels[channel].push(object);
        }
      }
    };
  };
  this.publishAll = function (data) {
    var channel, counter;
    for (channel in data) {
      if (data.hasOwnProperty(channel) && this.channels[channel] !== undefined) {
        for (counter = 0; counter < this.channels[channel].length; counter += 1) {
          this.channels[channel][counter].receive(data[channel], channel);
        }
      }
    }
  };
});
app.factory('Resource', function () {
  'use strict';
  var Resource = function (id, http) {
    this.id = id;
    this.values = [];
    this.hashMap = {};
    this.http = http;
  };
  Resource.prototype = {
    receive: function (values) {
      var self = this;
      values.forEach(function (value) {
        self.add(value);
      });
    },
    add: function (value) {
      var key, existing;
      existing = this.hashMap[value[this.id]];
      if (existing === undefined) {
        this.values.push(value);
        this.hashMap[value[this.id]] = value;
      } else {
        // copy values from new to old object if it exists already
        for (key in value) {
          if (value.hasOwnProperty(key)) {
            existing[key] = value[key];
          }
        }
      }
    },
    size: function () {
      return this.values.length;
    },
    get: function (id) {
      return this.hashMap[id];
    },
    delete: function (id) {
      // find index of object that should be deleted
      var i, deleteAtIndex;
      for (i = 0; i < this.values.length; i += 1) {
        if (this.values[i][this.id] === id) {
          deleteAtIndex = i;
          break;
        }
      }
      if (deleteAtIndex !== undefined) {
        this.values.splice(deleteAtIndex, 1);
      }
      if (this.hashMap[id] !== undefined) {
        delete this.hashMap[id];
      }
    },
    clear: function () {
      this.hashMap = {};
      // http://stackoverflow.com/questions/1232040/how-to-empty-an-array-in-javascript
      // this is the fastes way to empty an array when you want to keep the
      // reference around
      while (this.values.length > 0) {
        this.values.pop();
      }
    },
    getAll: function () {
      return this.values;
    }
  };
  return Resource;
});
app.service('Settings', function () {
  'use strict';
  this.settings = {};
  this.receive = function (data) {
    var key;
    for (key in data) {
      if (data.hasOwnProperty(key)) {
        this.settings[key] = data[key];
      }
    }
  };
  this.get = function (key) {
    return this.settings[key];
  };
  this.set = function (key, value) {
    this.settings[key] = value;
  };
});
(function (document, $) {
  'use strict';
  $(document).on('keyup', function (event) {
    var keyCode = event.keyCode;
    console.log(undefined);
    console.log(keyCode);
  });
  console.log('hi');
}(document, jQuery));

})(angular, jQuery, OC, oc_requesttoken);