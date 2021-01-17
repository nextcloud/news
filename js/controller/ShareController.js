/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Marco Nassabain <marco.nassabain@hotmail.com>
 */
app.controller('ShareController', function (ShareResource) {
    'use strict';

    this.userList = [];

    this.searchUsers = function(search) {
        // TODO: search === undefined 🤢 je pense pas que c'est ouf comme syntaxe
        if (search === '' || search === undefined) {
            this.userList = [];
            return;
        }

        // TODO: bug - requetes retardataires (regarder issues git)
        var response = ShareResource.getUsers(search);
        response.then((response) => {
            this.userList = response.ocs.data.users;
        });
    };

    this.shareItem = function(itemId, userId) {
        var response = ShareResource.shareItem(itemId, userId);
        response.then((result) => {
            return result;
        });
    };

});