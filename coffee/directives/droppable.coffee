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

angular.module('News').directive 'droppable', ['$rootScope', ($rootScope) ->

	return (scope, elm, attr) ->
		$elem = $(elm)

		details = 
			accept: '.feed'
			hoverClass: 'drag-and-drop'
			greedy: true
			drop: (event, ui) ->
				# in case jquery ui did something weird
				$('.drag-and-drop').removeClass('drag-and-drop')

				data = 
					folderId: parseInt($elem.data('id'), 10)
					feedId: parseInt($(ui.draggable).data('id'), 10)

				$rootScope.$broadcast('moveFeedToFolder', data)
				scope.$apply attr.droppable

		$elem.droppable(details)
]