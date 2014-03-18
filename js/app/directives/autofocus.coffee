angular.module('News').directive 'newsAutoFocus', ->
	directive =
		restrict: 'A'
		link: (scope, elm, attrs) ->
			$(window).load ->
				$(elm).focus()
