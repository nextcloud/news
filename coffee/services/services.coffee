###
# ownCloud - News app
#
# @author Bernhard Posselt
# Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
#
# This file is licensed under the Affero General Public License version 3 or later.
# See the COPYING-README file
#
###

angular.module('News').factory 'Loading', 
['_Loading', (_Loading) ->
	return new _Loading()
]

# Models
angular.module('News').factory 'ActiveFeed', 
['_ActiveFeed', 'Publisher', (_ActiveFeed, Publisher) ->
	model = new _ActiveFeed()
	Publisher.subscribeTo('activeFeed', model)
	return model
]

angular.module('News').factory 'ShowAll', 
['_ShowAll', 'Publisher', (_ShowAll, Publisher) ->
	model = new _ShowAll()
	Publisher.subscribeTo('showAll', model)
	return model
]

angular.module('News').factory 'StarredCount', 
['_StarredCount', 'Publisher', (_StarredCount, Publisher) ->
	model = new _StarredCount()
	Publisher.subscribeTo('starredCount', model)
	return model
]

angular.module('News').factory 'FeedModel', 
['_FeedModel', 'Publisher', 
(_FeedModel, Publisher) ->
	model = new _FeedModel()
	Publisher.subscribeTo('feeds', model)
	return model
]

angular.module('News').factory 'FolderModel', 
['_FolderModel', 'Publisher', 
(_FolderModel, Publisher) ->
	model = new _FolderModel()
	Publisher.subscribeTo('folders', model)
	return model
]

angular.module('News').factory 'ItemModel', 
['_ItemModel', 'Publisher', 'Cache', 'FeedType',
(_ItemModel, Publisher, Cache, FeedType) ->
	model = new _ItemModel(Cache, FeedType)
	Publisher.subscribeTo('items', model)
	return model
]

# Classes
angular.module('News').factory 'Cache', 
['_Cache', 'FeedType', 'FeedModel', 'FolderModel', 
(_Cache, FeedType, FeedModel, FolderModel) ->
	return new _Cache(FeedType, FeedModel, FolderModel)
]

angular.module('News').factory 'PersistenceNews',
['_PersistenceNews', '$http', '$rootScope', 'Loading', 'Publisher',
(_PersistenceNews, $http, $rootScope, Loading, Publisher) ->
	return new _PersistenceNews($http, $rootScope, Loading, Publisher)
]

angular.module('News').factory 'GarbageRegistry', 
['_GarbageRegistry', 'ItemModel',
(_GarbageRegistry, ItemModel) ->
	return new _GarbageRegistry(ItemModel)
]

angular.module('News').factory 'Publisher', 
['_Publisher', (_Publisher) ->
	return new _Publisher()
]

angular.module('News').factory 'OPMLParser', 
['_OPMLParser', (_OPMLParser) ->
	return new _OPMLParser()
]