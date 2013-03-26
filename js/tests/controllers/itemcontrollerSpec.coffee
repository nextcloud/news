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


describe '_ItemController', ->


	beforeEach module 'News'

	beforeEach inject (@_ItemController, @ActiveFeed, @ShowAll, @FeedType,
		               @StarredCount, @FeedModel, @FolderModel, @ItemModel) =>
		@scope = {}
		@persistence = {
			getItems: ->
		}
		@controller = new @_ItemController(@scope, @ItemModel)

	it 'should make items availabe', =>
		@ItemModel.getAll = jasmine.createSpy('ItemModel')
		new @_ItemController(@scope, @ItemModel)

		expect(@ItemModel.getAll).toHaveBeenCalled()
