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


# Inherit from this baseclass to define your own routes
angular.module('News').factory '_Request', ->

	class Request

		constructor: (@_$http, @_publisher, @_router) ->
			@_initialized = false
			@_shelvedRequests = []

			@_router.registerLoadedCallback =>
				@_initialized = true
				@_executeShelvedRequests()
				@_shelvedRequests = []


		request: (route, data={}) ->
			###
			Wrapper to do a normal request to the server. This needs to
			be done to hook the publisher into the requests and to handle
			requests, that come in before routes have been loaded

			route: the routename data can contain the following
			data.routeParams: object with parameters for the route
			data.data: ajax data objec which is passed to PHP
			data.onSuccess: callback for successful requests
			data.onFailure: callback for failed requests
			data.config: a config which should be passed to $http
			###
			defaultData =
				routeParams: {}
				data: {}
				onSuccess: ->
				onFailure: ->
				config: {}

			angular.extend(defaultData, data)

			# if routes are not ready yet, save the request
			if not @_initialized
				@_shelveRequest(route, defaultData)
				return

			url = @_router.generate(route, defaultData.routeParams)

			defaultConfig =
				url: url
				data: defaultData.data


			# overwrite default values from passed in config
			angular.extend(defaultConfig, defaultData.config)

			# use params array instead of data when using get
			if defaultConfig.method == 'GET'
				defaultConfig.params = defaultConfig.data

			@_$http(defaultConfig)
				.success (data, status, headers, config) =>

					# publish data to models
					for name, value of data
						@_publisher.publishDataTo(value, name)

					defaultData.onSuccess(data, status, headers, config)


				.error (data, status, headers, config) ->
					defaultData.onFailure(data, status, headers, config)


		post: (route, data={}) ->
			###
			Request shortcut which sets the method to POST
			###
			data.config or= {}
			data.config.method = 'POST'
			@request(route, data)


		get: (route, data={}) ->
			###
			Request shortcut which sets the method to GET
			###
			data.config or= {}
			data.config.method = 'GET'
			@request(route, data)

		put: (route, data={}) ->
			###
			Request shortcut which sets the method to GET
			###
			data.config or= {}
			data.config.method = 'PUT'
			@request(route, data)


		delete: (route, data={}) ->
			###
			Request shortcut which sets the method to GET
			###
			data.config or= {}
			data.config.method = 'DELETE'
			@request(route, data)


		_shelveRequest: (route, data) ->
			###
			Saves requests for later if the routes have not been loaded
			###
			request =
				route: route
				data: data

			@_shelvedRequests.push(request)


		_executeShelvedRequests: ->
			###
			Run all saved requests that were done before routes were fully
			loaded
			###
			for r in @_shelvedRequests
				@request(r.route, r.data)



	return Request
