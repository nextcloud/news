###
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
###

angular.module('News').factory 'FeedType', ->
	feedType = 
		Feed: 0
		Folder: 1
		Starred: 2
		Subscriptions: 3
		Shared: 4