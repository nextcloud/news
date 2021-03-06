/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Marco Nassabain <marco.nassabain@hotmail.com>
 * @author Nicolas Wendling <nicolas.wendling1011@gmail.com>
 * @author Jimmy Huynh <natorisaki@gmail.com>
 * @author Aurélien David <dav.aurelien@gmail.com>
 */
app.controller('ShareController', function (ShareResource, Loading) {
    'use strict';

    this.showDropDown = false;

    this.toggleDropdown = function() {
        this.showDropDown = !this.showDropDown;
    };

    /** Array containing users to share an item with */
    this.userList = [];

    /**
     * @param search Username search query
     * 
     * Retrieve users matching search query using OC
     */
    this.searchUsers = function(search) {
        Loading.setLoading('user', true);
        if (!search || search === '') {
            this.userList = [];
            Loading.setLoading('user', false);
            return;
        }

        var response = ShareResource.getUsers(search);
        response.then((response) => {
            this.userList = response.ocs.data.users;
            Loading.setLoading('user', false);
        });
    };

    /** Dictionary mapping articles to users they're shared with */
    this.usersSharedArticles = {};

    /**
     * @param itemId ID of the item to be shared
     * @param userId ID of the recipient
     * 
     * Call the /share route with the appropriate params to share an item.
     * Fills this.usersSharedArticles to avoid re-sharing the same article
     * with the same user multiple times.
     */
    this.shareItem = function(itemId, userId) {
        Loading.setLoading(userId, true);
        if (this.usersSharedArticles[itemId] && this.usersSharedArticles[itemId].includes(userId)) {
            Loading.setLoading(userId, false);
            return;
        }

        this.usersSharedArticles[itemId] = this.usersSharedArticles[itemId] ? this.usersSharedArticles[itemId] : [];
        this.usersSharedArticles[itemId].push(userId);
        
        ShareResource.shareItem(itemId, userId)
        .then((result) => {
            Loading.setLoading(userId, false);
            return result;
        });
    };

});
