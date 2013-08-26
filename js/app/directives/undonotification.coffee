###

ownCloud - News

@author Bernhard Posselt
@copyright 2012 Bernhard Posselt dev@bernhard-posselt.com

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

angular.module('News').directive 'undoNotification',
['$rootScope', '$timeout', 'Config',
($rootScope, $timeout, Config) ->

	return (scope, elm, attr) ->
		undo = ->
		caption = ''
		timeout = null

		$(elm).click ->
			timout = null
			$(@).fadeOut()

		$(elm).find('a').click ->
			undo()
			timout = null
			$rootScope.$apply()
			elm.fadeOut()

		scope.getCaption = ->
			return caption

		scope.$on 'undoMessage', (scope, data) ->
			# cancel previous timeouts
			if timeout
				$timeout.cancel(timeout.promise)
				
			# fade out if not reset with a new
			timeout = $timeout =>
				$(elm).fadeOut()
			, Config.undoTimeout
				
			undo = data.undoCallback
			caption = data.caption
			$(elm).fadeIn().css("display","inline")


]