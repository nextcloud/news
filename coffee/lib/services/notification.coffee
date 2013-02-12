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

###
# Inject notification into angular to make testing easier
###
angular.module('OC').factory 'Notification', ->
	return OC.Notification