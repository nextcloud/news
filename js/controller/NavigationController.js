/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.controller('NavigationController',
function ($route, FEED_TYPE, FeedResource, FolderResource, ItemResource,
    SettingsResource) {
    'use strict';

    this.feedError = '';
    this.folderError = '';

    this.getFeeds = () => {
        return FeedResource.getAll();
    };

    this.getFolders = () => {
        return FolderResource.getAll();
    };

    this.markFolderRead = (folderId) => {
        FeedResource.markFolderRead(folderId);

        for (let feed of FeedResource.getByFolderId(folderId)) {
            ItemResource.markFeedRead(feed.id);
        }
    };

    this.markFeedRead = (feedId) => {
        ItemResource.markFeedRead(feedId);
        FeedResource.markFeedRead(feedId);
    };

    this.markRead = () => {
        ItemResource.markRead();
        FeedResource.markRead();
    };

    this.isShowAll = () => {
        return SettingsResource.get('showAll');
    };

    this.getFeedsOfFolder = (folderId) => {
        return FeedResource.getByFolderId(folderId);
    };

    this.getUnreadCount = () => {
        return FeedResource.getUnreadCount();
    };

    this.getFeedUnreadCount = (feedId) => {
        return FeedResource.getById(feedId).unreadCount;
    };

    this.getFolderUnreadCount= (folderId) => {
        return FeedResource.getFolderUnreadCount(folderId);
    };

    this.getStarredCount = () => {
        return ItemResource.getStarredCount();
    };

    this.toggleFolder = (folderName) => {
        FolderResource.toggleOpen(folderName);
    };

    this.hasFeeds = (folderId) => {
        return FeedResource.getFolderUnreadCount(folderId) !== undefined;
    };

    this.subFeedActive = (folderId) => {
        let type = $route.current.$$route.type;

        if (type === FEED_TYPE.FEED) {
            let feed = FeedResource.getById($route.current.params.id);

            if (feed.folderId === folderId) {
                return true;
            }
        }

        return false;
    };

    this.isSubscriptionsActive = () => {
        return $route.current &&
            $route.current.$$route.type === FEED_TYPE.SUBSCRIPTIONS;
    };

    this.isStarredActive = () => {
        return $route.current &&
            $route.current.$$route.type === FEED_TYPE.STARRED;
    };

    this.isFolderActive = (folderId) => {
        let currentId = parseInt($route.current.params.id, 10);
        return $route.current &&
            $route.current.$$route.type === FEED_TYPE.FOLDER &&
            currentId === folderId;
    };

    this.isFeedActive = (feedId) => {
        let currentId = parseInt($route.current.params.id, 10);
        return $route.current &&
            $route.current.$$route.type === FEED_TYPE.FEED &&
            currentId === feedId;
    };

    this.folderNameExists = (folderName) => {
        return FolderResource.get(folderName) !== undefined;
    };

    // TBD
    this.isAddingFolder = () => {
        return true;
    };

    this.createFolder = (folder) => {
        console.log(folder.name);
        folder.name = '';
    };

    this.createFeed = (feed) => {
        this.newFolder = false;
        console.log(feed.url + feed.folder);
        feed.url = '';
    };

    this.renameFeed = (feed) => {
        feed.editing = false;
        // todo remote stuff
    };

    this.renameFolder = () => {
        console.log('TBD');
    };

    this.deleteFeed = (feed) => {
        feed.deleted = true;
        // todo remote stuff
    };

    this.undeleteFeed = (feed) => {
        feed.deleted = false;
        // todo remote stuff
    };

    this.removeFeed = (feed) => {
        console.log('remove ' + feed);
    };

    this.deleteFolder = (folderName) => {
        console.log(folderName);
    };

    this.moveFeed = (feedId, folderId) => {
        console.log(feedId + folderId);
    };

});