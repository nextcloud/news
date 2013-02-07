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

angular.module('News').factory 'Persistence', ->

	class Persistence

		constructor: (@appName, @$http) ->
			@appInitialized = false
			@shelvedRequests = []


		setInitialized: (isInitialized) ->
			if isInitialized
				@executePostRequests()
			@appInitialized = isInitialized


		executePostRequests: () ->
			for request in @shelvedRequests
				@post(request.route, request.data, request.callback)
				console.log request
			@shelvedRequests = []


		isInitialized: ->
			return @appInitialized


		post: (route, data={}, callback, errorCallback, init=false, contentType='application/x-www-form-urlencoded') ->
			if @isInitialized == false && init == false
				request =
					route: route
					data: data
					callback: callback
				@shelvedRequests.push(request)
				return

			if not callback
				callback = ->
			if not errorCallback
				errorCallback = ->

			url = OC.Router.generate("news_ajax_" + route)

			data = $.param(data)

			# csrf token
			headers =
				requesttoken: oc_requesttoken
				'Content-Type': 'application/x-www-form-urlencoded'
			
			@$http.post(url, data, {headers: headers}).
			success((data, status, headers, config) ->
				if data.status == "error"
					errorCallback(data.msg)
				else
					callback(data)
			).
			error (data, status, headers, config) ->
				console.warn('Error occured: ')
				console.warn(status)
				console.warn(headers)
				console.warn(config)

