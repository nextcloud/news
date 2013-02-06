###
# ownCloud
#
# @author Bernhard Posselt
# Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
#
# This file is licensed under the Affero General Public License version 3 or later.
# See the COPYING-README file
#
###

angular.module('OC').factory '_Request', ->

	class Request

		constructor: (@_$http, @_$rootScope, @_publisher, @_token, @_router) ->
			@_initialized = false
			@_shelvedRequests = []

			@_$rootScope.$on 'routesLoaded', =>
				@_executeShelvedRequests()
				@_initialized = true
				@_shelvedRequests = []


		request: (route, routeParams={}, data={}, onSuccess=null, onFailure=null, config={}) ->
			# if routes are not ready yet, save the request
			if not @_initialized
				@_shelveRequest(route, routeParams, data, method, config)
				return

			url = @_router.generate(route, routeParams)

			defaultConfig = 
				method: 'GET'
				url: url
				data: data

			# overwrite default values from passed in config
			for key, value of config
				defaultConfig[key] = value

			@_$http(config)
				.success (data, status, headers, config) =>
					if onSuccess
						onSuccess(data, status, headers, config)

					# publish data to models
					for name, value of data.data
						@publisher.publishDataTo(name, value)

				.error (data, status, headers, config) ->
					if onFailure
						onFailure(data, status, headers, config)


		_shelveRequest: (route, routeParams, data, method, config) ->
			request =
				route: route
				routeParams: routeParams
				data: data
				config: config
				method: method

			@_shelvedRequests.push(request)


		_executeShelvedRequests: ->
			for req in @_shelvedRequests
				@post(req.route, req.routeParams, req.data, req.method, req.config)



	return Request
