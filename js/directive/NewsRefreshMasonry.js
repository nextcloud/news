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
	var refresh = function (elem) {

			elem.parent().masonry({
				itemSelector: '.grid-item',
				gutter: 25,
				columnWidth: 300
			});

	};

	return function (scope, elem) {
		if (scope.$last) {
			refresh(elem);
		}
	};
});