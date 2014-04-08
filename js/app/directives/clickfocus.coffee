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

# Used to focus an element when you click on the element that this directive is
# bound to
# The selector attribute needs to defined, this element will be focused
# If the timeout attribute is defined, it will focus the element after
# after the passed miliseconds
# Examlpe: <div oc-click-focus="{selector: '#app-content', timeout: 3000}">
angular.module('News').directive 'ocClickFocus', ['$timeout', ($timeout) ->

	return (scope, elm, attr) ->
		options = scope.$eval(attr.ocClickFocus)

		if angular.isDefined(options) and angular.isDefined(options.selector)
			elm.click ->
				if angular.isDefined(options.timeout)
					$timeout ->
						$(options.selector).focus()
					, options.timeout
				else
					$(options.selector).focus()


]