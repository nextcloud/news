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

app = angular.module('News', []).config ($provide) ->
	# enter your config values in here
	config =
		MarkReadTimeout: 500
		ScrollTimeout: 500
		initialLoadedItemsNr: 20
		FeedUpdateInterval: 6000000

	$provide.value('Config', config)


app.run ['PersistenceNews', (PersistenceNews) ->
	PersistenceNews.loadInitial()
]


$(document).ready ->
	# this is used to forces browser to reload content after refreshing
	# and thus clearing the scroll cache
	$(this).keyup (e) ->
		if (e.which == 116) || (e.which == 82 && e.ctrlKey)
			document.location.reload(true)
			return false

	# click on upload button should trigger the file input
	$('#browselink').click ->
		$('#file_upload_start').trigger('click')
