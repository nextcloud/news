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

angular.module('News').factory 'OPMLParser', ->
	
	class Feed

		constructor: (@_name, @_url) ->

		getName: ->
			return @_name

		getUrl: ->
			return @_url

		isFolder: ->
			return false


	class Folder

		constructor: (@_name) ->
			@_items = []

		add: (feed) ->
			@_items.push(feed)

		getItems: ->
			return @_items

		getName: ->
			return @_name

		isFolder: ->
			return true


	class OPMLParser

		parseXML: (xml) ->
			$xml = $($.parseXML(xml))
			$root = $xml.find('body')
			structure = new Folder('root')
			@_recursivelyParse($root, structure)
			return structure

		_recursivelyParse: ($xml, structure) ->
			for outline in $xml.children('outline')
				$outline = $(outline)
				if angular.isDefined($outline.attr('xmlUrl'))
					feed = new Feed($outline.attr('text'), $outline.attr('xmlUrl'))
					structure.add(feed)
				else
					title = $outline.attr('text') || $outline.attr('title')
					folder = new Folder(title)
					structure.add(folder)
					@_recursivelyParse($outline, folder)


	return new OPMLParser()
