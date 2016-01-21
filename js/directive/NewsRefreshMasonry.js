/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.directive('newsRefreshMasonry', function () {
	'use strict';
	return function (scope, elem) {
		if (scope.$last) {
			var $grid = elem.parent().masonry({
				itemSelector: '.explore-feed',
				gutter: 25,
				columnWidth: 300
			});

			$grid.imagesLoaded().progress( function() {
				$grid.masonry('layout');
			});
		}
	};
});