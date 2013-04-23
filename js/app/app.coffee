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
		feedUpdateInterval: 1000*60*3  # miliseconds
		itemBatchSize: 20
		# the autoPageFactor defines how many heights of the box must be left
		# before it starts autopaging e.g. if it was 2, then it will start
		# to fetch new items if less than the height*2 px is left to scroll
		autoPageFactor: 6


angular.module('News').run ['Persistence', 'Config',
(Persistence, Config) ->

	Persistence.init()
	
	setInterval ->
		Persistence.getAllFeeds()
	, Config.feedUpdateInterval
]


$(document).ready ->
	# this is used to forces browser to reload content after refreshing
	# and thus clearing the scroll cache
	$(this).keyup (e) ->
		if (e.which == 116) || (e.which == 82 && e.ctrlKey)
			document.location.reload(true)
			return false
