/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.directive('newsRefreshMasonry', function ($timeout) {
	'use strict';
	var refresh = function () {
		$timeout(function () {
			$('.grid').masonry({
				itemSelector: '.grid-item',
				gutter: 25,
				columnWidth: 300
			});
			console.log('bubb');
		});
	};

	return function (scope) {
		console.log('loading');
		console.log(scope);
		if (scope.$last) {
			refresh();
		}
	};
});