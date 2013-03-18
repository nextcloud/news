###

ownCloud - News

@author Raghu Nayyar
@copyright 2012 Raghu Nayyar me@iraghu.com

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


describe '_OPMLParser', ->

	beforeEach module 'News'

	beforeEach inject (@_OPMLParser) =>
		@parser = new @_OPMLParser()
		
	it 'should return only the root folder when parsing empty OPML', =>
		@data = @parser.parseXML('')
		expect(@data.getName()).toBe('root')
			
			
			