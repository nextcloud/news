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

    /** Value used to check if the received response is the most recent one */
    this.searchQuery = '';

    /** True if the most recent request failed */
    this.searchUsersFailed = false;

    /**  */
    this.facebookLimit = 180;
    this.twitterLimit = 100;
    this.emailLimit = 180;

    /**
     * @param search Username search query
     *
     * Retrieve users matching search query using OC
     */
    this.searchUsers = function(search) {
        this.searchUsersFailed = false;
        if (!search || search === '') {
            this.userList = [];
            return;
        }

        Loading.setLoading('user', true);
        this.searchQuery = search;

        ShareResource.getUsers(search)
        .then((response) => {
            if (this.searchQuery === search) {
                this.userList = response.ocs.data.exact.users;
                this.userList = this.userList.concat(response.ocs.data.users);
                Loading.setLoading('user', false);
            }
        })
        .catch(() => {
            if (this.searchQuery === search) {
                this.userList = [];
                this.searchUsersFailed = true;
                Loading.setLoading('user', false);
            }
        });
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

    this.isAnySocialAppEnabled = function() {
        let media = ['facebook', 'twitter', 'email'];
        return media.some(m => this.isSocialAppEnabled(m));
    };

    this.getFacebookUrl = function(url, intro){
        return `https://www.facebook.com/sharer/sharer.php?u=${url}&quote=${intro.substring(0,this.facebookLimit)}`+
                `...`;
    };

    this.getTwitterUrl = function(url, intro){
        return `https://twitter.com/intent/tweet?url=${url}&text=${intro.substring(0,this.twitterLimit)}`+
                `...`;
    };

    this.getEmailUrl = function(url, object, intro){
        return encodeURI(`mailto:?subject=${object}&body=${intro.substring(0,this.emailLimit)}...\n\n${url}`);
    };
});
