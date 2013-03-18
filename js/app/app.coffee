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


# app main
angular.module('News', ['OC', 'ui']).config ($provide) ->
	$provide.value 'Config', config =
		markReadTimeout: 500
		scrollTimeout: 500
		feedUpdateInterval: 6000000
		itemBatchSize: 20


angular.module('News').run ['Persistence', (Persistence) ->
	Persistence.init()
]


$(document).ready ->
	# this is used to forces browser to reload content after refreshing
	# and thus clearing the scroll cache
	$(this).keyup (e) ->
		if (e.which == 116) || (e.which == 82 && e.ctrlKey)
			document.location.reload(true)
			return false
