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

angular.module('News').factory 'Language', ->

	class Language

		constructor: ->
			@_language = 'en'
			@_langs = [
				'ar-ma'
				'ar'
				'bg'
				'ca'
				'cs'
				'cv'
				'da'
				'de'
				'el'
				'en-ca'
				'en-gb'
				'eo'
				'es'
				'et'
				'eu'
				'fi'
				'fr-ca'
				'fr'
				'gl'
				'he'
				'hi'
				'hu'
				'id'
				'is'
				'it'
				'ja'
				'ka'
				'ko'
				'lv'
				'ms-my'
				'nb'
				'ne'
				'nl'
				'pl'
				'pt-br'
				'pt'
				'ro'
				'ru'
				'sk'
				'sl'
				'sv'
				'th'
				'tr'
				'tzm-la'
				'tzm'
				'uk'
				'zh-cn'
				'zh-tw'
			]


		handle: (data) ->
			# fix broken server locales
			data = data.replace('_', '-').toLowerCase()

			# check if the first part is available, if so use this
			if not (data in @_langs)
				data = data.split('-')[0]

			# if its not available default to english
			if not (data in @_langs)
				data = 'en'

			@_language = data


		getLanguage: ->
			return @_language


		getMomentFromTimestamp: (timestamp) ->
			return moment.unix(timestamp).lang(@_language)


	return new Language()
