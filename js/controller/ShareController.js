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
        // TODO: catch error
    };

    /** Dictionary mapping articles to users they're shared with */
    this.usersSharedArticles = [];

    /**
     * Test whether an item is shared with a user
     *
     * @param itemId ID of the item being shared
     * @param userId User ID of the recipient
     * @returns boolean
     */
    this.itemIsSharedWithUser = function(itemId, userId) {
        let item = this.usersSharedArticles.find(i => i.id === itemId);
        if (!item) {
            return false;
        }
        let user = item.users.find(u => u.id === userId);
        if (!user || !user.status) {
            return false;
        }
        return true;
    };

    /**
     * Inserts an item share action into the dictionary
     *
     * @param itemId ID of the item being shared
     * @param userId User ID of the recipient
     * @param status boolean indicating if the share was successful
     */
    this.addItemShareWithUser = function(itemId, userId, status) {
        let item = this.usersSharedArticles.find(i => i.id === itemId);
        if (!item) {
            item = {
                id: itemId,
                users: []
            };
            this.usersSharedArticles.push(item);
        }
        let user = item.users.find(u => u.id === userId);
        if (!user) {
            user = {
                id: userId,
                status: status
            };
            item.users.push(user);
        }
        user.status = status;
    };

    /**
     * @param itemId ID of the item to be shared
     * @param userId ID of the recipient
     *
     * Call the /share route with the appropriate params to share an item.
     * Fills this.usersSharedArticles to avoid re-sharing the same article
     * with the same user multiple times.
     */
    this.shareItem = function(itemId, userId) {
        if (this.itemIsSharedWithUser(itemId, userId)) {
            return;
        }
        Loading.setLoading(userId, true);

        ShareResource.shareItem(itemId, userId)
        .then(() => {
            this.addItemShareWithUser(itemId, userId, true);
            Loading.setLoading(userId, false);
        })
        .catch(() => {
            this.addItemShareWithUser(itemId, userId, false);
            Loading.setLoading(userId, false);
        });
    };

    /**
     * Indicates whether the share action is in progress
     *
     * @param userId User ID of the recipient
     * @returns boolean
     */
    this.isLoading = function(userId) {
        return Loading.isLoading(userId);
    };

    /**
     * Indicates whether the share actions matches the given status
     *
     * @param itemId ID of the item being shared
     * @param userId User ID of the recipient
     * @param status true (successful) / false (failed)
     * @returns boolean
     */
    this.isStatus = function(itemId, userId, status) {
        let item = this.usersSharedArticles.find(i => i.id === itemId);
        if (!item) {
            return false;
        }
        let user = item.users.find(u => u.id === userId);
        if (!user) {
            return false;
        }
        return user.status === status;
    };

    /**
     * Checks if the social sharing app for the given media is active
     *
     * @param media
     * @returns boolean
     */
    this.isSocialAppEnabled = function(media) {
        let app = 'socialsharing_' + media;
        return app in OC.appswebroots;
    };

    this.getFacebookUrl = function(url){
        return 'https://www.facebook.com/sharer/sharer.php?u='+url;
    };

    this.getTwitterUrl = function(url){
        return 'https://twitter.com/intent/tweet?url='+url;
    };
});
