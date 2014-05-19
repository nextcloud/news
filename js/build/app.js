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
    // constants
    $provide.constant('CONFIG', { REFRESH_RATE: 60 });
    $provide.constant('BASE_URL', OC.generateUrl('/apps/news'));
    $provide.constant('FEED_TYPE', {
      FEED: 0,
      FOLDER: 1,
      STARRED: 2,
      SUBSCRIPTIONS: 3,
      SHARED: 4
    });
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
    $routeProvider.when('/items', {
      controller: 'ItemController',
      templateUrl: 'content.html',
      resolve: {}
    }).when('/items/starred', {
      controller: 'StarredController',
      templateUrl: 'content.html',
      resolve: {}
    }).when('/items/feeds/:id', {
      controller: 'FeedController',
      templateUrl: 'content.html',
      resolve: {}
    }).when('/items/folders/:id', {
      controller: 'FolderController',
      templateUrl: 'content.html',
      resolve: {}
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
  'Item',
  'Feed',
  'Folder',
  'Settings',
  'Publisher',
  'BASE_URL',
  'FEED_TYPE',
  'CONFIG',
  function ($rootScope, $location, $http, $q, $interval, Loading, Item, Feed, Folder, Settings, Publisher, BASE_URL, FEED_TYPE, CONFIG) {
    'use strict';
    // show Loading screen
    Loading.setLoading('global', true);
    // listen to keys in returned queries to automatically distribute the
    // incoming values to models
    Publisher.subscribe(Item).toChannel('items');
    Publisher.subscribe(Folder).toChannel('folders');
    Publisher.subscribe(Feed).toChannel('feeds');
    Publisher.subscribe(Settings).toChannel('settings');
    // load feeds, settings and last read feed
    var settingsDeferred, activeFeedDeferred;
    settingsDeferred = $q.defer();
    $http.get(BASE_URL + '/settings').then(function (data) {
      Publisher.publishAll(data);
      settingsDeferred.resolve();
    });
    activeFeedDeferred = $q.defer();
    $http.get(BASE_URL + '/feeds/active').then(function (data) {
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
    // disable loading if all initial requests finished
    $q.all([
      settingsDeferred.promise,
      activeFeedDeferred.promise
    ]).then(function () {
      Loading.setLoading('global', false);
    });
    // refresh feeds and folders
    $interval(function () {
      $http.get(BASE_URL + '/feeds');
      $http.get(BASE_URL + '/folders');
    }, CONFIG.REFRESH_RATE * 1000);
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
  'Feed',
  'Folder',
  function (Loading, Feed, Folder) {
    'use strict';
    this.loading = Loading;
    this.isFirstRun = function () {
      return Feed.size() === 0 && Folder.size() === 0;
    };
  }
]);
app.controller('ItemController', function () {
  'use strict';
  console.log('here');
});
app.controller('NavigationController', function () {
  'use strict';
  console.log('here');
});
app.controller('SettingsController', function () {
  'use strict';
  console.log('here');
});
app.factory('Feed', [
  'Model',
  function (Model) {
    'use strict';
    var Feed = function () {
      Model.call(this, 'url');
    };
    Feed.prototype = Object.create(Model.prototype);
    return new Feed();
  }
]);
app.factory('Folder', [
  'Model',
  function (Model) {
    'use strict';
    var Folder = function () {
      Model.call(this, 'name');
    };
    Folder.prototype = Object.create(Model.prototype);
    return new Folder();
  }
]);
app.factory('Item', [
  'Model',
  function (Model) {
    'use strict';
    var Item = function () {
      Model.call(this, 'id');
    };
    Item.prototype = Object.create(Model.prototype);
    return new Item();
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
app.factory('Model', function () {
  'use strict';
  var Model = function (id) {
    this.id = id;
    this.values = [];
    this.hashMap = {};
  };
  Model.prototype = {
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
    }
  };
  return Model;
});
app.service('Publisher', function () {
  'use strict';
  var self = this;
  this.channels = {};
  this.subscribe = function (object) {
    return {
      toChannel: function (channel) {
        self.channels[channel] = self.channels[channel] || [];
        self.channels[channel].push(object);
      }
    };
  };
  this.publishAll = function (data) {
    var channel, counter;
    for (channel in data) {
      if (data.hasOwnProperty(channel) && this.channels[channel] !== undefined) {
        for (counter = 0; counter < this.channels[channel].length; counter += 1) {
          this.channels[channel][counter].receive(data[channel]);
        }
      }
    }
  };
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

})(angular, jQuery, OC, oc_requesttoken);