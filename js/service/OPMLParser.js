/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
app.service('OPMLParser', function () {
    'use strict';

    var parseOutline = function (outline) {
        var url = outline.attr('xmlUrl') || outline.attr('htmlUrl');
        var name = outline.attr('title') || outline.attr('text') || url;

        // folder
        if (url === undefined) {
            return {
                type: 'folder',
                name: name,
                feeds: []
            };

        // feed
        } else {
            return {
                type: 'feed',
                name: name,
                url: url
            };
        }
    };

    // there is only one level, so feeds in a folder in a folder should be
    // attached to the root folder
    var recursivelyParse = function (level, root, firstLevel) {
        for (var i=0; i<level.length; i+=1) {
            var outline = $(level[i]);

            var entry = parseOutline(outline);

            if (entry.type === 'feed') {
                root.feeds.push(entry);
            } else {

                // only first level should append folders
                if (firstLevel) {
                    recursivelyParse(outline.children('outline'), entry, false);
                    root.folders.push(entry);
                } else {
                    recursivelyParse(outline.children('outline'), root, false);
                }
            }
        };

        return root;
    };

    this.parse = function (xml) {
        xml = $.parseXML(xml);
        var firstLevel = $(xml).find('body > outline');

        var root = {
            'feeds': [],
            'folders': []
        };

        return recursivelyParse(firstLevel, root, true);
    };

});