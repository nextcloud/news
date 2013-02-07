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

angular.module('News').factory '_OPMLParser', ->
	
	class Feed

		constructor: (@name, @url) ->

		getName: ->
			return @name

		getUrl: ->
			return @url

		isFolder: ->
			return false


	class Folder

		constructor: (@name) ->
			@items = []

		add: (feed) ->
			@items.push(feed)

		getItems: ->
			return @items

		getName: ->
			return @name

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
				if $outline.attr('type') != undefined
					feed = new Feed($outline.attr('text'), $outline.attr('xmlUrl'))
					structure.add(feed)
				else
					folder = new Folder($outline.attr('text'))
					structure.add(folder)
					@_recursivelyParse($outline, folder)


	return OPMLParser
