/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */
describe('OPMLParser', function () {
    'use strict';

    var result;

    /*jshint multistr: true */
    /*jshint quotmark: double */
    var xml = "<?xml version='1.0' ?> \
    <opml version='1.1'> \
    <head> \
    </head> \
    <body> \
        <outline htmlUrl='http://www.reddit.com/r/tldr/' text='test_text'/> \
        <outline text='Design' title='Tesign'> \
            <outline \
                htmlUrl='http://worrydream.com/' \
                text='yo' \
                title='man' \
                xmlUrl='http://worrydream.com/feed.xml'/> \
            <outline text='Mom' title='Me'> \
                <outline \
                    htmlUrl='http://afaikblog.wordpress.com'/> \
                <outline \
                    htmlUrl='http://informationarchitects.net' \
                    xmlUrl='http://informationarchitects.net/feed/'/> \
            </outline> \
        </outline> \
        <outline text='Nomadism'> \
            <outline \
                htmlUrl='http://a-flat.posterous.com' \
                title='a-flat' \
                xmlUrl='http://a-flat.posterous.com/rss.xml'/> \
        </outline> \
        \<outline text='Nomadism'> \
            <outline \
                htmlUrl='http://google.com' \
                title='google' \
                xmlUrl='http://google.com/rss.xml'/> \
        </outline> \
        <outline title='Elezea' text='Elezee' \
                 xmlUrl='http://feeds.feedburner.com/elezea'/> \
    </body> \
    </opml>";
    /*jshint quotmark: single */

    beforeEach(module('News'));

    beforeEach(inject(function (OPMLParser) {
        result = OPMLParser.parse(xml);
    }));


    it ('should parse the correct amount of feeds and folders', function () {
        expect(result.folders.length).toBe(2);
        expect(result.feeds.length).toBe(2);
        expect(result.folders[0].feeds.length).toBe(3);
        expect(result.folders[1].feeds.length).toBe(2);
    });


    it ('should default to title for feeds and folders', function () {
        expect(result.folders[0].name).toBe('Tesign');
        expect(result.folders[1].name).toBe('Nomadism');
        expect(result.feeds[0].name).toBe('test_text');
        expect(result.feeds[1].name).toBe('Elezea');
    });


    it ('should default to url for feeds if no title or text', function () {
        expect(result.folders[0].feeds[0].name).toBe('man');
        expect(result.folders[0].feeds[1].name).toBe(
            'http://afaikblog.wordpress.com');
        expect(result.folders[0].feeds[2].name).toBe(
            'http://informationarchitects.net/feed/');
    });


});
