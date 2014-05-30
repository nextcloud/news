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
        return $route.current &&
            $route.current.$$route.type === FEED_TYPE.FOLDER &&
            $route.current.params.id === folderId;
    };

    this.isFeedActive = (feedId) => {
        return $route.current &&
            $route.current.$$route.type === FEED_TYPE.FEED &&
            $route.current.params.id === feedId;
    };

    // TBD
    this.isAddingFolder = () => {
        return true;
    };

    this.createFeed = (feedUrl, folderId) => {
        console.log(feedUrl + folderId);
    };

    this.createFolder = (folderName) => {
        console.log(folderName);
    };

    this.cancelRenameFolder = (folderId) => {
        console.log(folderId);
    };

    this.renameFeed = (feedId, feedTitle) => {
        console.log(feedId + feedTitle);
    };

    this.cancelRenameFeed = (feedId) => {
        console.log(feedId);
    };

    this.renameFolder = () => {
        console.log('TBD');
    };

    this.deleteFeed = () => {
        console.log('TBD');
    };

    this.deleteFolder = () => {
        console.log('TBD');
    };

    this.moveFeed = () => {
        console.log('TBD');
    };

});