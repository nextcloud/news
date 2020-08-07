/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.directive('newsDroppable', function ($rootScope) {
    'use strict';

    return function (scope, elem, attr) {
        var details = {
            accept: '.feed',
            hoverClass: 'drag-and-drop',
            greedy: true,
            drop: function (event, ui) {

                $('.drag-and-drop').removeClass('drag-and-drop');

                var data = {
                    folderId: parseInt(elem.data('id'), 10),
                    feedId: parseInt($(ui.draggable).data('id'), 10)
                };

                $rootScope.$broadcast('moveFeedToFolder', data);
                scope.$apply(attr.droppable);
            }
        };

        elem.droppable(details);
    };
});