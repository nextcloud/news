/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.service('OPMLImporter', function (FeedResource, FolderResource, $q) {
    'use strict';
    var startFeedJob = function (queue) {
        var deferred = $q.defer();

        if (queue.lenght > 0) {
            var feed = queue.pop();
            var url = feed.url;
            var title = feed.title;
            var folderId = 0;
            var folderName = feed.folderName;

            if (folderName !== undefined &&
                FeedResource.get(folderName) !== undefined) {
                folderId = FeedResource.get(feed.folderName).id;
            }

            // make sure to not add already existing feeds
            if (url !== undefined && FeedResource.get(url) === undefined) {
                FeedResource.create(url, folderId, title)
                .finally(function () {
                    startFeedJob(queue);
                });
            }
        } else {
            deferred.resolve();
        }

        return deferred.promise;
    };

    this.importFolders = function (content) {
        // assumption: folders are fast to create and we dont need a queue for
        // them
        var feedQueue = [];
        var folderPromises = [];
        content.folders.forEach(function (folder) {
            if (folder.name !== undefined) {
                // skip already created folders
                if (FolderResource.get(folder.name) !== undefined) {
                    folderPromises.push(FolderResource.create(folder.name));
                }

                folder.feeds.forEach(function (feed) {
                    feed.folderName = folder.name;
                    feedQueue.push(feed);
                });
            }
        });
        feedQueue = feedQueue.concat(content.feeds);

        var deferred = $q.defer();

        $q.all(folderPromises).finally(function () {
            deferred.resolve(feedQueue);
        });

        return deferred.promise;
    };

    this.importFeedQueue = function (feedQueue, jobSize) {
        // queue feeds to prevent server slowdown
        var deferred = $q.defer();

        var jobPromises = [];
        for (var i=0; i<jobSize; i+=1) {
            jobPromises.push(startFeedJob(feedQueue));
        }

        $q.all(jobPromises).then(function () {
            deferred.resolve();
        });

        return deferred.promise;
    };

});