/**
 * ownCloud - core
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */

(function (window, document, $) {

	'use strict';

	$(document).ready(function () {

		var buttons = $('[data-app-slide-toggle-area]:not([data-app-slide-toggle-area=""])');

		$(document).click(function (event) {

			buttons.each(function (index, button) {
				console.log(button);

				var area = $(button).data('app-slide-toggle-area');

				// if the
				if (button === event.target) {
					console.log(area);
					event.stopPropagation();
				}
			});

		});
	});

}(window, document, jQuery));