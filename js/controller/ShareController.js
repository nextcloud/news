/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Marco Nassabain <marco.nassabain@hotmail.com>
 */
app.controller('ShareController', function (ShareResource, Loading) {
    'use strict';

    this.userList = [];

    /**
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

    // Dict <itemId, List<Int>(user_id)>: Local mapping b/w users & articles: 
    //[Article 1 : <Jimmy, Aurelien, ...>, Article 2: <...>]
    this.usersSharedArticles = {};

    this.shareItem = function(itemId, userId) {
        Loading.setLoading(userId, true);
        if (this.usersSharedArticles[itemId] && this.usersSharedArticles[itemId].includes(userId)) {
            Loading.setLoading(userId, false);
            return;
        }

        // quick initialization (instead of if (...) : [])
        this.usersSharedArticles[itemId] = this.usersSharedArticles[itemId] ? this.usersSharedArticles[itemId] : [];

        this.usersSharedArticles[itemId].push(userId);
        
        var response = ShareResource.shareItem(itemId, userId);
        response.then((result) => {
            Loading.setLoading(userId, false);
            return result;
        });
    };

});