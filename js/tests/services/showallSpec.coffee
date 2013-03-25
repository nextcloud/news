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


describe '_ShowAll', ->


	beforeEach module 'News'

	beforeEach inject (@_ShowAll) =>
		@showAll = new @_ShowAll()


	it 'should be false by default', =>
		

		expect(@showAll.getShowAll()).toBeFalsy()


	it 'should set the correct showAll value', =>
		@showAll.handle(true)
		expect(@showAll.getShowAll()).toBeTruthy()


	it 'should provide a set showall setter', =>
		@showAll.setShowAll(true)
		expect(@showAll.getShowAll()).toBeTruthy()

		@showAll.setShowAll(false)
		expect(@showAll.getShowAll()).toBeFalsy()
