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

angular.module('News').directive 'undoNotification', ['$rootScope',
($rootScope) ->

	return (scope, elm, attr) ->

		elm.click ->
			$(@).fadeOut()

		scope.$on 'notUndone', ->
			$(elm).fadeOut()

		undo = ->
		caption = ''

		link = $(elm).find('a')
		link.click ->
			undo()
			$rootScope.$apply()
			elm.fadeOut()

		scope.getCaption = ->
			return caption

		scope.$on 'undoMessage', (scope, data) ->
			undo = data.undoCallback
			caption = data.caption
			elm.fadeIn().css("display","inline")


]