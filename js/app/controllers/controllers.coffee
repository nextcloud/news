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

angular.module('News').controller 'SettingsController', 
['$scope', '_SettingsController', 
($scope, _SettingsController)->

	return new _SettingsController($scope)
]


angular.module('News').controller 'FeedController', 
['$scope', '_FeedController', 'FolderModel', 'FeedModel', 
($scope, _FeedController, FolderModel, FeedModel)->

	return new _FeedController($scope, FolderModel, FeedModel)
]

angular.module('News').controller 'ItemController', 
['$scope', '_ItemController', 'ItemModel', 
($scope, _ItemController, ItemModel)->

	return new _ItemController($scope, ItemModel)
]