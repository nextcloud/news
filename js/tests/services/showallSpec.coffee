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


describe 'ShowAll', ->


	beforeEach module 'News'

	beforeEach inject (@ShowAll) =>

	it 'should be false by default', =>
		expect(@ShowAll.getShowAll()).toBeFalsy()


	it 'should set the correct ShowAll value', =>
		@ShowAll.handle(true)
		expect(@ShowAll.getShowAll()).toBeTruthy()


	it 'should provide a set Showall setter', =>
		@ShowAll.setShowAll(true)
		expect(@ShowAll.getShowAll()).toBeTruthy()

		@ShowAll.setShowAll(false)
		expect(@ShowAll.getShowAll()).toBeFalsy()
