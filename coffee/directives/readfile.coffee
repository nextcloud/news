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

###
Thise directive can be bound on an input element with type file and name files []
When a file is input, the content will be broadcasted as a readFile event
###
angular.module('News').directive 'readFile', ['$rootScope', ($rootScope) ->

	return (scope, elm, attr) ->
		$(elm).change ->
			if window.File and window.FileReader and window.FileList
				file = elm[0].files[0]

				reader = new FileReader()

				reader.onload = (e) ->
					content = e.target.result
					$rootScope.$broadcast 'readFile', content	
				
				reader.readAsText(file)

			else
				alert 'Your browser does not support the FileReader API!'
]