(function(angular, $, OC, undefined){

'use strict';


var app = angular.module('News', [
    'ngRoute',
    'ngSanitize',
    'ngAnimate'
  ]);
app.config([
  '$routeProvider',
  '$provide',
  function ($routeProvider, $provide) {
    'use strict';
    $provide.constant('baseUrl', OC.generateUrl(''));
    $routeProvider.when('/items', {
      controller: 'AllItemsController',
      templateUrl: 'content.html',
      resolve: {}
    }).when('/items/starred', {
      controller: 'StarredItemsController',
      templateUrl: 'content.html',
      resolve: {}
    }).when('/items/feeds/:id', {
      controller: 'FeedItemsController',
      templateUrl: 'content.html',
      resolve: {}
    }).when('/items/folders/:id', {
      controller: 'FolderItemsController',
      templateUrl: 'content.html',
      resolve: {}
    }).otherwise({ redirectTo: '/items' });
  }
]);
app.run([
  '$rootScope',
  '$location',
  'Loading',
  'Setup',
  function ($rootScope, $location, Loading, Setup) {
    'use strict';
    // load feeds, settings and last read feed
    Setup.load();
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
app.service('Loading', function () {
  'use strict';
  this.loading = {
    global: false,
    content: false
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
  this.publish = function (value) {
    return {
      onChannel: function (channel) {
        self.channels[channel].forEach(function (object) {
          object.receive(value);
        });
      }
    };
  };
});
app.service('Setup', function () {
  'use strict';
  this.load = function () {
    console.log('init');
  };
});

})(angular, jQuery, OC);