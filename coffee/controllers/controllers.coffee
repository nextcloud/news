###
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
###

angular.module('News').controller 'SettingsController', 
['_SettingsController', '$scope', '$rootScope', 'ShowAll', 'PersistenceNews', 
'FolderModel', 'FeedModel', 'OPMLParser',
(_SettingsController, $scope, $rootScope, ShowAll, PersistenceNews, 
FolderModel, FeedModel, OPMLParser) ->
        return new _SettingsController($scope, $rootScope, PersistenceNews,
                                                                        OPMLParser)
]

angular.module('News').controller 'ItemController', 
['_ItemController', '$scope', 'ItemModel', 'ActiveFeed', 'PersistenceNews', 'FeedModel',
'StarredCount', 'GarbageRegistry', 'ShowAll', 'Loading', '$rootScope', 'FeedType',
(_ItemController, $scope, ItemModel, ActiveFeed, PersistenceNews, FeedModel, 
StarredCount, GarbageRegistry, ShowAll, Loading, $rootScope, FeedType) ->
	return new _ItemController($scope, ItemModel, ActiveFeed, PersistenceNews
								FeedModel, StarredCount, GarbageRegistry, 
								ShowAll, Loading, $rootScope, FeedType)
]

angular.module('News').controller 'FeedController', 
['_FeedController', '$scope', 'FeedModel', 'FeedType', 'FolderModel', 'ActiveFeed', 'PersistenceNews',
'StarredCount', 'ShowAll', 'ItemModel', 'GarbageRegistry', '$rootScope', 'Loading',
'Config',
(_FeedController, $scope, FeedModel, FeedType, FolderModel, ActiveFeed, PersistenceNews
StarredCount, ShowAll, ItemModel, GarbageRegistry, $rootScope, Loading, Config) ->
	return new _FeedController($scope, FeedModel, FolderModel, FeedType, 
								ActiveFeed, PersistenceNews, StarredCount, ShowAll,
								ItemModel, GarbageRegistry, $rootScope, Loading,
								Config)
]

angular.module('News').controller 'AddNewController',
['_AddNewController', '$scope',
(_AddNewController, $scope) ->
        return new _AddNewController($scope)
]