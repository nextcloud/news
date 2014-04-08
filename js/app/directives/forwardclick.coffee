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


# Used to forward clicks to another element via jquery selector
# The expression which can be passed looks like this {selector:'#opml-upload'}
# The element where to which the click was fowarded must not be a child element
# otherwise this will end in endless recursion
angular.module('News').directive 'ocForwardClick', ->

	return (scope, elm, attr) ->
		options = scope.$eval(attr.ocForwardClick)

		if angular.isDefined(options) and angular.isDefined(options.selector)
			$(elm).click ->
				$(options.selector).trigger('click')
