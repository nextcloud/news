###
# ownCloud
#
# @author Bernhard Posselt
# Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
#
# This file is licensed under the Affero General Public License version 3 or later.
# See the COPYING-README file
#
###

###
# Various config stuff for owncloud
###
angular.module('OC', []).config ['$httpProvider', ($httpProvider) ->

	# Always send the CSRF token by default
	$httpProvider.defaults.get['requesttoken'] = oc_requesttoken
	$httpProvider.defaults.post['requesttoken'] = oc_requesttoken
	
	# needed because crap PHP does not understand JSON
	$httpProvider.defaults.post['Content-Type'] = 'application/x-www-form-urlencoded'
	$httpProvider.defaults.get['Content-Type'] = 'application/x-www-form-urlencoded'
	$httpProvider.defaults.transformRequest = (data) ->
		if angular.isDefined(data)
			return data
		else
			return $.param(data)
]