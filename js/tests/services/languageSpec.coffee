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


describe 'Language', ->

	beforeEach module 'News'

	beforeEach inject (@Language, @FeedType) =>
		@data = 'de'


	it 'should be en by default', =>
		expect(@Language.getLanguage()).toBe('en')


	it 'should set the correct language', =>
		@Language.handle(@data)
		expect(@Language.getLanguage()).toBe('de')

	it 'should only set the first part of the language if not available', =>
		@Language.handle 'de_DE'
		expect(@Language.getLanguage()).toBe('de')

	it 'should default to en', =>
		@Language.handle 'dse_DEst'
		expect(@Language.getLanguage()).toBe('en')


	it 'should support languages', =>
		langs = [
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

		for lang in langs
			@Language.handle lang
			expect(@Language.getLanguage()).toBe(lang)