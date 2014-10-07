/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.directive('newsOnFolderRoute', function ($rootScope, $route, FEED_TYPE) {
    'use strict';

    return {
    	scope: {
    		newsOnFolderRoute: '&',
    	},
    	link: function (scope) {
	        $rootScope.$on('$locationChangeStart', function () {
	        	if ($route.current.$$route.type === FEED_TYPE.FOLDER) {
	        		var id = parseInt($route.current.params.id, 10);
	        		scope.folder.existingFolder = scope.newsOnFolderRoute(id);
	        	}
	        });
	    }
    };

});