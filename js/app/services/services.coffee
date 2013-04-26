###

ownCloud - News

@author Bernhard Posselt
@copyright 2012 Bernhard Posselt nukeawhale@gmail.com

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
License as published by the Free Software Foundation; either
version 3 of the License, or any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU AFFERO GENERAL PUBLIC LICENSE for more details.

You should have received a copy of the GNU Affero General Public
License along with this library.  If not, see <http://www.gnu.org/licenses/>.

###


# request related stuff
angular.module('News').factory 'Request',
['_Request', '$http', 'Publisher', 'Router',
(_Request, $http, Publisher, Router) ->
	return new _Request($http, Publisher, Router)
]


# loading helpers
angular.module('News').factory 'FeedLoading', ['_Loading', (_Loading) ->
	return new _Loading()
]

angular.module('News').factory 'AutoPageLoading', ['_Loading', (_Loading) ->
	return new _Loading()
]

angular.module('News').factory 'NewLoading', ['_Loading', (_Loading) ->
	return new _Loading()
]


angular.module('News').factory 'Publisher',
['_Publisher', 'ActiveFeed', 'ShowAll', 'StarredCount', 'ItemModel',
'FolderModel', 'FeedModel', 'Language', 'NewestItem',
(_Publisher, ActiveFeed, ShowAll, StarredCount, ItemModel,
FolderModel, FeedModel, Language, NewestItem) ->

	# register items at publisher to automatically add incoming items
	publisher = new _Publisher()
	publisher.subscribeObjectTo(ActiveFeed, 'activeFeed')
	publisher.subscribeObjectTo(ShowAll, 'showAll')
	publisher.subscribeObjectTo(Language, 'language')
	publisher.subscribeObjectTo(StarredCount, 'starred')
	publisher.subscribeObjectTo(FolderModel, 'folders')
	publisher.subscribeObjectTo(FeedModel, 'feeds')
	publisher.subscribeObjectTo(ItemModel, 'items')
	publisher.subscribeObjectTo(NewestItem, 'newestItemId')

	return publisher
]

