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

describe '_Request', ->

	beforeEach module 'News'

	beforeEach inject (_Request, _Publisher) =>
		@router =
			generate: (route, values) ->
				return 'url'
			registerLoadedCallback: (callback) ->
				callback()
		@publisher = new _Publisher()
		@request = _Request



	it 'should not send requests if not initialized', =>
		http = jasmine.createSpy('http')
		@router.registerLoadedCallback = ->
		req = new @request(http, @publisher, @router)

		req.request('route')

		expect(http).not.toHaveBeenCalled()


	it 'should send requests if initialized', =>
		success =
			success: ->
				error: ->

		@router.registerLoadedCallback = (callback) ->
			@callback = callback
		@router.call = ->
			@callback()

		http = jasmine.createSpy('http').andReturn(success)

		config =
			route: 'route'
			data:
				data:
					abc: 'test'

		called =
			url: 'url'
			data: config.data.data

		req = new @request(http, @publisher, @router)
		req.request(config.route, config.data)

		@router.call()

		expect(http).toHaveBeenCalledWith(called)
		expect(http.callCount).toBe(1)


	it 'should should call router', =>
		success =
			success: ->
				error: ->

		http = jasmine.createSpy('http').andReturn(success)
		router =
			generate: jasmine.createSpy('router').andReturn('url')
			registerLoadedCallback: @router.registerLoadedCallback

		config =
			route: 'route'
			data:
				routeParams:
					test: 'test'

		req = new @request(http, @publisher, router)
		req.request(config.route, config.data)

		expect(router.generate).toHaveBeenCalledWith(config.route,
				config.data.routeParams)


	it 'should call callbacks', =>
		error =
			error: (callback) ->
				callback({})
		success =
			success: (callback) ->
				callback({})
				return error

		http = jasmine.createSpy('http').andReturn(success)
		onSuccess = jasmine.createSpy('onSucces')
		onFailure = jasmine.createSpy('onFailure')

		req = new @request(http, @publisher, @router)
		data =
			onSuccess: onSuccess
			onFailure: onFailure
		req.request('route', data)

		expect(onSuccess).toHaveBeenCalled()
		expect(onFailure).toHaveBeenCalled()


	it 'should call publisher', =>
		fromServer =
			files: ['data']

		publisher =
			publishDataTo: jasmine.createSpy('publisher')

		error =
			error: (callback) ->
				callback({})
		success =
			success: (callback) ->
				callback(fromServer)
				return error

		http = jasmine.createSpy('http').andReturn(success)

		req = new @request(http, publisher, @router)
		req.request(null)

		expect(publisher.publishDataTo).toHaveBeenCalledWith(
			fromServer.files,
			'files'
		)


	it 'should use default config', =>
		success =
			success: ->
				error: ->

		http = jasmine.createSpy('http').andReturn(success)
		req = new @request(http, @publisher, @router)

		defaultConfig =
			config:
				url: 'url'
				data:
					test: 2

		req.request('test', defaultConfig)

		expect(http).toHaveBeenCalledWith(defaultConfig.config)



	it 'should extend default config', =>
		success =
			success: ->
				error: ->

		http = jasmine.createSpy('http').andReturn(success)
		req = new @request(http, @publisher, @router)

		defaultConfig =
			config:
				url: 'wonderurl'
				data:
					test: 2

		req.request('test', defaultConfig)

		expect(http).toHaveBeenCalledWith(defaultConfig.config)


	it 'should have a post shortcut', =>
		success =
			success: ->
				error: ->

		http = jasmine.createSpy('http').andReturn(success)
		req = new @request(http, @publisher, @router)

		defaultConfig =
			config:
				url: 'wonderurl'
				method: 'POST'
				data:
					test: 2

		req.post('test', defaultConfig)

		expect(http).toHaveBeenCalledWith(defaultConfig.config)



	it 'should have a get shortcut', =>
		success =
			success: ->
				error: ->

		http = jasmine.createSpy('http').andReturn(success)
		req = new @request(http, @publisher, @router)

		data =
			config:
				url: 'wonderurl'
			data:
				test: 2

		expected =
			url: 'wonderurl'
			data: data.data
			method: 'GET'
			params: data.data

		req.get('test', data)

		expect(http).toHaveBeenCalledWith(expected)


	it 'should have a put shortcut', =>
		success =
			success: ->
				error: ->

		http = jasmine.createSpy('http').andReturn(success)
		req = new @request(http, @publisher, @router)

		defaultConfig =
			config:
				url: 'wonderurl'
				method: 'PUT'
				data:
					test: 2

		req.put('test', defaultConfig)

		expect(http).toHaveBeenCalledWith(defaultConfig.config)


	it 'should have a delete shortcut', =>
		success =
			success: ->
				error: ->

		http = jasmine.createSpy('http').andReturn(success)
		req = new @request(http, @publisher, @router)

		defaultConfig =
			config:
				url: 'wonderurl'
				method: 'DELETE'
				data:
					test: 2

		req.delete('test', defaultConfig)

		expect(http).toHaveBeenCalledWith(defaultConfig.config)