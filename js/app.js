/**
 * ownCloud RSS reader app - v0.0.1
 *
 * Copyright (c) 2013 - Alessandro Cosentino <cosenal@gmail.com>
 * Copyright (c) 2013 - Bernhard Posselt <nukeawhale@gmail.com>
 *
 * This file is licensed under the Affero General Public License version 3 or later.
 * See the COPYING-README file
 *
 */

/*
# ownCloud
#
# @author Bernhard Posselt
# Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
#
# This file is licensed under the Affero General Public License version 3 or
# later.
#
# See the COPYING-README file
#
*/


/*
# Various config stuff for owncloud
*/


(function() {

  angular.module('OC', []).config([
    '$httpProvider', function($httpProvider) {
      $httpProvider.defaults.get['requesttoken'] = oc_requesttoken;
      $httpProvider.defaults.post['requesttoken'] = oc_requesttoken;
      $httpProvider.defaults.post['Content-Type'] = 'application/x-www-form-urlencoded';
      $httpProvider.defaults.get['Content-Type'] = 'application/x-www-form-urlencoded';
      return $httpProvider.defaults.transformRequest = function(data) {
        if (angular.isDefined(data)) {
          return data;
        } else {
          return $.param(data);
        }
      };
    }
  ]);

  angular.module('OC').run([
    '$rootScope', 'Router', function($rootScope, Router) {
      var init;
      init = function() {
        return $rootScope.$broadcast('routesLoaded');
      };
      return Router.registerLoadedCallback(init);
    }
  ]);

  /*
  # ownCloud
  #
  # @author Bernhard Posselt
  # Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
  #
  # This file is licensed under the Affero General Public License version 3 or
  # later.
  #
  # See the COPYING-README file
  #
  */


  /*
  # Used for properly distributing received model data from the server
  */


  angular.module('OC').factory('_Publisher', function() {
    var Publisher;
    Publisher = (function() {

      function Publisher() {
        this.subscriptions = {};
      }

      Publisher.prototype.subscribeModelTo = function(model, name) {
        var _base;
        (_base = this.subscriptions)[name] || (_base[name] = []);
        return this.subscriptions[name].push(model);
      };

      Publisher.prototype.publishDataTo = function(data, name) {
        var subscriber, _i, _len, _ref, _results;
        _ref = this.subscriptions[name] || [];
        _results = [];
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          subscriber = _ref[_i];
          _results.push(subscriber.handle(data));
        }
        return _results;
      };

      return Publisher;

    })();
    return Publisher;
  });

  /*
  # ownCloud
  #
  # @author Bernhard Posselt
  # Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
  #
  # This file is licensed under the Affero General Public License version 3 or
  # later.
  #
  # See the COPYING-README file
  #
  */


  angular.module('OC').factory('_Request', function() {
    var Request;
    Request = (function() {

      function Request(_$http, _$rootScope, _publisher, _token, _router) {
        var _this = this;
        this._$http = _$http;
        this._$rootScope = _$rootScope;
        this._publisher = _publisher;
        this._token = _token;
        this._router = _router;
        this._initialized = false;
        this._shelvedRequests = [];
        this._$rootScope.$on('routesLoaded', function() {
          _this._executeShelvedRequests();
          _this._initialized = true;
          return _this._shelvedRequests = [];
        });
      }

      Request.prototype.request = function(route, routeParams, data, onSuccess, onFailure, config) {
        var defaultConfig, key, url, value,
          _this = this;
        if (routeParams == null) {
          routeParams = {};
        }
        if (data == null) {
          data = {};
        }
        if (onSuccess == null) {
          onSuccess = null;
        }
        if (onFailure == null) {
          onFailure = null;
        }
        if (config == null) {
          config = {};
        }
        if (!this._initialized) {
          this._shelveRequest(route, routeParams, data, method, config);
          return;
        }
        url = this._router.generate(route, routeParams);
        defaultConfig = {
          method: 'GET',
          url: url,
          data: data
        };
        for (key in config) {
          value = config[key];
          defaultConfig[key] = value;
        }
        return this._$http(config).success(function(data, status, headers, config) {
          var name, _ref, _results;
          if (onSuccess) {
            onSuccess(data, status, headers, config);
          }
          _ref = data.data;
          _results = [];
          for (name in _ref) {
            value = _ref[name];
            _results.push(_this.publisher.publishDataTo(name, value));
          }
          return _results;
        }).error(function(data, status, headers, config) {
          if (onFailure) {
            return onFailure(data, status, headers, config);
          }
        });
      };

      Request.prototype._shelveRequest = function(route, routeParams, data, method, config) {
        var request;
        request = {
          route: route,
          routeParams: routeParams,
          data: data,
          config: config,
          method: method
        };
        return this._shelvedRequests.push(request);
      };

      Request.prototype._executeShelvedRequests = function() {
        var req, _i, _len, _ref, _results;
        _ref = this._shelvedRequests;
        _results = [];
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          req = _ref[_i];
          _results.push(this.post(req.route, req.routeParams, req.data, req.method, req.config));
        }
        return _results;
      };

      return Request;

    })();
    return Request;
  });

  /*
  # ownCloud
  #
  # @author Bernhard Posselt
  # Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
  #
  # This file is licensed under the Affero General Public License version 3 or
  # later.
  #
  # See the COPYING-README file
  #
  */


  /*
  # Inject router into angular to make testing easier
  */


  angular.module('OC').factory('Router', function() {
    return OC.Router;
  });

}).call(this);


/*
# ownCloud news app
#
# @author Alessandro Cosentino
# @author Bernhard Posselt
# Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
# Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
#
# This file is licensed under the Affero General Public License version 3 or
# later.
#
# See the COPYING-README file
#
*/


(function() {
  var app, markingRead, scrolling,
    __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  app = angular.module('News', ['ui']).config(function($provide) {
    var config;
    config = {
      MarkReadTimeout: 500,
      ScrollTimeout: 500,
      initialLoadedItemsNr: 20,
      FeedUpdateInterval: 6000000
    };
    return $provide.value('Config', config);
  });

  app.run([
    'PersistenceNews', function(PersistenceNews) {
      return PersistenceNews.loadInitial();
    }
  ]);

  $(document).ready(function() {
    $(this).keyup(function(e) {
      if ((e.which === 116) || (e.which === 82 && e.ctrlKey)) {
        document.location.reload(true);
        return false;
      }
    });
    return $('#browselink').click(function() {
      return $('#file_upload_start').trigger('click');
    });
  });

  /*
  # ownCloud news app
  #
  # @author Alessandro Cosentino
  # @author Bernhard Posselt
  # Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
  # Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
  #
  # This file is licensed under the Affero General Public License version 3 or
  # later.
  #
  # See the COPYING-README file
  #
  */


  angular.module('News').factory('_ActiveFeed', function() {
    var ActiveFeed;
    ActiveFeed = (function() {

      function ActiveFeed() {
        this.id = 0;
        this.type = 3;
      }

      ActiveFeed.prototype.handle = function(data) {
        this.id = data.id;
        return this.type = data.type;
      };

      return ActiveFeed;

    })();
    return ActiveFeed;
  });

  /*
  # ownCloud news app
  #
  # @author Alessandro Cosentino
  # @author Bernhard Posselt
  # Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
  # Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
  #
  # This file is licensed under the Affero General Public License version 3 or
  # later.
  #
  # See the COPYING-README file
  #
  */


  angular.module('News').factory('_Cache', function() {
    var Cache;
    Cache = (function() {

      function Cache(feedType, feedModel, folderModel) {
        this.feedType = feedType;
        this.feedModel = feedModel;
        this.folderModel = folderModel;
        this.clear();
      }

      Cache.prototype.clear = function() {
        this.feedCache = [];
        this.folderCache = {};
        this.folderCacheLastModified = 0;
        this.importantCache = [];
        this.highestId = 0;
        this.lowestId = 0;
        this.highestTimestamp = 0;
        this.lowestTimestamp = 0;
        this.highestIds = {};
        this.lowestIds = {};
        this.highestTimestamps = {};
        return this.lowestTimestamps = {};
      };

      Cache.prototype.add = function(item) {
        if (!this.feedCache[item.feedId]) {
          this.feedCache[item.feedId] = [];
        }
        this.feedCache[item.feedId].push(item);
        if (this.highestTimestamp < item.date) {
          this.highestTimestamp = item.date;
        }
        if (this.lowestTimestamp > item.date) {
          this.lowestTimestamp = item.date;
        }
        if (this.highestId < item.id) {
          this.highestId = item.id;
        }
        if (this.lowestId > item.id) {
          this.lowestId = item.id;
        }
        if (item.isImportant) {
          this.importantCache.push(item);
        }
        if (this.highestTimestamps[item.feedId] === void 0 || item.id > this.highestTimestamps[item.feedId]) {
          this.highestTimestamps[item.feedId] = item.date;
        }
        if (this.lowestTimestamps[item.feedId] === void 0 || item.id > this.lowestTimestamps[item.feedId]) {
          this.lowestTimestamps[item.feedId] = item.date;
        }
        if (this.highestIds[item.feedId] === void 0 || item.id > this.highestIds[item.feedId]) {
          this.highestIds[item.feedId] = item.id;
        }
        if (this.lowestIds[item.feedId] === void 0 || item.id > this.lowestIds[item.feedId]) {
          return this.lowestIds[item.feedId] = item.id;
        }
      };

      Cache.prototype.getItemsOfFeed = function(feedId) {
        return this.feedCache[feedId];
      };

      Cache.prototype.getFeedIdsOfFolder = function(folderId) {
        this.buildFolderCache(folderId);
        return this.folderCache[folderId];
      };

      Cache.prototype.getImportantItems = function() {
        return this.importantCache;
      };

      Cache.prototype.buildFolderCache = function(id) {
        var feed, _i, _len, _ref, _results;
        if (this.folderCacheLastModified !== this.feedModel.getLastModified()) {
          this.folderCache = {};
          this.folderCacheLastModified = this.feedModel.getLastModified();
        }
        if (this.folderCache[id] === void 0) {
          this.folderCache[id] = [];
          _ref = this.feedModel.getItems();
          _results = [];
          for (_i = 0, _len = _ref.length; _i < _len; _i++) {
            feed = _ref[_i];
            if (feed.folderId === id) {
              _results.push(this.folderCache[id].push(feed.id));
            } else {
              _results.push(void 0);
            }
          }
          return _results;
        }
      };

      Cache.prototype.getFeedsOfFolderId = function(id) {
        this.buildFolderCache(id);
        return this.folderCache[id];
      };

      Cache.prototype.removeItemInArray = function(id, array) {
        var counter, element, removeItemIndex, _i, _len;
        removeItemIndex = null;
        counter = 0;
        for (_i = 0, _len = array.length; _i < _len; _i++) {
          element = array[_i];
          if (element.id === id) {
            removeItemIndex = counter;
            break;
          }
          counter += 1;
        }
        if (removeItemIndex !== null) {
          return array.splice(removeItemIndex, 1);
        }
      };

      Cache.prototype.remove = function(item) {
        this.removeItemInArray(item.id, this.feedCache[item.feedId]);
        return this.removeItemInArray(item.id, this.importantCache);
      };

      Cache.prototype.setImportant = function(item, isImportant) {
        if (isImportant) {
          return this.importantCache.push(item);
        } else {
          return this.removeItemInArray(item.id, this.importantCache);
        }
      };

      Cache.prototype.getHighestId = function(type, id) {
        if (this.isFeed(type)) {
          return this.highestIds[id] || 0;
        } else {
          return this.highestId;
        }
      };

      Cache.prototype.getHighestTimestamp = function(type, id) {
        if (this.isFeed(type)) {
          return this.highestTimestamps[id] || 0;
        } else {
          return this.highestTimestamp;
        }
      };

      Cache.prototype.getLowestId = function(type, id) {
        if (this.isFeed(type)) {
          return this.lowestIds[id] || 0;
        } else {
          return this.lowestId;
        }
      };

      Cache.prototype.getLowestTimestamp = function(type, id) {
        if (this.isFeed(type)) {
          return this.lowestTimestamps[id] || 0;
        } else {
          return this.lowestTimestamp;
        }
      };

      Cache.prototype.isFeed = function(type) {
        return type === this.feedType.Feed;
      };

      return Cache;

    })();
    return Cache;
  });

  /*
  # ownCloud news app
  #
  # @author Alessandro Cosentino
  # @author Bernhard Posselt
  # Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
  # Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
  #
  # This file is licensed under the Affero General Public License version 3 or
  # later.
  #
  # See the COPYING-README file
  #
  */


  angular.module('News').factory('_FeedModel', [
    'Model', function(Model) {
      var FeedModel;
      FeedModel = (function(_super) {

        __extends(FeedModel, _super);

        function FeedModel() {
          FeedModel.__super__.constructor.call(this);
        }

        FeedModel.prototype.add = function(item) {
          return FeedModel.__super__.add.call(this, this.bindAdditional(item));
        };

        FeedModel.prototype.bindAdditional = function(item) {
          if (item.icon === "url()") {
            item.icon = 'url(' + OC.imagePath('news', 'rss.svg') + ')';
          }
          return item;
        };

        return FeedModel;

      })(Model);
      return FeedModel;
    }
  ]);

  /*
  # ownCloud news app
  #
  # @author Alessandro Cosentino
  # @author Bernhard Posselt
  # Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
  # Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
  #
  # This file is licensed under the Affero General Public License version 3 or
  # later.
  #
  # See the COPYING-README file
  #
  */


  angular.module('News').factory('FeedType', function() {
    var feedType;
    return feedType = {
      Feed: 0,
      Folder: 1,
      Starred: 2,
      Subscriptions: 3,
      Shared: 4
    };
  });

  /*
  # ownCloud news app
  #
  # @author Alessandro Cosentino
  # @author Bernhard Posselt
  # Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
  # Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
  #
  # This file is licensed under the Affero General Public License version 3 or
  # later.
  #
  # See the COPYING-README file
  #
  */


  angular.module('News').factory('_FolderModel', [
    'Model', function(Model, $rootScope) {
      var FolderModel;
      FolderModel = (function(_super) {

        __extends(FolderModel, _super);

        function FolderModel() {
          FolderModel.__super__.constructor.call(this);
        }

        return FolderModel;

      })(Model);
      return FolderModel;
    }
  ]);

  /*
  # ownCloud news app
  #
  # @author Alessandro Cosentino
  # @author Bernhard Posselt
  # Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
  # Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
  #
  # This file is licensed under the Affero General Public License version 3 or
  # later.
  #
  # See the COPYING-README file
  #
  */


  angular.module('News').factory('_GarbageRegistry', function() {
    var GarbageRegistry;
    GarbageRegistry = (function() {

      function GarbageRegistry(itemModel) {
        this.itemModel = itemModel;
        this.registeredItemIds = {};
      }

      GarbageRegistry.prototype.register = function(item) {
        var itemId;
        itemId = item.id;
        return this.registeredItemIds[itemId] = item;
      };

      GarbageRegistry.prototype.unregister = function(item) {
        var itemId;
        itemId = item.id;
        return delete this.registeredItemIds[itemId];
      };

      GarbageRegistry.prototype.clear = function() {
        var id, item, _ref;
        _ref = this.registeredItemIds;
        for (id in _ref) {
          item = _ref[id];
          if (!item.keptUnread) {
            this.itemModel.removeById(parseInt(id, 10));
          }
          item.keptUnread = false;
        }
        return this.registeredItemIds = {};
      };

      return GarbageRegistry;

    })();
    return GarbageRegistry;
  });

  /*
  # ownCloud news app
  #
  # @author Alessandro Cosentino
  # @author Bernhard Posselt
  # Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
  # Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
  #
  # This file is licensed under the Affero General Public License version 3 or
  # later.
  #
  # See the COPYING-README file
  #
  */


  angular.module('News').factory('_ItemModel', [
    'Model', function(Model) {
      var ItemModel;
      ItemModel = (function(_super) {

        __extends(ItemModel, _super);

        function ItemModel(cache, feedType) {
          this.cache = cache;
          this.feedType = feedType;
          ItemModel.__super__.constructor.call(this);
        }

        ItemModel.prototype.clearCache = function() {
          this.cache.clear();
          return ItemModel.__super__.clearCache.call(this);
        };

        ItemModel.prototype.add = function(item) {
          item = this.bindAdditional(item);
          if (ItemModel.__super__.add.call(this, item)) {
            return this.cache.add(this.getItemById(item.id));
          }
        };

        ItemModel.prototype.bindAdditional = function(item) {
          item.getRelativeDate = function() {
            return moment.unix(this.date).fromNow();
          };
          item.getAuthorLine = function() {
            if (this.author !== null && this.author.trim() !== "") {
              return "by " + this.author;
            } else {
              return "";
            }
          };
          return item;
        };

        ItemModel.prototype.removeById = function(itemId) {
          var item;
          item = this.getItemById(itemId);
          if (item !== void 0) {
            this.cache.remove(item);
            return ItemModel.__super__.removeById.call(this, itemId);
          }
        };

        ItemModel.prototype.getHighestId = function(type, id) {
          return this.cache.getHighestId(type, id);
        };

        ItemModel.prototype.getHighestTimestamp = function(type, id) {
          return this.cache.getHighestTimestamp(type, id);
        };

        ItemModel.prototype.getLowestId = function(type, id) {
          return this.cache.getLowestId(type, id);
        };

        ItemModel.prototype.getLowestTimestamp = function(type, id) {
          return this.cache.getLowestTimestamp(type, id);
        };

        ItemModel.prototype.getFeedsOfFolderId = function(id) {
          return this.cache.getFeedsOfFolderId(id);
        };

        ItemModel.prototype.getItemsByTypeAndId = function(type, id) {
          var feedId, items, _i, _len, _ref;
          switch (type) {
            case this.feedType.Feed:
              items = this.cache.getItemsOfFeed(id) || [];
              return items;
            case this.feedType.Subscriptions:
              return this.getItems();
            case this.feedType.Folder:
              items = [];
              _ref = this.cache.getFeedIdsOfFolder(id);
              for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                feedId = _ref[_i];
                items = items.concat(this.cache.getItemsOfFeed(feedId) || []);
              }
              return items;
            case this.feedType.Starred:
              return this.cache.getImportantItems();
          }
        };

        ItemModel.prototype.setImportant = function(itemId, isImportant) {
          var item;
          item = this.getItemById(itemId);
          this.cache.setImportant(item, isImportant);
          return item.isImportant = isImportant;
        };

        return ItemModel;

      })(Model);
      return ItemModel;
    }
  ]);

  /*
  # ownCloud news app
  #
  # @author Alessandro Cosentino
  # @author Bernhard Posselt
  # Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
  # Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
  #
  # This file is licensed under the Affero General Public License version 3 or
  # later.
  #
  # See the COPYING-README file
  #
  */


  angular.module('News').factory('_Loading', function() {
    var Loading;
    return Loading = (function() {

      function Loading() {
        this.loading = 0;
      }

      return Loading;

    })();
  });

  /*
  # ownCloud news app
  #
  # @author Alessandro Cosentino
  # @author Bernhard Posselt
  # Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
  # Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
  #
  # This file is licensed under the Affero General Public License version 3 or
  # later.
  #
  # See the COPYING-README file
  #
  */


  angular.module('News').factory('Model', function() {
    var Model;
    Model = (function() {

      function Model() {
        this.clearCache();
      }

      Model.prototype.handle = function(data) {
        var item, _i, _len, _results;
        _results = [];
        for (_i = 0, _len = data.length; _i < _len; _i++) {
          item = data[_i];
          _results.push(this.add(item));
        }
        return _results;
      };

      Model.prototype.clearCache = function() {
        this.items = [];
        this.itemIds = {};
        return this.markAccessed();
      };

      Model.prototype.markAccessed = function() {
        return this.lastAccessed = new Date().getTime();
      };

      Model.prototype.getLastModified = function() {
        return this.lastAccessed;
      };

      Model.prototype.add = function(item) {
        if (this.itemIds[item.id] === void 0) {
          this.items.push(item);
          this.itemIds[item.id] = item;
          this.markAccessed();
          return true;
        } else {
          this.update(item);
          return false;
        }
      };

      Model.prototype.update = function(item) {
        var key, updatedItem, value;
        updatedItem = this.itemIds[item.id];
        for (key in item) {
          value = item[key];
          if (key !== 'id') {
            updatedItem[key] = value;
          }
        }
        return this.markAccessed();
      };

      Model.prototype.removeById = function(id) {
        var counter, item, removeItemIndex, _i, _len, _ref;
        removeItemIndex = null;
        counter = 0;
        _ref = this.items;
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          item = _ref[_i];
          if (item.id === id) {
            removeItemIndex = counter;
            break;
          }
          counter += 1;
        }
        if (removeItemIndex !== null) {
          this.items.splice(removeItemIndex, 1);
          delete this.itemIds[id];
        }
        return this.markAccessed();
      };

      Model.prototype.getItemById = function(id) {
        return this.itemIds[id];
      };

      Model.prototype.getItems = function() {
        return this.items;
      };

      return Model;

    })();
    return Model;
  });

  /*
  # ownCloud news app
  #
  # @author Alessandro Cosentino
  # @author Bernhard Posselt
  # Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
  # Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
  #
  # This file is licensed under the Affero General Public License version 3 or
  # later.
  #
  # See the COPYING-README file
  #
  */


  angular.module('News').factory('_OPMLParser', function() {
    var Feed, Folder, OPMLParser;
    Feed = (function() {

      function Feed(name, url) {
        this.name = name;
        this.url = url;
      }

      Feed.prototype.getName = function() {
        return this.name;
      };

      Feed.prototype.getUrl = function() {
        return this.url;
      };

      Feed.prototype.isFolder = function() {
        return false;
      };

      return Feed;

    })();
    Folder = (function() {

      function Folder(name) {
        this.name = name;
        this.items = [];
      }

      Folder.prototype.add = function(feed) {
        return this.items.push(feed);
      };

      Folder.prototype.getItems = function() {
        return this.items;
      };

      Folder.prototype.getName = function() {
        return this.name;
      };

      Folder.prototype.isFolder = function() {
        return true;
      };

      return Folder;

    })();
    OPMLParser = (function() {

      function OPMLParser() {}

      OPMLParser.prototype.parseXML = function(xml) {
        var $root, $xml, structure;
        $xml = $($.parseXML(xml));
        $root = $xml.find('body');
        structure = new Folder('root');
        this._recursivelyParse($root, structure);
        return structure;
      };

      OPMLParser.prototype._recursivelyParse = function($xml, structure) {
        var $outline, feed, folder, outline, _i, _len, _ref, _results;
        _ref = $xml.children('outline');
        _results = [];
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          outline = _ref[_i];
          $outline = $(outline);
          if ($outline.attr('type') !== void 0) {
            feed = new Feed($outline.attr('text'), $outline.attr('xmlUrl'));
            _results.push(structure.add(feed));
          } else {
            folder = new Folder($outline.attr('text'));
            structure.add(folder);
            _results.push(this._recursivelyParse($outline, folder));
          }
        }
        return _results;
      };

      return OPMLParser;

    })();
    return OPMLParser;
  });

  /*
  # ownCloud news app
  #
  # @author Alessandro Cosentino
  # @author Bernhard Posselt
  # Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
  # Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
  #
  # This file is licensed under the Affero General Public License version 3 or
  # later.
  #
  # See the COPYING-README file
  #
  */


  angular.module('News').factory('Persistence', function() {
    var Persistence;
    return Persistence = (function() {

      function Persistence(appName, $http) {
        this.appName = appName;
        this.$http = $http;
        this.appInitialized = false;
        this.shelvedRequests = [];
      }

      Persistence.prototype.setInitialized = function(isInitialized) {
        if (isInitialized) {
          this.executePostRequests();
        }
        return this.appInitialized = isInitialized;
      };

      Persistence.prototype.executePostRequests = function() {
        var request, _i, _len, _ref;
        _ref = this.shelvedRequests;
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          request = _ref[_i];
          this.post(request.route, request.data, request.callback);
          console.log(request);
        }
        return this.shelvedRequests = [];
      };

      Persistence.prototype.isInitialized = function() {
        return this.appInitialized;
      };

      Persistence.prototype.post = function(route, data, callback, errorCallback, init, contentType) {
        var headers, request, url;
        if (data == null) {
          data = {};
        }
        if (init == null) {
          init = false;
        }
        if (contentType == null) {
          contentType = 'application/x-www-form-urlencoded';
        }
        if (this.isInitialized === false && init === false) {
          request = {
            route: route,
            data: data,
            callback: callback
          };
          this.shelvedRequests.push(request);
          return;
        }
        if (!callback) {
          callback = function() {};
        }
        if (!errorCallback) {
          errorCallback = function() {};
        }
        url = OC.Router.generate("news_ajax_" + route);
        data = $.param(data);
        headers = {
          requesttoken: oc_requesttoken,
          'Content-Type': 'application/x-www-form-urlencoded'
        };
        return this.$http.post(url, data, {
          headers: headers
        }).success(function(data, status, headers, config) {
          if (data.status === "error") {
            return errorCallback(data.msg);
          } else {
            return callback(data);
          }
        }).error(function(data, status, headers, config) {
          console.warn('Error occured: ');
          console.warn(status);
          console.warn(headers);
          return console.warn(config);
        });
      };

      return Persistence;

    })();
  });

  /*
  # ownCloud news app
  #
  # @author Alessandro Cosentino
  # @author Bernhard Posselt
  # Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
  # Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
  #
  # This file is licensed under the Affero General Public License version 3 or
  # later.
  #
  # See the COPYING-README file
  #
  */


  angular.module('News').factory('_PersistenceNews', [
    'Persistence', function(Persistence) {
      var PersistenceNews;
      PersistenceNews = (function(_super) {

        __extends(PersistenceNews, _super);

        function PersistenceNews($http, $rootScope, loading, publisher) {
          this.$rootScope = $rootScope;
          this.loading = loading;
          this.publisher = publisher;
          PersistenceNews.__super__.constructor.call(this, 'news', $http);
        }

        PersistenceNews.prototype.updateModels = function(data) {
          var type, value, _results;
          _results = [];
          for (type in data) {
            value = data[type];
            _results.push(this.publisher.publish(type, value));
          }
          return _results;
        };

        PersistenceNews.prototype.loadInitial = function() {
          var _this = this;
          this.loading.loading += 1;
          return OC.Router.registerLoadedCallback(function() {
            return _this.post('init', {}, function(json) {
              _this.loading.loading -= 1;
              _this.updateModels(json.data);
              _this.$rootScope.$broadcast('triggerHideRead');
              return _this.setInitialized(true);
            }, null, true);
          });
        };

        PersistenceNews.prototype.loadFeed = function(type, id, latestFeedId, latestTimestamp, limit) {
          var data,
            _this = this;
          if (limit == null) {
            limit = 20;
          }
          data = {
            type: type,
            id: id,
            latestFeedId: latestFeedId,
            latestTimestamp: latestTimestamp,
            limit: limit
          };
          this.loading.loading += 1;
          return this.post('loadfeed', data, function(json) {
            _this.loading.loading -= 1;
            return _this.updateModels(json.data);
          });
        };

        PersistenceNews.prototype.createFeed = function(feedUrl, folderId, onSuccess, onError) {
          var data,
            _this = this;
          data = {
            feedUrl: feedUrl,
            folderId: folderId
          };
          return this.post('createfeed', data, function(json) {
            onSuccess(json.data);
            return _this.updateModels(json.data);
          }, onError);
        };

        PersistenceNews.prototype.deleteFeed = function(feedId, onSuccess) {
          var data;
          data = {
            feedId: feedId
          };
          return this.post('deletefeed', data, onSuccess);
        };

        PersistenceNews.prototype.moveFeedToFolder = function(feedId, folderId) {
          var data;
          data = {
            feedId: feedId,
            folderId: folderId
          };
          return this.post('movefeedtofolder', data);
        };

        PersistenceNews.prototype.createFolder = function(folderName, onSuccess) {
          var data,
            _this = this;
          data = {
            folderName: folderName
          };
          return this.post('createfolder', data, function(json) {
            onSuccess(json.data);
            return _this.updateModels(json.data);
          });
        };

        PersistenceNews.prototype.deleteFolder = function(folderId) {
          var data;
          data = {
            folderId: folderId
          };
          return this.post('deletefolder', data);
        };

        PersistenceNews.prototype.changeFolderName = function(folderId, newFolderName) {
          var data;
          data = {
            folderId: folderId,
            newFolderName: newFolderName
          };
          return this.post('folderName', data);
        };

        PersistenceNews.prototype.showAll = function(isShowAll) {
          var data;
          data = {
            showAll: isShowAll
          };
          return this.post('setshowall', data);
        };

        PersistenceNews.prototype.markRead = function(itemId, isRead) {
          var data, status;
          if (isRead) {
            status = 'read';
          } else {
            status = 'unread';
          }
          data = {
            itemId: itemId,
            status: status
          };
          return this.post('setitemstatus', data);
        };

        PersistenceNews.prototype.setImportant = function(itemId, isImportant) {
          var data, status;
          if (isImportant) {
            status = 'important';
          } else {
            status = 'unimportant';
          }
          data = {
            itemId: itemId,
            status: status
          };
          return this.post('setitemstatus', data);
        };

        PersistenceNews.prototype.collapseFolder = function(folderId, value) {
          var data;
          data = {
            folderId: folderId,
            opened: value
          };
          return this.post('collapsefolder', data);
        };

        PersistenceNews.prototype.updateFeed = function(feedId) {
          var data,
            _this = this;
          data = {
            feedId: feedId
          };
          return this.post('updatefeed', data, function(json) {
            return _this.updateModels(json.data);
          });
        };

        PersistenceNews.prototype.setAllItemsRead = function(feedId, mostRecentItemId) {
          var data;
          data = {
            feedId: feedId,
            mostRecentItemId: mostRecentItemId
          };
          return this.post('setallitemsread', data);
        };

        return PersistenceNews;

      })(Persistence);
      return PersistenceNews;
    }
  ]);

  /*
  # ownCloud news app
  #
  # @author Alessandro Cosentino
  # @author Bernhard Posselt
  # Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
  # Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
  #
  # This file is licensed under the Affero General Public License version 3 or
  # later.
  #
  # See the COPYING-README file
  #
  */


  angular.module('News').factory('_Publisher', function() {
    var Publisher;
    Publisher = (function() {

      function Publisher() {
        this.subscriptions = {};
      }

      Publisher.prototype.subscribeTo = function(type, object) {
        var _base;
        (_base = this.subscriptions)[type] || (_base[type] = []);
        return this.subscriptions[type].push(object);
      };

      Publisher.prototype.publish = function(type, message) {
        var subscriber, _i, _len, _ref, _results;
        _ref = this.subscriptions[type] || [];
        _results = [];
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          subscriber = _ref[_i];
          _results.push(subscriber.handle(message));
        }
        return _results;
      };

      return Publisher;

    })();
    return Publisher;
  });

  /*
  # ownCloud news app
  #
  # @author Alessandro Cosentino
  # @author Bernhard Posselt
  # Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
  # Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
  #
  # This file is licensed under the Affero General Public License version 3 or
  # later.
  #
  # See the COPYING-README file
  #
  */


  angular.module('News').factory('Loading', [
    '_Loading', function(_Loading) {
      return new _Loading();
    }
  ]);

  angular.module('News').factory('ActiveFeed', [
    '_ActiveFeed', 'Publisher', function(_ActiveFeed, Publisher) {
      var model;
      model = new _ActiveFeed();
      Publisher.subscribeTo('activeFeed', model);
      return model;
    }
  ]);

  angular.module('News').factory('ShowAll', [
    '_ShowAll', 'Publisher', function(_ShowAll, Publisher) {
      var model;
      model = new _ShowAll();
      Publisher.subscribeTo('showAll', model);
      return model;
    }
  ]);

  angular.module('News').factory('StarredCount', [
    '_StarredCount', 'Publisher', function(_StarredCount, Publisher) {
      var model;
      model = new _StarredCount();
      Publisher.subscribeTo('starredCount', model);
      return model;
    }
  ]);

  angular.module('News').factory('FeedModel', [
    '_FeedModel', 'Publisher', function(_FeedModel, Publisher) {
      var model;
      model = new _FeedModel();
      Publisher.subscribeTo('feeds', model);
      return model;
    }
  ]);

  angular.module('News').factory('FolderModel', [
    '_FolderModel', 'Publisher', function(_FolderModel, Publisher) {
      var model;
      model = new _FolderModel();
      Publisher.subscribeTo('folders', model);
      return model;
    }
  ]);

  angular.module('News').factory('ItemModel', [
    '_ItemModel', 'Publisher', 'Cache', 'FeedType', function(_ItemModel, Publisher, Cache, FeedType) {
      var model;
      model = new _ItemModel(Cache, FeedType);
      Publisher.subscribeTo('items', model);
      return model;
    }
  ]);

  angular.module('News').factory('Cache', [
    '_Cache', 'FeedType', 'FeedModel', 'FolderModel', function(_Cache, FeedType, FeedModel, FolderModel) {
      return new _Cache(FeedType, FeedModel, FolderModel);
    }
  ]);

  angular.module('News').factory('PersistenceNews', [
    '_PersistenceNews', '$http', '$rootScope', 'Loading', 'Publisher', function(_PersistenceNews, $http, $rootScope, Loading, Publisher) {
      return new _PersistenceNews($http, $rootScope, Loading, Publisher);
    }
  ]);

  angular.module('News').factory('GarbageRegistry', [
    '_GarbageRegistry', 'ItemModel', function(_GarbageRegistry, ItemModel) {
      return new _GarbageRegistry(ItemModel);
    }
  ]);

  angular.module('News').factory('Publisher', [
    '_Publisher', function(_Publisher) {
      return new _Publisher();
    }
  ]);

  angular.module('News').factory('OPMLParser', [
    '_OPMLParser', function(_OPMLParser) {
      return new _OPMLParser();
    }
  ]);

  /*
  # ownCloud news app
  #
  # @author Alessandro Cosentino
  # @author Bernhard Posselt
  # Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
  # Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
  #
  # This file is licensed under the Affero General Public License version 3 or
  # later.
  #
  # See the COPYING-README file
  #
  */


  angular.module('News').factory('_ShowAll', function() {
    var ShowAll;
    ShowAll = (function() {

      function ShowAll() {
        this.showAll = false;
      }

      ShowAll.prototype.handle = function(data) {
        return this.showAll = data;
      };

      return ShowAll;

    })();
    return ShowAll;
  });

  /*
  # ownCloud news app
  #
  # @author Alessandro Cosentino
  # @author Bernhard Posselt
  # Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
  # Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
  #
  # This file is licensed under the Affero General Public License version 3 or
  # later.
  #
  # See the COPYING-README file
  #
  */


  angular.module('News').factory('_StarredCount', function() {
    var StarredCount;
    StarredCount = (function() {

      function StarredCount() {
        this.count = 0;
      }

      StarredCount.prototype.handle = function(data) {
        return this.count = data;
      };

      return StarredCount;

    })();
    return StarredCount;
  });

  /*
  # ownCloud news app
  #
  # @author Alessandro Cosentino
  # @author Bernhard Posselt
  # Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
  # Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
  #
  # This file is licensed under the Affero General Public License version 3 or
  # later.
  #
  # See the COPYING-README file
  #
  */


  angular.module('News').factory('_AddNewController', [
    'Controller', function(Controller) {
      var AddNewController;
      return AddNewController = (function(_super) {

        __extends(AddNewController, _super);

        function AddNewController() {
          return AddNewController.__super__.constructor.apply(this, arguments);
        }

        return AddNewController;

      })(Controller);
    }
  ]);

  /*
  # ownCloud news app
  #
  # @author Alessandro Cosentino
  # @author Bernhard Posselt
  # Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
  # Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
  #
  # This file is licensed under the Affero General Public License version 3 or
  # later.
  #
  # See the COPYING-README file
  #
  */


  angular.module('News').factory('Controller', function() {
    var Controller;
    return Controller = (function() {

      function Controller() {}

      return Controller;

    })();
  });

  /*
  # ownCloud news app
  #
  # @author Alessandro Cosentino
  # @author Bernhard Posselt
  # Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
  # Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
  #
  # This file is licensed under the Affero General Public License version 3 or
  # later.
  #
  # See the COPYING-README file
  #
  */


  angular.module('News').controller('SettingsController', [
    '_SettingsController', '$scope', '$rootScope', 'ShowAll', 'PersistenceNews', 'FolderModel', 'FeedModel', 'OPMLParser', function(_SettingsController, $scope, $rootScope, ShowAll, PersistenceNews, FolderModel, FeedModel, OPMLParser) {
      return new _SettingsController($scope, $rootScope, PersistenceNews, OPMLParser);
    }
  ]);

  angular.module('News').controller('ItemController', [
    '_ItemController', '$scope', 'ItemModel', 'ActiveFeed', 'PersistenceNews', 'FeedModel', 'StarredCount', 'GarbageRegistry', 'ShowAll', 'Loading', '$rootScope', 'FeedType', function(_ItemController, $scope, ItemModel, ActiveFeed, PersistenceNews, FeedModel, StarredCount, GarbageRegistry, ShowAll, Loading, $rootScope, FeedType) {
      return new _ItemController($scope, ItemModel, ActiveFeed, PersistenceNews, FeedModel, StarredCount, GarbageRegistry, ShowAll, Loading, $rootScope, FeedType);
    }
  ]);

  angular.module('News').controller('FeedController', [
    '_FeedController', '$scope', 'FeedModel', 'FeedType', 'FolderModel', 'ActiveFeed', 'PersistenceNews', 'StarredCount', 'ShowAll', 'ItemModel', 'GarbageRegistry', '$rootScope', 'Loading', 'Config', function(_FeedController, $scope, FeedModel, FeedType, FolderModel, ActiveFeed, PersistenceNews, StarredCount, ShowAll, ItemModel, GarbageRegistry, $rootScope, Loading, Config) {
      return new _FeedController($scope, FeedModel, FolderModel, FeedType, ActiveFeed, PersistenceNews, StarredCount, ShowAll, ItemModel, GarbageRegistry, $rootScope, Loading, Config);
    }
  ]);

  angular.module('News').controller('AddNewController', [
    '_AddNewController', '$scope', function(_AddNewController, $scope) {
      return new _AddNewController($scope);
    }
  ]);

  /*
  # ownCloud news app
  #
  # @author Alessandro Cosentino
  # @author Bernhard Posselt
  # Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
  # Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
  #
  # This file is licensed under the Affero General Public License version 3 or
  # later.
  #
  # See the COPYING-README file
  #
  */


  angular.module('News').factory('_FeedController', [
    'Controller', function(Controller) {
      var FeedController;
      FeedController = (function(_super) {

        __extends(FeedController, _super);

        function FeedController($scope, feedModel, folderModel, feedType, activeFeed, persistence, starredCount, showAll, itemModel, garbageRegistry, $rootScope, loading, config) {
          var _this = this;
          this.$scope = $scope;
          this.feedModel = feedModel;
          this.folderModel = folderModel;
          this.feedType = feedType;
          this.activeFeed = activeFeed;
          this.persistence = persistence;
          this.starredCount = starredCount;
          this.showAll = showAll;
          this.itemModel = itemModel;
          this.garbageRegistry = garbageRegistry;
          this.$rootScope = $rootScope;
          this.loading = loading;
          this.config = config;
          this.showSubscriptions = true;
          this.$scope.feeds = this.feedModel.getItems();
          this.$scope.folders = this.folderModel.getItems();
          this.$scope.feedType = this.feedType;
          this.$scope.getShowAll = function() {
            return _this.showAll.showAll;
          };
          this.$scope.setShowAll = function(value) {
            _this.showAll.showAll = value;
            _this.persistence.showAll(value);
            return _this.$rootScope.$broadcast('triggerHideRead');
          };
          this.$scope.addFeed = function(url, folder) {
            var feed, folderId, onError, onSuccess, _i, _len, _ref;
            _this.$scope.feedEmptyError = false;
            _this.$scope.feedExistsError = false;
            _this.$scope.feedError = false;
            if (url === void 0 || url.trim() === '') {
              _this.$scope.feedEmptyError = true;
            } else {
              url = url.trim();
              _ref = _this.feedModel.getItems();
              for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                feed = _ref[_i];
                if (url === feed.url) {
                  _this.$scope.feedExistsError = true;
                }
              }
            }
            if (!(_this.$scope.feedEmptyError || _this.$scope.feedExistsError)) {
              if (folder === void 0) {
                folderId = 0;
              } else {
                folderId = folder.id;
              }
              _this.$scope.adding = true;
              onSuccess = function() {
                _this.$scope.feedUrl = '';
                return _this.$scope.adding = false;
              };
              onError = function() {
                _this.$scope.feedError = true;
                return _this.$scope.adding = false;
              };
              return _this.persistence.createFeed(url, folderId, onSuccess, onError);
            }
          };
          this.$scope.addFolder = function(name) {
            var folder, onSuccess, _i, _len, _ref;
            _this.$scope.folderEmptyError = false;
            _this.$scope.folderExistsError = false;
            if (name === void 0 || name.trim() === '') {
              _this.$scope.folderEmptyError = true;
            } else {
              name = name.trim();
              _ref = _this.folderModel.getItems();
              for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                folder = _ref[_i];
                if (name.toLowerCase() === folder.name.toLowerCase()) {
                  _this.$scope.folderExistsError = true;
                }
              }
            }
            if (!(_this.$scope.folderEmptyError || _this.$scope.folderExistsError)) {
              _this.addingFolder = true;
              onSuccess = function() {
                _this.$scope.folderName = '';
                return _this.addingFolder = false;
              };
              return _this.persistence.createFolder(name, onSuccess);
            }
          };
          this.$scope.toggleFolder = function(folderId) {
            var folder;
            folder = _this.folderModel.getItemById(folderId);
            folder.open = !folder.open;
            return _this.persistence.collapseFolder(folder.id, folder.open);
          };
          this.$scope.isFeedActive = function(type, id) {
            if (type === _this.activeFeed.type && id === _this.activeFeed.id) {
              return true;
            } else {
              return false;
            }
          };
          this.$scope.loadFeed = function(type, id) {
            return _this.loadFeed(type, id);
          };
          this.$scope.getUnreadCount = function(type, id) {
            var count;
            count = _this.getUnreadCount(type, id);
            if (count > 999) {
              return "999+";
            } else {
              return count;
            }
          };
          this.$scope.renameFolder = function() {
            return alert('not implemented yet, needs better solution');
          };
          this.$scope.triggerHideRead = function() {
            return _this.triggerHideRead();
          };
          this.$scope.isShown = function(type, id) {
            switch (type) {
              case _this.feedType.Subscriptions:
                return _this.showSubscriptions;
              case _this.feedType.Starred:
                return _this.starredCount.count > 0;
            }
          };
          this.$scope["delete"] = function(type, id) {
            switch (type) {
              case _this.feedType.Folder:
                _this.folderModel.removeById(id);
                return _this.persistence.deleteFolder(id);
              case _this.feedType.Feed:
                _this.feedModel.removeById(id);
                return _this.persistence.deleteFeed(id);
            }
          };
          this.$scope.markAllRead = function(type, id) {
            var feed, feedId, item, itemId, mostRecentItemId, _i, _j, _len, _len1, _ref, _ref1, _ref2, _ref3, _ref4, _results, _results1;
            switch (type) {
              case _this.feedType.Feed:
                _ref = _this.itemModel.getItemsByTypeAndId(type, id);
                for (itemId in _ref) {
                  item = _ref[itemId];
                  item.isRead = true;
                }
                feed = _this.feedModel.getItemById(id);
                feed.unreadCount = 0;
                mostRecentItemId = _this.itemModel.getHighestId(type, id);
                return _this.persistence.setAllItemsRead(feed.id, mostRecentItemId);
              case _this.feedType.Folder:
                _ref1 = _this.itemModel.getItemsByTypeAndId(type, id);
                for (itemId in _ref1) {
                  item = _ref1[itemId];
                  item.isRead = true;
                }
                _ref2 = _this.itemModel.getFeedsOfFolderId(id);
                _results = [];
                for (_i = 0, _len = _ref2.length; _i < _len; _i++) {
                  feedId = _ref2[_i];
                  feed = _this.feedModel.getItemById(feedId);
                  feed.unreadCount = 0;
                  mostRecentItemId = _this.itemModel.getHighestId(type, feedId);
                  _results.push(_this.persistence.setAllItemsRead(feedId, mostRecentItemId));
                }
                return _results;
                break;
              case _this.feedType.Subscriptions:
                _ref3 = _this.itemModel.getItemsByTypeAndId(type, id);
                for (itemId in _ref3) {
                  item = _ref3[itemId];
                  item.isRead = true;
                }
                _ref4 = _this.feedModel.getItems();
                _results1 = [];
                for (_j = 0, _len1 = _ref4.length; _j < _len1; _j++) {
                  feed = _ref4[_j];
                  feed.unreadCount = 0;
                  mostRecentItemId = _this.itemModel.getHighestId(type, feed.id);
                  _results1.push(_this.persistence.setAllItemsRead(feed.id, mostRecentItemId));
                }
                return _results1;
            }
          };
          this.$scope.$on('triggerHideRead', function() {
            _this.itemModel.clearCache();
            _this.triggerHideRead();
            return _this.loadFeed(activeFeed.type, activeFeed.id);
          });
          this.$scope.$on('loadFeed', function(scope, params) {
            return _this.loadFeed(params.type, params.id);
          });
          this.$scope.$on('moveFeedToFolder', function(scope, params) {
            return _this.moveFeedToFolder(params.feedId, params.folderId);
          });
          setInterval(function() {
            return _this.updateFeeds();
          }, this.config.FeedUpdateInterval);
        }

        FeedController.prototype.updateFeeds = function() {
          var feed, _i, _len, _ref, _results;
          _ref = this.feedModel.getItems();
          _results = [];
          for (_i = 0, _len = _ref.length; _i < _len; _i++) {
            feed = _ref[_i];
            _results.push(this.persistence.updateFeed(feed.id));
          }
          return _results;
        };

        FeedController.prototype.moveFeedToFolder = function(feedId, folderId) {
          var feed;
          feed = this.feedModel.getItemById(feedId);
          if (feed.folderId !== folderId) {
            feed.folderId = folderId;
            this.feedModel.markAccessed();
            return this.persistence.moveFeedToFolder(feedId, folderId);
          }
        };

        FeedController.prototype.loadFeed = function(type, id) {
          if (type !== this.activeFeed.type || id !== this.activeFeed.id) {
            if (!(type === this.feedType.Feed && this.activeFeed.type === this.feedType.Feed)) {
              this.itemModel.clearCache();
            }
          }
          this.activeFeed.id = id;
          this.activeFeed.type = type;
          this.$scope.triggerHideRead();
          return this.persistence.loadFeed(type, id, this.itemModel.getHighestId(type, id), this.itemModel.getHighestTimestamp(type, id), this.config.initialLoadedItemsNr);
        };

        FeedController.prototype.triggerHideRead = function() {
          var feed, folder, preventParentFolder, _i, _j, _len, _len1, _ref, _ref1;
          preventParentFolder = 0;
          _ref = this.feedModel.getItems();
          for (_i = 0, _len = _ref.length; _i < _len; _i++) {
            feed = _ref[_i];
            if (this.showAll.showAll === false && this.getUnreadCount(this.feedType.Feed, feed.id) === 0) {
              if (this.activeFeed.type === this.feedType.Feed && this.activeFeed.id === feed.id) {
                feed.show = true;
                preventParentFolder = feed.folderId;
              } else {
                feed.show = false;
              }
            } else {
              feed.show = true;
            }
          }
          _ref1 = this.folderModel.getItems();
          for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
            folder = _ref1[_j];
            if (this.showAll.showAll === false && this.getUnreadCount(this.feedType.Folder, folder.id) === 0) {
              if ((this.activeFeed.type === this.feedType.Folder && this.activeFeed.id === folder.id) || preventParentFolder === folder.id) {
                folder.show = true;
              } else {
                folder.show = false;
              }
            } else {
              folder.show = true;
            }
          }
          if (this.showAll.showAll === false && this.getUnreadCount(this.feedType.Subscriptions, 0) === 0) {
            if (this.activeFeed.type === this.feedType.Subscriptions) {
              this.showSubscriptions = true;
            } else {
              this.showSubscriptions = false;
            }
          } else {
            this.showSubscriptions = true;
          }
          if (this.showAll.showAll === false && this.getUnreadCount(this.feedType.Starred, 0) === 0) {
            if (this.activeFeed.type === this.feedType.Starred) {
              this.showStarred = true;
            } else {
              this.showStarred = false;
            }
          } else {
            this.showStarred = true;
          }
          return this.garbageRegistry.clear();
        };

        FeedController.prototype.getUnreadCount = function(type, id) {
          var counter, feed, _i, _j, _len, _len1, _ref, _ref1;
          switch (type) {
            case this.feedType.Feed:
              return this.feedModel.getItemById(id).unreadCount;
            case this.feedType.Folder:
              counter = 0;
              _ref = this.feedModel.getItems();
              for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                feed = _ref[_i];
                if (feed.folderId === id) {
                  counter += feed.unreadCount;
                }
              }
              return counter;
            case this.feedType.Starred:
              return this.starredCount.count;
            case this.feedType.Subscriptions:
              counter = 0;
              _ref1 = this.feedModel.getItems();
              for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
                feed = _ref1[_j];
                counter += feed.unreadCount;
              }
              return counter;
          }
        };

        return FeedController;

      })(Controller);
      return FeedController;
    }
  ]);

  /*
  # ownCloud news app
  #
  # @author Alessandro Cosentino
  # @author Bernhard Posselt
  # Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
  # Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
  #
  # This file is licensed under the Affero General Public License version 3 or
  # later.
  #
  # See the COPYING-README file
  #
  */


  angular.module('News').factory('_ItemController', [
    'Controller', function(Controller) {
      var ItemController;
      ItemController = (function(_super) {

        __extends(ItemController, _super);

        function ItemController($scope, itemModel, activeFeed, persistence, feedModel, starredCount, garbageRegistry, showAll, loading, $rootScope, feedType) {
          var _this = this;
          this.$scope = $scope;
          this.itemModel = itemModel;
          this.activeFeed = activeFeed;
          this.persistence = persistence;
          this.feedModel = feedModel;
          this.starredCount = starredCount;
          this.garbageRegistry = garbageRegistry;
          this.showAll = showAll;
          this.loading = loading;
          this.$rootScope = $rootScope;
          this.feedType = feedType;
          this.batchSize = 4;
          this.loaderQueue = 0;
          this.$scope.getItems = function(type, id) {
            return _this.itemModel.getItemsByTypeAndId(type, id);
          };
          this.$scope.items = this.itemModel.getItems();
          this.$scope.loading = this.loading;
          this.$scope.scroll = function() {};
          this.$scope.activeFeed = this.activeFeed;
          this.$scope.$on('read', function(scope, params) {
            return _this.$scope.markRead(params.id, params.feed);
          });
          this.$scope.loadFeed = function(feedId) {
            var params;
            params = {
              id: feedId,
              type: _this.feedType.Feed
            };
            return _this.$rootScope.$broadcast('loadFeed', params);
          };
          this.$scope.markRead = function(itemId, feedId) {
            var feed, item;
            item = _this.itemModel.getItemById(itemId);
            feed = _this.feedModel.getItemById(feedId);
            if (!item.keptUnread && !item.isRead) {
              item.isRead = true;
              feed.unreadCount -= 1;
              if (!_this.showAll.showAll) {
                _this.garbageRegistry.register(item);
              }
              return _this.persistence.markRead(itemId, true);
            }
          };
          this.$scope.keepUnread = function(itemId, feedId) {
            var feed, item;
            item = _this.itemModel.getItemById(itemId);
            feed = _this.feedModel.getItemById(feedId);
            item.keptUnread = !item.keptUnread;
            if (item.isRead) {
              item.isRead = false;
              feed.unreadCount += 1;
              return _this.persistence.markRead(itemId, false);
            }
          };
          this.$scope.isKeptUnread = function(itemId) {
            return _this.itemModel.getItemById(itemId).keptUnread;
          };
          this.$scope.toggleImportant = function(itemId) {
            var item;
            item = _this.itemModel.getItemById(itemId);
            _this.itemModel.setImportant(itemId, !item.isImportant);
            if (item.isImportant) {
              _this.starredCount.count += 1;
            } else {
              _this.starredCount.count -= 1;
            }
            return _this.persistence.setImportant(itemId, item.isImportant);
          };
        }

        return ItemController;

      })(Controller);
      return ItemController;
    }
  ]);

  /*
  # ownCloud news app
  #
  # @author Alessandro Cosentino
  # @author Bernhard Posselt
  # Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
  # Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
  #
  # This file is licensed under the Affero General Public License version 3 or
  # later.
  #
  # See the COPYING-README file
  #
  */


  angular.module('News').factory('_SettingsController', [
    'Controller', function(Controller) {
      var SettingsController;
      SettingsController = (function(_super) {

        __extends(SettingsController, _super);

        function SettingsController($scope, $rootScope, persistence, opmlParser) {
          var _this = this;
          this.$scope = $scope;
          this.$rootScope = $rootScope;
          this.persistence = persistence;
          this.opmlParser = opmlParser;
          this.add = false;
          this.settings = false;
          this.addingFeed = false;
          this.addingFolder = false;
          this.$scope.$on('readFile', function(scope, fileContent) {
            var structure;
            structure = _this.opmlParser.parseXML(fileContent);
            return _this.parseOPMLStructure(structure);
          });
          this.$scope.$on('hidesettings', function() {
            return _this.$scope.showSettings = false;
          });
        }

        SettingsController.prototype.parseOPMLStructure = function(structure, folderId) {
          var item, onError, onSuccess, _i, _len, _ref, _results,
            _this = this;
          if (folderId == null) {
            folderId = 0;
          }
          _ref = structure.getItems();
          _results = [];
          for (_i = 0, _len = _ref.length; _i < _len; _i++) {
            item = _ref[_i];
            if (item.isFolder()) {
              onSuccess = function(data) {
                console.log(data);
                folderId = data.folders[0].id;
                return _this.parseOPMLStructure(item, folderId);
              };
              _results.push(this.persistence.createFolder(item.getName(), onSuccess));
            } else {
              onSuccess = function() {};
              onError = function() {};
              _results.push(this.persistence.createFeed(item.getUrl(), folderId, onSuccess, onError));
            }
          }
          return _results;
        };

        return SettingsController;

      })(Controller);
      return SettingsController;
    }
  ]);

  /*
  # ownCloud news app
  #
  # @author Alessandro Cosentino
  # @author Bernhard Posselt
  # Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
  # Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
  #
  # This file is licensed under the Affero General Public License version 3 or
  # later.
  #
  # See the COPYING-README file
  #
  */


  /*
  Turns a normal select into a folder select with the ability to create new folders
  */


  angular.module('News').directive('addFolderSelect', [
    '$rootScope', function() {
      return function(scope, elm, attr) {
        var options;
        options = {
          singleSelect: true,
          selectedFirst: true,
          createText: $(elm).data('create'),
          createdCallback: function(selected, value) {
            console.log(selected);
            return console.log(value);
          }
        };
        return $(elm).multiSelect(options);
      };
    }
  ]);

  /*
  # ownCloud news app
  #
  # @author Alessandro Cosentino
  # @author Bernhard Posselt
  # Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
  # Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
  #
  # This file is licensed under the Affero General Public License version 3 or
  # later.
  #
  # See the COPYING-README file
  #
  */


  angular.module('News').directive('draggable', function() {
    return function(scope, elm, attr) {
      var details;
      details = {
        revert: true,
        stack: '> li',
        zIndex: 1000,
        axis: 'y',
        helper: 'clone'
      };
      return $(elm).draggable(details);
    };
  });

  /*
  # ownCloud news app
  #
  # @author Alessandro Cosentino
  # @author Bernhard Posselt
  # Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
  # Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
  #
  # This file is licensed under the Affero General Public License version 3 or
  # later.
  #
  # See the COPYING-README file
  #
  */


  angular.module('News').directive('droppable', [
    '$rootScope', function($rootScope) {
      return function(scope, elm, attr) {
        var $elem, details;
        $elem = $(elm);
        details = {
          accept: '.feed',
          hoverClass: 'drag-and-drop',
          greedy: true,
          drop: function(event, ui) {
            var data;
            $('.drag-and-drop').removeClass('drag-and-drop');
            data = {
              folderId: parseInt($elem.data('id'), 10),
              feedId: parseInt($(ui.draggable).data('id'), 10)
            };
            $rootScope.$broadcast('moveFeedToFolder', data);
            return scope.$apply(attr.droppable);
          }
        };
        return $elem.droppable(details);
      };
    }
  ]);

  /*
  # ownCloud news app
  #
  # @author Alessandro Cosentino
  # @author Bernhard Posselt
  # Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
  # Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
  #
  # This file is licensed under the Affero General Public License version 3 or
  # later.
  #
  # See the COPYING-README file
  #
  */


  angular.module('News').directive('feedNavigation', function() {
    return function(scope, elm, attr) {
      var jumpTo, jumpToNextItem, jumpToPreviousItem;
      jumpTo = function($scrollArea, $item) {
        var position;
        position = $item.offset().top - $scrollArea.offset().top + $scrollArea.scrollTop();
        return $scrollArea.scrollTop(position);
      };
      jumpToPreviousItem = function(scrollArea) {
        var $item, $items, $previous, $scrollArea, item, notJumped, _i, _len;
        $scrollArea = $(scrollArea);
        $items = $scrollArea.find('.feed_item');
        notJumped = true;
        for (_i = 0, _len = $items.length; _i < _len; _i++) {
          item = $items[_i];
          $item = $(item);
          if ($item.position().top >= 0) {
            $previous = $item.prev();
            if ($previous.length > 0) {
              jumpTo($scrollArea, $previous);
            }
            notJumped = false;
            break;
          }
        }
        if ($items.length > 0 && notJumped) {
          return jumpTo($scrollArea, $items.last());
        }
      };
      jumpToNextItem = function(scrollArea) {
        var $item, $items, $scrollArea, item, _i, _len, _results;
        $scrollArea = $(scrollArea);
        $items = $scrollArea.find('.feed_item');
        _results = [];
        for (_i = 0, _len = $items.length; _i < _len; _i++) {
          item = $items[_i];
          $item = $(item);
          if ($item.position().top > 1) {
            jumpTo($scrollArea, $item);
            break;
          } else {
            _results.push(void 0);
          }
        }
        return _results;
      };
      return $(document).keydown(function(e) {
        var focused, scrollArea;
        focused = $(':focus');
        if (!(focused.is('input') || focused.is('select') || focused.is('textarea') || focused.is('checkbox') || focused.is('button'))) {
          scrollArea = elm;
          if (e.keyCode === 74 || e.keyCode === 39) {
            return jumpToNextItem(scrollArea);
          } else if (e.keyCode === 75 || e.keyCode === 37) {
            return jumpToPreviousItem(scrollArea);
          }
        }
      });
    };
  });

  /*
  # ownCloud news app
  #
  # @author Alessandro Cosentino
  # @author Bernhard Posselt
  # Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
  # Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
  #
  # This file is licensed under the Affero General Public License version 3 or
  # later.
  #
  # See the COPYING-README file
  #
  */


  /*
  # This is used to signal the settings bar that the app has been focused and that
  # it should hide
  */


  angular.module('News').directive('hideSettingsWhenFocusLost', [
    '$rootScope', function($rootScope) {
      return function(scope, elm, attr) {
        $(document.body).click(function() {
          $rootScope.$broadcast('hidesettings');
          return scope.$apply(attr.hideSettingsWhenFocusLost);
        });
        return $(elm).click(function(e) {
          return e.stopPropagation();
        });
      };
    }
  ]);

  /*
  # ownCloud news app
  #
  # @author Alessandro Cosentino
  # @author Bernhard Posselt
  # Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
  # Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
  #
  # This file is licensed under the Affero General Public License version 3 or
  # later.
  #
  # See the COPYING-README file
  #
  */


  angular.module('News').directive('onEnter', function() {
    return function(scope, elm, attr) {
      return elm.bind('keyup', function(e) {
        if (e.keyCode === 13) {
          e.preventDefault();
          return scope.$apply(attr.onEnter);
        }
      });
    };
  });

  /*
  # ownCloud news app
  #
  # @author Alessandro Cosentino
  # @author Bernhard Posselt
  # Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
  # Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
  #
  # This file is licensed under the Affero General Public License version 3 or
  # later.
  #
  # See the COPYING-README file
  #
  */


  /*
  Thise directive can be bound on an input element with type file and name files []
  When a file is input, the content will be broadcasted as a readFile event
  */


  angular.module('News').directive('readFile', [
    '$rootScope', function($rootScope) {
      return function(scope, elm, attr) {
        return $(elm).change(function() {
          var file, reader;
          if (window.File && window.FileReader && window.FileList) {
            file = elm[0].files[0];
            reader = new FileReader();
            reader.onload = function(e) {
              var content;
              content = e.target.result;
              return $rootScope.$broadcast('readFile', content);
            };
            return reader.readAsText(file);
          } else {
            return alert('Your browser does not support the FileReader API!');
          }
        });
      };
    }
  ]);

  /*
  # ownCloud news app
  #
  # @author Alessandro Cosentino
  # @author Bernhard Posselt
  # Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
  # Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
  #
  # This file is licensed under the Affero General Public License version 3 or
  # later.
  #
  # See the COPYING-README file
  #
  */


  scrolling = true;

  markingRead = true;

  angular.module('News').directive('whenScrolled', [
    '$rootScope', 'Config', function($rootScope, Config) {
      return function(scope, elm, attr) {
        return elm.bind('scroll', function() {
          if (scrolling) {
            scrolling = false;
            setTimeout(function() {
              return scrolling = true;
            }, Config.ScrollTimeout);
            if (markingRead) {
              markingRead = false;
              setTimeout(function() {
                var $elems, feed, feedItem, id, offset, _i, _len, _results;
                markingRead = true;
                $elems = $(elm).find('.feed_item:not(.read)');
                _results = [];
                for (_i = 0, _len = $elems.length; _i < _len; _i++) {
                  feedItem = $elems[_i];
                  offset = $(feedItem).position().top;
                  if (offset <= -50) {
                    id = parseInt($(feedItem).data('id'), 10);
                    feed = parseInt($(feedItem).data('feed'), 10);
                    _results.push($rootScope.$broadcast('read', {
                      id: id,
                      feed: feed
                    }));
                  } else {
                    break;
                  }
                }
                return _results;
              }, Config.MarkReadTimeout);
            }
            return scope.$apply(attr.whenScrolled);
          }
        });
      };
    }
  ]);

  /*
  # ownCloud news app
  #
  # @author Alessandro Cosentino
  # @author Bernhard Posselt
  # Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
  # Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
  #
  # This file is licensed under the Affero General Public License version 3 or
  # later.
  #
  # See the COPYING-README file
  #
  */


  angular.module('News').filter('feedInFolder', function() {
    return function(feeds, folderId) {
      var feed, result, _i, _len;
      result = [];
      for (_i = 0, _len = feeds.length; _i < _len; _i++) {
        feed = feeds[_i];
        if (feed.folderId === folderId) {
          result.push(feed);
        }
      }
      return result;
    };
  });

}).call(this);
