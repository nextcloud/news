(function(window, document, angular, $, OC, csrfToken, undefined){

'use strict';


var app = angular.module('News', ['ngRoute', 'ngSanitize', 'ngAnimate']);
app.config(["$routeProvider", "$provide", "$httpProvider", function ($routeProvider, $provide, $httpProvider) {
    'use strict';

    var feedType = {
        FEED: 0,
        FOLDER: 1,
        STARRED: 2,
        SUBSCRIPTIONS: 3,
        SHARED: 4
    };

    // constants
    $provide.constant('REFRESH_RATE', 60);  // seconds
    $provide.constant('ITEM_BATCH_SIZE', 50);  // how many items to autopage by
    $provide.constant('BASE_URL', OC.generateUrl('/apps/news'));
    $provide.constant('FEED_TYPE', feedType);

    // make sure that the CSRF header is only sent to the ownCloud domain
    $provide.factory('CSRFInterceptor', ["$q", "BASE_URL", function ($q, BASE_URL) {
        return {
            request: function (config) {
                if (config.url.indexOf(BASE_URL) === 0) {
                    config.headers.requesttoken = csrfToken;
                }

                return config || $q.when(config);
            }
        };
    }]);
    $httpProvider.interceptors.push('CSRFInterceptor');

    // routing
    var getResolve = function (type) {
        return {
            // request to items also returns feeds
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
                        url:  BASE_URL + '/items',
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

    $routeProvider
        .when('/items', {
            controller: 'ContentController as Content',
            templateUrl: 'content.html',
            resolve: getResolve(feedType.SUBSCRIPTIONS),
            type: feedType.SUBSCRIPTIONS
        })
        .when('/items/starred', {
            controller: 'ContentController as Content',
            templateUrl: 'content.html',
            resolve: getResolve(feedType.STARRED),
            type: feedType.STARRED
        })
        .when('/items/feeds/:id', {
            controller: 'ContentController as Content',
            templateUrl: 'content.html',
            resolve: getResolve(feedType.FEED),
            type: feedType.FEED
        })
        .when('/items/folders/:id', {
            controller: 'ContentController as Content',
            templateUrl: 'content.html',
            resolve: getResolve(feedType.FOLDER),
            type: feedType.FOLDER
        });

}]);


app.run(["$rootScope", "$location", "$http", "$q", "$interval", "Loading", "ItemResource", "FeedResource", "FolderResource", "SettingsResource", "Publisher", "BASE_URL", "FEED_TYPE", "REFRESH_RATE", function ($rootScope, $location, $http, $q, $interval, Loading,
         ItemResource, FeedResource, FolderResource, SettingsResource,
          Publisher, BASE_URL, FEED_TYPE, REFRESH_RATE) {
    'use strict';

    // show Loading screen
    Loading.setLoading('global', true);

    // listen to keys in returned queries to automatically distribute the
    // incoming values to models
    Publisher.subscribe(ItemResource).toChannels(['items', 'newestItemId',
                                                  'starred']);
    Publisher.subscribe(FolderResource).toChannels(['folders']);
    Publisher.subscribe(FeedResource).toChannels(['feeds']);
    Publisher.subscribe(SettingsResource).toChannels(['settings']);

    // load feeds, settings and last read feed
    var settingsDeferred = $q.defer();
    $http.get(BASE_URL + '/settings').success(function (data) {
        Publisher.publishAll(data);
        settingsDeferred.resolve();
    });

    var activeFeedDeferred = $q.defer();
    var path = $location.path();
    $http.get(BASE_URL + '/feeds/active').success(function (data) {
        var url;

        switch (data.activeFeed.type) {

        case FEED_TYPE.FEED:
            url = '/items/feeds/' + data.activeFeed.id;
            break;

        case FEED_TYPE.FOLDER:
            url = '/items/folders/' + data.activeFeed.id;
            break;

        case FEED_TYPE.STARRED:
            url = '/items/starred';
            break;

        default:
            url = '/items';
        }

        // only redirect if url is empty or faulty
        if (!/^\/items(\/(starred|feeds\/\d+|folders\/\d+))?\/?$/.test(path)) {
            $location.path(url);
        }

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

    // disable loading if all initial requests finished
    $q.all(
        [
            settingsDeferred.promise,
            activeFeedDeferred.promise,
            feedDeferred.promise,
            folderDeferred.promise
        ]
    )
        .then(function () {
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

}]);
app.controller('AppController',
["Loading", "FeedResource", "FolderResource", function (Loading, FeedResource, FolderResource) {
    'use strict';

    this.loading = Loading;

    this.isFirstRun = function () {
        return FeedResource.size() === 0 && FolderResource.size() === 0;
    };

}]);
app.controller('ContentController',
["Publisher", "FeedResource", "ItemResource", "SettingsResource", "data", "$route", "$routeParams", "FEED_TYPE", function (Publisher, FeedResource, ItemResource, SettingsResource, data,
    $route, $routeParams, FEED_TYPE) {
    'use strict';

    // dont cache items across multiple route changes
    ItemResource.clear();

    // distribute data to models based on key
    Publisher.publishAll(data);


    this.isAutoPagingEnabled = true;

    this.getItems = function () {
        return ItemResource.getAll();
    };

    this.toggleStar = function (itemId) {
        ItemResource.toggleStar(itemId);
    };

    this.toggleItem = function (item) {
        // TODO: unittest
        if (this.isCompactView()) {
            item.show = !item.show;
        }
    };

    this.markRead = function (itemId) {
        var item = ItemResource.get(itemId);

        if (!item.keepUnread && item.unread === true) {
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
            return 'id';
        } else {
            return '-id';
        }
    };

    this.isCompactView = function () {
        return SettingsResource.get('compact');
    };

    this.autoPagingEnabled = function () {
        return this.isAutoPagingEnabled;
    };

    this.markReadEnabled = function () {
        return !SettingsResource.get('preventReadOnScroll');
    };

    this.scrollRead = function (itemIds) {
        var ids = [];
        var feedIds = [];

        itemIds.forEach(function (itemId) {
            var item = ItemResource.get(itemId);
            if (!item.keepUnread) {
                ids.push(itemId);
                feedIds.push(item.feedId);
            }
        });

        FeedResource.markItemsOfFeedsRead(feedIds);
        ItemResource.markItemsRead(ids);
    };

    this.isFeed = function () {
        return $route.current.$$route.type === FEED_TYPE.FEED;
    };

    this.autoPage = function () {
        this.isAutoPagingEnabled = false;

        var type = $route.current.$$route.type;
        var id = $routeParams.id;

        var self = this;
        ItemResource.autoPage(type, id).success(function (data) {
            Publisher.publishAll(data);

            if (data.items.length > 0) {
                self.isAutoPagingEnabled = true;
            }
        }).error(function () {
            self.isAutoPagingEnabled = true;
        });
    };

    this.getRelativeDate = function (timestamp) {
        if (timestamp !== undefined && timestamp !== '') {
            var languageCode = SettingsResource.get('language');
            var date =
                moment.unix(timestamp).locale(languageCode).fromNow() + '';
            return date;
        } else {
            return '';
        }
    };

}]);
app.controller('NavigationController',
["$route", "FEED_TYPE", "FeedResource", "FolderResource", "ItemResource", "SettingsResource", "Publisher", "$rootScope", "$location", function ($route, FEED_TYPE, FeedResource, FolderResource, ItemResource,
    SettingsResource, Publisher, $rootScope, $location) {
    'use strict';

    this.feedError = '';
    this.folderError = '';

    this.getFeeds = function () {
        return FeedResource.getAll();
    };

    this.getFolders = function () {
        return FolderResource.getAll();
    };

    this.markFolderRead = function (folderId) {
        FeedResource.markFolderRead(folderId);

        FeedResource.getByFolderId(folderId).forEach(function (feed) {
            ItemResource.markFeedRead(feed.id);
        });
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

    this.getFeedsOfFolder = function (folderId) {
        return FeedResource.getByFolderId(folderId);
    };

    this.getUnreadCount = function () {
        return FeedResource.getUnreadCount();
    };

    this.getFeedUnreadCount = function (feedId) {
        var feed = FeedResource.getById(feedId);
        if (feed !== undefined) {
            return feed.unreadCount;
        } else {
            return 0;
        }
    };

    this.getFolderUnreadCount= function (folderId) {
        return FeedResource.getFolderUnreadCount(folderId);
    };

    this.getStarredCount = function () {
        return ItemResource.getStarredCount();
    };

    this.toggleFolder = function (folderName) {
        FolderResource.toggleOpen(folderName);
    };

    this.hasFeeds = function (folderId) {
        return FeedResource.getFolderUnreadCount(folderId) !== undefined;
    };

    this.subFeedActive = function (folderId) {
        var type = $route.current.$$route.type;

        if (type === FEED_TYPE.FEED) {
            var feed = FeedResource.getById($route.current.params.id);

            if (feed.folderId === folderId) {
                return true;
            }
        }

        return false;
    };

    this.isSubscriptionsActive = function () {
        return $route.current &&
            $route.current.$$route.type === FEED_TYPE.SUBSCRIPTIONS;
    };

    this.isStarredActive = function () {
        return $route.current &&
            $route.current.$$route.type === FEED_TYPE.STARRED;
    };

    this.isFolderActive = function (folderId) {
        var currentId = parseInt($route.current.params.id, 10);
        return $route.current &&
            $route.current.$$route.type === FEED_TYPE.FOLDER &&
            currentId === folderId;
    };

    this.isFeedActive = function (feedId) {
        var currentId = parseInt($route.current.params.id, 10);
        return $route.current &&
            $route.current.$$route.type === FEED_TYPE.FEED &&
            currentId === feedId;
    };

    this.folderNameExists = function (folderName) {
        folderName = folderName || '';
        return FolderResource.get(folderName.trim()) !== undefined;
    };

    this.feedUrlExists = function (url) {
        url = url || '';
        url = url.trim();
        return FeedResource.get(url) !== undefined ||
            FeedResource.get('http://' + url) !== undefined;
    };

    this.createFeed = function (feed) {
        this.newFolder = false;

        var self = this;
        var folderName = feed.folder;
        var folderId = feed.folderId || {id: 0};

        // we dont need to create a new folder
        if (folderName === undefined) {
            folderId.getsFeed = true;

            FeedResource.create(feed.url, folderId.id, undefined)
            .then(function (data) {
                Publisher.publishAll(data);

                // set folder as default
                var createdFeed = data.feeds[0];

                // load created feed
                $location.path('/items/feeds/' + createdFeed.id);
                folderId.getsFeed = undefined;
            }, function () {
                folderId.getsFeed = undefined;
            });
        } else {
            // create folder first and then the feed
            FolderResource.create(folderName).then(function (data) {
                Publisher.publishAll(data);

                feed.folderId = data.folders[0];
                feed.folder = undefined;
                self.createFeed(feed);
            });
        }

        feed.url = '';
    };

    this.createFolder = function (folder) {
        FolderResource.create(folder.name).then(function (data) {
            Publisher.publishAll(data);
        });
        folder.name = '';
    };

    this.moveFeed = function (feedId, folderId) {
        var reload = false;
        var feed = FeedResource.getById(feedId);

        if (feed.folderId === folderId) {
            return;
        }

        if (this.isFolderActive(feed.folderId) ||
            this.isFolderActive(folderId)) {
            reload = true;
        }

        FeedResource.move(feedId, folderId);

        if (reload) {
            $route.reload();
        }
    };

    // TBD
    this.renameFeed = function (feed) {
        feed.editing = false;
        // todo remote stuff
    };

    this.renameFolder = function (folder) {
        console.log(folder);
    };

    this.deleteFeed = function (feed) {
        feed.deleted = true;
        // todo remote stuff
    };

    this.undeleteFeed = function (feed) {
        feed.deleted = false;
        // todo remote stuff
    };

    this.removeFeed = function (feed) {
        console.log('remove ' + feed);
    };

    this.deleteFolder = function (folderName) {
        console.log(folderName);
    };

    this.removeFolder = function (folder) {
        console.log('remove ' + folder);
    };

    var self = this;
    $rootScope.$on('moveFeedToFolder', function (scope, data) {
        self.moveFeed(data.feedId, data.folderId);
    });

}]);
app.controller('SettingsController',
["$route", "SettingsResource", "FeedResource", function ($route, SettingsResource, FeedResource) {
    'use strict';

    this.importing = false;
    this.opmlImportError = false;
    this.articleImportError = false;

    var set = function (key, value) {
        SettingsResource.set(key, value);

        if (['showAll', 'oldestFirst'].indexOf(key) >= 0) {
            $route.reload();
        }
    };


    this.toggleSetting = function (key) {
        set(key, !this.getSetting(key));
    };


    this.getSetting = function (key) {
        return SettingsResource.get(key);
    };


    this.feedSize = function () {
        return FeedResource.size();
    };


    // TBD
    this.importOpml = function (content) {
        console.log(content);
    };


    this.importArticles = function (content) {
        console.log(content);
    };

}]);
app.filter('trustUrl', ["$sce", function ($sce) {
    'use strict';

    return function (url) {
        return $sce.trustAsResourceUrl(url);
    };
}]);
app.filter('unreadCountFormatter', function () {
    'use strict';

    return function (unreadCount) {
        if (unreadCount > 999) {
            return '999+';
        }
        return unreadCount;
    };
});
app.factory('FeedResource', ["Resource", "$http", "BASE_URL", "$q", function (Resource, $http, BASE_URL, $q) {
    'use strict';

    var FeedResource = function ($http, BASE_URL, $q) {
        Resource.call(this, $http, BASE_URL, 'url');
        this.ids = {};
        this.unreadCount = 0;
        this.folderUnreadCount = {};
        this.folderIds = {};
        this.deleted = null;
        this.$q = $q;
    };

    FeedResource.prototype = Object.create(Resource.prototype);

    FeedResource.prototype.receive = function (data) {
        Resource.prototype.receive.call(this, data);
        this.updateUnreadCache();
        this.updateFolderCache();
    };


    FeedResource.prototype.updateUnreadCache = function () {
        this.unreadCount = 0;
        this.folderUnreadCount = {};

        var self = this;
        this.values.forEach(function (feed) {
            if (feed.unreadCount) {
                self.unreadCount += feed.unreadCount;
            }
            if (feed.folderId !== undefined) {
                self.folderUnreadCount[feed.folderId] =
                    self.folderUnreadCount[feed.folderId] || 0;
                self.folderUnreadCount[feed.folderId] += feed.unreadCount;
            }
        });
    };


    FeedResource.prototype.updateFolderCache = function () {
        this.folderIds = {};

        var self = this;
        this.values.forEach(function (feed) {
            self.folderIds[feed.folderId] =
                self.folderIds[feed.folderId] || [];
            self.folderIds[feed.folderId].push(feed);
        });
    };


    FeedResource.prototype.add = function (value) {
        Resource.prototype.add.call(this, value);
        if (value.id !== undefined) {
            this.ids[value.id] = this.hashMap[value.url];
        }
    };


    FeedResource.prototype.delete = function (url) {
        var feed = this.get(url);
        this.deleted = feed;
        delete this.ids[feed.id];

        Resource.prototype.delete.call(this, url);

        this.updateUnreadCache();
        this.updateFolderCache();

        return this.http.delete(this.BASE_URL + '/feeds/' + feed.id);
    };


    FeedResource.prototype.markRead = function () {
        this.values.forEach(function (feed) {
            feed.unreadCount = 0;
        });

        this.unreadCount = 0;
        this.folderUnreadCount = {};
    };


    FeedResource.prototype.markFeedRead = function (feedId) {
        this.ids[feedId].unreadCount = 0;
        this.updateUnreadCache();
    };


    FeedResource.prototype.markFolderRead = function (folderId) {
        this.values.forEach(function (feed) {
            if (feed.folderId === folderId) {
                feed.unreadCount = 0;
            }
        });

        this.updateUnreadCache();
    };


    FeedResource.prototype.markItemOfFeedRead = function (feedId) {
        this.ids[feedId].unreadCount -= 1;
        this.updateUnreadCache();
    };


    FeedResource.prototype.markItemsOfFeedsRead = function (feedIds) {
        var self = this;
        feedIds.forEach(function (feedId) {
            self.ids[feedId].unreadCount -= 1;
        });

        this.updateUnreadCache();
    };


    FeedResource.prototype.markItemOfFeedUnread = function (feedId) {
        this.ids[feedId].unreadCount += 1;
        this.updateUnreadCache();
    };


    FeedResource.prototype.getUnreadCount = function () {
        return this.unreadCount;
    };


    FeedResource.prototype.getFolderUnreadCount = function (folderId) {
        return this.folderUnreadCount[folderId];
    };


    FeedResource.prototype.getByFolderId = function (folderId) {
        return this.folderIds[folderId] || [];
    };


    FeedResource.prototype.getById = function (feedId) {
        return this.ids[feedId];
    };


    FeedResource.prototype.rename = function (url, name) {
        var feed = this.get(url);
        feed.title = name;

        return this.http({
            method: 'POST',
            url: this.BASE_URL + '/feeds/' + feed.id + '/rename',
            data: {
                feedTitle: name
            }
        });
    };


    FeedResource.prototype.move = function (feedId, folderId) {
        var feed = this.getById(feedId);
        feed.folderId = folderId;

        this.updateFolderCache();
        this.updateUnreadCache();

        return this.http({
            method: 'POST',
            url: this.BASE_URL + '/feeds/' + feed.id + '/move',
            data: {
                parentFolderId: folderId
            }
        });

    };


    FeedResource.prototype.create = function (url, folderId, title) {
        url = url.trim();
        if (!url.startsWith('http')) {
            url = 'http://' + url;
        }

        if (title !== undefined) {
            title = title.trim();
        }

        var feed = {
            url: url,
            folderId: folderId || 0,
            title: title || url,
            unreadCount: 0
        };

        this.add(feed);
        this.updateFolderCache();

        var deferred = this.$q.defer();

        this.http({
            method: 'POST',
            url: this.BASE_URL + '/feeds',
            data: {
                url: url,
                parentFolderId: folderId || 0,
                title: title
            }
        }).success(function (data) {
            deferred.resolve(data);
        }).error(function (data) {
            feed.faviconLink = '';
            feed.error = data.message;
            deferred.reject();
        });

        return deferred.promise;
    };


    FeedResource.prototype.undoDelete = function () {
        if (this.deleted) {
            this.add(this.deleted);

            return this.http.post(
                this.BASE_URL + '/feeds/' + this.deleted.id + '/restore'
            );
        }

        this.updateFolderCache();
        this.updateUnreadCache();
    };


    return new FeedResource($http, BASE_URL, $q);
}]);
app.factory('FolderResource', ["Resource", "$http", "BASE_URL", "$q", function (Resource, $http, BASE_URL, $q) {
    'use strict';

    var FolderResource = function ($http, BASE_URL, $q) {
        Resource.call(this, $http, BASE_URL, 'name');
        this.deleted = null;
        this.$q = $q;
    };

    FolderResource.prototype = Object.create(Resource.prototype);

    FolderResource.prototype.delete = function (folderName) {
        var folder = this.get(folderName);
        this.deleted = folder;

        Resource.prototype.delete.call(this, folderName);

        return this.http.delete(this.BASE_URL + '/folders/' + folder.id);
    };


    FolderResource.prototype.toggleOpen = function (folderName) {
        var folder = this.get(folderName);
        folder.opened = !folder.opened;

        return this.http({
            url: this.BASE_URL + '/folders/' + folder.id + '/open',
            method: 'POST',
            data: {
                folderId: folder.id,
                open: folder.opened
            }
        });
    };


    FolderResource.prototype.rename = function (folderName, toFolderName) {
        var folder = this.get(folderName);

        folder.name = toFolderName;

        delete this.hashMap[folderName];
        this.hashMap[toFolderName] = folder;

        // FIXME: check for errors
        // FIXME: transfer feeds
        return this.http({
            url: this.BASE_URL + '/folders/' + folder.id + '/rename',
            method: 'POST',
            data: {
                folderName: toFolderName
            }
        });
    };


    FolderResource.prototype.create = function (folderName) {
        folderName = folderName.trim();
        var folder = {
            name: folderName
        };

        this.add(folder);

        var deferred = this.$q.defer();

        this.http({
            url: this.BASE_URL + '/folders',
            method: 'POST',
            data: {
                folderName: folderName
            }
        }).success(function (data) {
            deferred.resolve(data);
        }).error(function (data) {
            folder.error = data.message;
        });

        return deferred.promise;
    };


    FolderResource.prototype.undoDelete = function () {
        // TODO: check for errors
        if (this.deleted) {
            this.add(this.deleted);

            return this.http.post(
                this.BASE_URL + '/folders/' + this.deleted.id + '/restore'
            );
        }
    };


    return new FolderResource($http, BASE_URL, $q);
}]);
app.factory('ItemResource', ["Resource", "$http", "BASE_URL", "ITEM_BATCH_SIZE", function (Resource, $http, BASE_URL,
                                      ITEM_BATCH_SIZE) {
    'use strict';


    var ItemResource = function ($http, BASE_URL, ITEM_BATCH_SIZE) {
        Resource.call(this, $http, BASE_URL);
        this.starredCount = 0;
        this.batchSize = ITEM_BATCH_SIZE;
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


    ItemResource.prototype.star = function (itemId, isStarred) {
        if (isStarred === undefined) {
            isStarred = true;
        }

        var it = this.get(itemId);
        var url = this.BASE_URL +
            '/items/' + it.feedId + '/' + it.guidHash + '/star';

        it.starred = isStarred;

        if (isStarred) {
            this.starredCount += 1;
        } else {
            this.starredCount -= 1;
        }

        return this.http({
            url: url,
            method: 'POST',
            data: {
                isStarred: isStarred
            }
        });
    };


    ItemResource.prototype.toggleStar = function (itemId) {
        if (this.get(itemId).starred) {
            this.star(itemId, false);
        } else {
            this.star(itemId, true);
        }
    };


    ItemResource.prototype.markItemRead = function (itemId, isRead) {
        if (isRead === undefined) {
            isRead = true;
        }

        this.get(itemId).unread = !isRead;

        return this.http({
            url: this.BASE_URL + '/items/' + itemId + '/read',
            method: 'POST',
            data: {
                isRead: isRead
            }
        });
    };


    ItemResource.prototype.markItemsRead = function (itemIds) {
        var self = this;

        itemIds.forEach(function(itemId) {
            self.get(itemId).unread = false;
        });

        return this.http({
            url: this.BASE_URL + '/items/read/multiple',
            method: 'POST',
            data: {
                itemIds: itemIds
            }
        });
    };


    ItemResource.prototype.markFeedRead = function (feedId, read) {
        if (read === undefined) {
            read = true;
        }

        var items = this.values.filter(function (element) {
            return element.feedId === feedId;
        });

        items.forEach(function (item) {
            item.unread = !read;
        });

        return this.http.post(this.BASE_URL + '/feeds/' + feedId + '/read');
    };


    ItemResource.prototype.markRead = function () {
        this.values.forEach(function (item) {
            item.unread = false;
        });

        return this.http.post(this.BASE_URL + '/items/read');
    };


    ItemResource.prototype.autoPage = function (type, id) {
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
    };


    return new ItemResource($http, BASE_URL, ITEM_BATCH_SIZE);
}]);
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
/*jshint undef:false*/
app.service('Publisher', function () {
    'use strict';

    this.channels = {};

    this.subscribe = function (obj) {
        var self = this;

        return {
            toChannels: function (channels) {
                channels.forEach(function (channel) {
                    self.channels[channel] = self.channels[channel] || [];
                    self.channels[channel].push(obj);
                });
            }
        };

    };

    this.publishAll = function (data) {
        var self = this;

        Object.keys(data).forEach(function (channel) {
            var listeners = self.channels[channel];
            if (listeners !== undefined) {
                listeners.forEach(function (listener) {
                    listener.receive(data[channel], channel);
                });
            }
        });
    };

});
app.factory('Resource', function () {
    'use strict';

    var Resource = function (http, BASE_URL, id) {
        this.id = id || 'id';
        this.values = [];
        this.hashMap = {};
        this.http = http;
        this.BASE_URL = BASE_URL;
    };


    Resource.prototype.receive = function (objs) {
        var self = this;
        objs.forEach(function (obj) {
            self.add(obj);
        });
    };


    Resource.prototype.add = function (obj) {
        var existing = this.hashMap[obj[this.id]];

        if (existing === undefined) {
            this.values.push(obj);
            this.hashMap[obj[this.id]] = obj;
        } else {
            // copy values from new to old object if it exists already
            Object.keys(obj).forEach(function (key) {
                existing[key] = obj[key];
            });
        }
    };


    Resource.prototype.size = function () {
        return this.values.length;
    };


    Resource.prototype.get = function (id) {
        return this.hashMap[id];
    };


    Resource.prototype.delete = function (id) {
        // find index of object that should be deleted
        var self = this;
        var deleteAtIndex = this.values.findIndex(function(element) {
            return element[self.id] === id;
        });

        if (deleteAtIndex !== undefined) {
            this.values.splice(deleteAtIndex, 1);
        }

        if (this.hashMap[id] !== undefined) {
            delete this.hashMap[id];
        }
    };


    Resource.prototype.clear = function () {
        this.hashMap = {};

        // http://stackoverflow.com/questions/1232040
        // this is the fastes way to empty an array when you want to keep
        // the reference around
        while (this.values.length > 0) {
            this.values.pop();
        }
    };


    Resource.prototype.getAll = function () {
        return this.values;
    };


    return Resource;
});
/*jshint unused:false*/
app.service('SettingsResource', ["$http", "BASE_URL", function ($http, BASE_URL) {
    'use strict';

    this.settings = {
        language: 'en',
        showAll: false,
        compact: false,
        oldestFirst: false,
        preventReadOnScroll: false
    };
    this.defaultLanguageCode = 'en';
    this.supportedLanguageCodes = [
        'ar-ma', 'ar', 'bg', 'ca', 'cs', 'cv', 'da', 'de', 'el', 'en-ca',
        'en-gb', 'eo', 'es', 'et', 'eu', 'fi', 'fr-ca', 'fr', 'gl', 'he', 'hi',
        'hu', 'id', 'is', 'it', 'ja', 'ka', 'ko', 'lv', 'ms-my', 'nb', 'ne',
        'nl', 'pl', 'pt-br', 'pt', 'ro', 'ru', 'sk', 'sl', 'sv', 'th', 'tr',
        'tzm-la', 'tzm', 'uk', 'zh-cn', 'zh-tw'
    ];

    this.receive = function (data) {
        var self = this;
        Object.keys(data).forEach(function (key) {
            var value = data[key];

            if (key === 'language') {
                value = self.processLanguageCode(value);
            }

            self.settings[key] = value;
        });
    };

    this.get = function (key) {
        return this.settings[key];
    };

    this.set = function (key, value) {
        this.settings[key] = value;

        return $http({
            url: BASE_URL + '/settings',
            method: 'PUT',
            data: this.settings
        });
    };

    this.processLanguageCode = function (languageCode) {
        languageCode = languageCode.replace('_', '-').toLowerCase();

        if (this.supportedLanguageCodes.indexOf(languageCode) < 0) {
            languageCode = languageCode.split('-')[0];
        }

        if (this.supportedLanguageCodes.indexOf(languageCode) < 0) {
            languageCode = this.defaultLanguageCode;
        }

        return languageCode;
    };

}]);
/**
 * Code in here acts only as a click shortcut mechanism. That's why its not
 * being put into a directive since it has to be tested with protractor
 * anyways and theres no benefit from wiring it into the angular app
 */
(function (window, document, $) {
    'use strict';

    var noInputFocused = function (element) {
        return !(
            element.is('input') ||
            element.is('select') ||
            element.is('textarea') ||
            element.is('checkbox')
        );
    };

    var noModifierKey = function (event) {
        return !(
            event.shiftKey ||
            event.altKey ||
            event.ctrlKey ||
            event.metaKey
        );
    };

    var onActiveItem = function (scrollArea, callback) {
        var items = scrollArea.find('.item');

        items.each(function (index, item) {
            item = $(item);

            // 130px of the item should be visible
            if ((item.height() + item.position().top) > 30) {
                callback(item);

                return false;
            }
        });

    };

    var toggleUnread = function (scrollArea) {
        onActiveItem(scrollArea, function (item) {
            item.find('.toggle-keep-unread').trigger('click');
        });
    };

    var toggleStar = function (scrollArea) {
        onActiveItem(scrollArea, function (item) {
            item.find('.star').trigger('click');
        });
    };

    var expandItem = function (scrollArea) {
        onActiveItem(scrollArea, function (item) {
            item.find('.utils').trigger('click');
        });
    };

    var openLink = function (scrollArea) {
        onActiveItem(scrollArea, function (item) {
            item.trigger('click');  // mark read
            window.open(item.find('.external').attr('href'), '_blank');
        });
    };

    var scrollToItem = function (scrollArea, item, isCompactMode) {
        // if you go to the next article in compact view, it should
        // expand the current one
        scrollArea.scrollTop(
            item.offset().top - scrollArea.offset().top + scrollArea.scrollTop()
        );

        if (isCompactMode) {
            onActiveItem(scrollArea, function (item) {
                if (!item.hasClass('open')) {
                    item.find('.utils').trigger('click');
                }
            });
        }
    };

    var scrollToNextItem = function (scrollArea, isCompactMode) {
        var items = scrollArea.find('.item');
        var jumped = false;

        items.each(function (index, item) {
            item = $(item);

            if (item.position().top > 1) {
                scrollToItem(scrollArea, item, isCompactMode);

                jumped = true;

                return false;
            }
        });

        // in case this is the last item it should still scroll below the top
        if (!jumped) {
            scrollArea.scrollTop(scrollArea.prop('scrollHeight'));
        }

    };

    var scrollToPreviousItem = function (scrollArea, isCompactMode) {
        var items = scrollArea.find('.item');
        var jumped = false;

        items.each(function (index, item) {
            item = $(item);

            if (item.position().top >= 0) {
                var previous = item.prev();

                // if there are no items before the current one
                if (previous.length > 0) {
                    scrollToItem(scrollArea, previous, isCompactMode);
                }

                jumped = true;

                return false;
            }
        });

        // if there was no jump jump to the last element
        if (!jumped && items.length > 0) {
            scrollToItem(scrollArea, items.last());
        }

    };


    $(document).keyup(function (event) {
        if (noInputFocused($(':focus')) && noModifierKey(event)) {
            var keyCode = event.keyCode;
            var scrollArea = $('#app-content');
            var isCompactMode = $('#app-content-wrapper > .compact').length > 0;

            // j, n, right arrow
            if ([74, 78, 39].indexOf(keyCode) >= 0) {

                event.preventDefault();
                scrollToNextItem(scrollArea, isCompactMode);

            // k, p, left arrow
            } else if ([75, 80, 37].indexOf(keyCode) >= 0) {

                event.preventDefault();
                scrollToPreviousItem(scrollArea, isCompactMode);

            // u
            } else if ([85].indexOf(keyCode) >= 0) {

                event.preventDefault();
                toggleUnread(scrollArea);

            // e
            } else if ([69].indexOf(keyCode) >= 0) {

                event.preventDefault();
                expandItem(scrollArea);

            // s, i, l
            } else if ([73, 83, 76].indexOf(keyCode) >= 0) {

                event.preventDefault();
                toggleStar(scrollArea);

            // h
            } else if ([72].indexOf(keyCode) >= 0) {

                event.preventDefault();
                toggleStar(scrollArea);
                scrollToNextItem(scrollArea);

            // o
            } else if ([79].indexOf(keyCode) >= 0) {

                event.preventDefault();
                openLink(scrollArea);

            }

        }
    });

}(window, document, $));
app.run(["$document", "$rootScope", function ($document, $rootScope) {
    'use strict';
    $document.click(function (event) {
        $rootScope.$broadcast('documentClicked', event);
    });
}]);

app.directive('appNavigationEntryUtils', function () {
    'use strict';
    return {
        restrict: 'C',
        link: function (scope, elm) {
            var menu = elm.siblings('.app-navigation-entry-menu');
            var button = $(elm)
                .find('.app-navigation-entry-utils-menu-button button');

            button.click(function () {
                menu.toggleClass('open');
            });

            scope.$on('documentClicked', function (scope, event) {
                if (event.target !== button[0]) {
                    menu.removeClass('open');
                }
            });
        }
    };
});
app.directive('newsAudio', function () {
    'use strict';
    return {
        restrict: 'E',
        scope: {
            src: '@',
            type: '@'
        },
        transclude: true,
        template: '' +
        '<audio controls="controls" preload="none" ng-hide="cantPlay()">' +
            '<source ng-src="{{ src|trustUrl }}">' +
        '</audio>' +
        '<a ng-href="{{ src|trustUrl }}" class="button" ng-show="cantPlay()" ' +
            'ng-transclude></a>',
        link: function (scope, elm) {
            var source = elm.children().children('source')[0];
            var cantPlay = false;

            source.addEventListener('error', function () {
                scope.$apply(function () {
                    cantPlay = true;
                });
            });

            scope.cantPlay = function () {
                return cantPlay;
            };
        }
    };
});
app.directive('newsAutoFocus', function () {
    'use strict';
    return function (scope, elem, attrs) {
        var toFocus = elem;

        if (attrs.newsAutoFocus) {
            toFocus = $(attrs.newsAutoFocus);
        }

        toFocus.focus();
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
app.directive('newsDraggable', function () {
    'use strict';

    return function (scope, elem, attr) {
        var options = scope.$eval(attr.newsDraggable);

        if (angular.isDefined(options)) {
            elem.draggable(options);
        } else {
            elem.draggable();
        }

        attr.$observe('newsDraggableDisable', function (value) {
        	if (value === 'true') {
        		elem.draggable('disable');
        	} else {
        		elem.draggable('enable');
        	}
        });
    };
});
app.directive('newsDroppable', ["$rootScope", function ($rootScope) {
    'use strict';

    return function (scope, elem, attr) {
        var details = {
            accept: '.feed',
            hoverClass: 'drag-and-drop',
            greedy: true,
            drop: function (event, ui) {

                $('.drag-and-drop').removeClass('drag-and-drop');

                var data = {
                    folderId: parseInt(elem.data('id'), 10),
                    feedId: parseInt($(ui.draggable).data('id'), 10)
                };

                $rootScope.$broadcast('moveFeedToFolder', data);
                scope.$apply(attr.droppable);
            }
        };

        elem.droppable(details);
    };
}]);
app.directive('newsFocus', ["$timeout", "$interpolate", function ($timeout, $interpolate) {
    'use strict';

    return function (scope, elem, attrs) {
        elem.click(function () {
            var toReadd = $($interpolate(attrs.newsFocus)(scope));
            $timeout(function () {
                toReadd.focus();
            }, 500);
        });
    };

}]);
app.directive('newsReadFile', function () {
    'use strict';

    return function (scope, elem, attr) {

        elem.change(function () {

            var file = elem[0].files[0];
            var reader = new FileReader();

            reader.onload = function (event) {
                elem[0].value = 0;
                // FIXME: is there a more flexible solution where we dont have
                // to bind the file to scope?
                scope.$fileContent = event.target.result;
                scope.$apply(attr.newsReadFile);
            };

            reader.readAsText(file);
        });
    };
});
app.directive('newsScroll', ["$timeout", function ($timeout) {
    'use strict';

    // autopaging
    var autoPage = function (enabled, limit, elem, scope) {
        if (enabled) {
            var counter = 0;
            var articles = elem.find('.item');

            for (var i = articles.length - 1; i >= 0; i -= 1) {
                var item = $(articles[i]);


                // if the counter is higher than the size it means
                // that it didnt break to auto page yet and that
                // there are more items, so break
                if (counter >= limit) {
                    break;
                }

                // this is only reached when the item is not is
                // below the top and we didnt hit the factor yet so
                // autopage and break
                if (item.position().top < 0) {
                    scope.$apply(scope.newsScrollAutoPage);
                    break;
                }

                counter += 1;
            }
        }
    };

    // mark read
    var markRead = function (enabled, elem, scope) {
        if (enabled) {
            var ids = [];
            var articles = elem.find('.item:not(.read)');

            articles.each(function(index, article) {
                var item = $(article);

                if (item.position().top <= -50) {
                    ids.push(parseInt(item.data('id'), 10));
                } else {
                    return false;
                }
            });

            scope.itemIds = ids;
            scope.$apply(scope.newsScrollMarkRead);
        }
    };

    return {
        restrict: 'A',
        scope: {
            'newsScrollAutoPage': '&',
            'newsScrollMarkRead': '&',
            'newsScrollEnabledMarkRead': '=',
            'newsScrollEnabledAutoPage': '=',
            'newsScrollMarkReadTimeout': '@',  // optional, defaults to 1 second
            'newsScrollTimeout': '@',  // optional, defaults to 1 second
            'newsScrollAutoPageWhenLeft': '@'  // optional, defaults to 50
        },
        link: function (scope, elem) {
            var allowScroll = true;

            var scrollTimeout = scope.newsScrollTimeout || 1;
            var markReadTimeout = scope.newsScrollMarkReadTimeout || 1;
            var autoPageLimit = scope.newsScrollAutoPageWhenLeft || 50;

            var scrollHandler = function () {
                // allow only one scroll event to trigger at once
                if (allowScroll) {
                    allowScroll = false;

                    $timeout(function () {
                        allowScroll = true;
                    }, scrollTimeout*1000);

                    autoPage(scope.newsScrollEnabledAutoPage,
                             autoPageLimit,
                             elem,
                             scope);

                    // allow user to undo accidental scroll
                    $timeout(function () {
                        markRead(scope.newsScrollEnabledMarkRead,
                                 elem,
                                 scope);
                    }, markReadTimeout*1000);
                }

            };

            elem.on('scroll', scrollHandler);

            // remove scroll handler if element is destroyed
            scope.$on('$destroy', function () {
                elem.off('scroll', scrollHandler);
            });
        }
    };
}]);
app.directive('newsStopPropagation', function () {
    'use strict';
    return {
        restrict: 'A',
        link: function (scope, element) {
            element.bind('click', function (event) {
                event.stopPropagation();
            });
        }
    };
});
app.directive('newsTimeout', ["$timeout", function ($timeout) {
    'use strict';

    return {
        restrict: 'A',
        scope: {
            'newsTimeout': '&'
        },
        link: function (scope) {
            var seconds = 7;
            var timer = $timeout(scope.newsTimeout, seconds * 1000);

            // remove timeout if element is being removed by
            // for instance clicking on the x button
            scope.$on('$destroy', function () {
                $timeout.cancel(timer);
            });
        }
    };
}]);
app.directive('newsTitleUnreadCount', ["$window", function ($window) {
    'use strict';

    var baseTitle = $window.document.title;

    return {
        restrict: 'E',
        scope: {
            unreadCount: '@'
        },
        link: function (scope, elem, attrs) {
            attrs.$observe('unreadCount', function (value) {
                var titles = baseTitle.split('-');

                if (value !== '0') {
                    $window.document.title = titles[0] +
                        '(' + value + ') - ' + titles[1];
                }
            });
        }
    };

}]);
app.directive('newsTriggerClick', function () {
    'use strict';

    return function (scope, elm, attr) {
        elm.click(function () {
            $(attr.newsTriggerClick).trigger('click');
        });
    };

});

})(window, document, angular, jQuery, OC, oc_requesttoken);