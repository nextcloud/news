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
function (FeedResource, FolderResource, ItemResource, SettingsResource) {
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

    // TBD
    this.createFeed = () => {
        console.log('TBD');
    };

    this.createFolder = () => {
        console.log('TBD');
    };

    this.renameFeed = () => {
        console.log('TBD');
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

    this.isActive = () => {
        console.log('TBD');
    };

    this.isVisible = () => {
        console.log('TBD');
    };


});