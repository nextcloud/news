owncloud-news (5.3.0)
* **Enhancement**: Make it possible to search articles in the search field. To migrate older articles to this functionality, check the README section "Updating from versions prior to 5.3.0", #185
* **Enhancement**: Stop video and audio playback if a new video or audio file is being played
* **Enhancement**: Do not stop playing podcasts when the feed is being reloaded or changed, #156
* **New dependency**: Bump required ownCloud version to 8.1

owncloud-news (5.2.8)
* **Bugfix**: Fix feed subscribe to URL
* **Enhancement**: Provide checksums for all files

owncloud-news (5.2.7)
* **Enhancement**: If the app is called with the subscribe\_to url parameter (e.g. **?subscribe_to=http://path.to/feed**) the feed input is prefilled with the address
* **Enhancement**: Register the app as feed reader in Firefox

owncloud-news (5.2.6)
* **Bugfix**: Update picoFeed to strip out contents of script and style tags which were previously converted into plain text, #723

owncloud-news (5.2.5)
* **Bugfix**: Fix flying loading icon since snap.js does not seem to want to fix it
* **Bugfix**: Turn all http iframes into https iframes
* **Bugfix**: Set a CSP to display external media on master

owncloud-news (5.2.4)
* **Enhancement**: Add a new API route to check for the status and possible problems

owncloud-news (5.2.3)
* **Enhancement**: Push explore button at the bottom of the feed list
* **Enhancement**: When passing a negative batchSizes to the item API, all items will be returned

owncloud-news (5.2.2)
* **Security**: Only allow YouTube and Vimeo to embed iframes if they use HTTPS to prevent mixed active content iframe attacks

owncloud-news (5.2.1)
* **Bugfix**: Fix admin settings by using the correct config path

owncloud-news (5.2.0)
* **New dependency**: Require iconv php module (which is required by picoFeed)

owncloud-news (5.1.1)
* **Bugfix**: Use the correct scrolltop position for pull to refresh to not reload the feed when jumping back to an article using keyboard shortcuts

owncloud-news (5.1.0)
* **Backwards incompatible change**: Break client side plugin API to combat limitations that make it hard to impossible to get the DOM element
* **New dependency**: Bump required ownCloud version to 8 beta 2
* **Enhancement**: Expose feed ordering parameter in API

owncloud-news (5.0.1)
* **Enhancement**: Show error messages when authentication or network related errors appear
* **Enhancement**: Show a pull to refresh area if you are at the very top and jump to the previous article using either page up or a jump to previous article shortcut. If this area is already visible reload the page
* **Enhancement**: Make it possible to overwrite the global ordering for certain feeds

owncloud-news (5.0.0)
* **New dependency**: Bump required ownCloud version to 8
* **Enhancement**: Set the rel="noreferrer" attribute for all links that point to external articles to enhance privacy

owncloud-news (4.3.2)
* **Bugfix**: Update picoFeed to fix an HTTP cache problem where only one match (Etag or Last-Modified) was required to mark a feed as not modified. This can cause some feeds to not update if one cache parameter is always the same
* **Enhancement**: If a feed is added without the protocol, prepend https:// instead of http:// (e.g. adding cnn.com would result in https://cnn.com being added)

owncloud-news (4.3.1)
* **Bugfix**: Update picoFeed to fix feed format detection, e.g. http://aroundthebloc.podbean.com/feed/ was previously not recognized as a valid feed
* **Enhancement**: Add thecodinglove.com and der-postilion.com enhancers
* **Enhancement**: Make it possible to define your own article actions by adding article action plugins

owncloud-news (4.3.0)
* **Backwards incompatible change**: Refactor the Python updater into a Python package. To stay with the old behavior without installing the package use **python3 -m bin/updater/owncloud_news_updater YOUROPTIONS**
* **Backwards incompatible change**: Use seconds instead of minutes for the Python updater interval
* **New dependency**: Require requests 2.5.0 for the Python updater
* **Enhancement**: Ship a systemd and sysvinit script + installer for the Python updater to run the updater in the background
* **Enhancement**: Log errors from Python updater to stderr
* **Enhancement**: Add metronieuws.nl article enhancer

owncloud-news (4.2.6)
* **Bugfix**: Wording fixes
* **Enhancement**: Add linuxtoday.com article enhancer
* **Enhancement**: Make expand entries in compact view after jumping to next/previous feed optional with a setting

owncloud-news (4.2.5)
* **Bugfix**: Fix keep unread shortcut in compact view

owncloud-news (4.2.4)
* **Bugfix**: Clicking the button to open a website in compact view now marks the article read
* **Bugfix**: Make read on scroll more sensitive in compact view to mark it read without having to completely scroll over the entry
* **Enhancement**: Better explanation for reverse ordering setting
* **Enhancement**: Do not expand entries in compact view when using the jump to next/previous article shortcuts since this makes it harder to keep an overview and the exact same behavior is available in expanded view anyways

owncloud-news (4.2.3)
* **Bugfix**: Fix cron update

owncloud-news (4.2.2)
* **Enhancement**: Use a light gradient at the bottom of the feed when no items are left to autopage

owncloud-news (4.2.1)
* **Bugfix**: Rewrite relative URLs

owncloud-news (4.2.0)
* **Security**: Fix [XEE](https://www.owasp.org/index.php/XML_External_Entity_(XXE)_Processing) vulnerability in picoFeed RSS library. All versions starting from 4.0.0 are affected
* **Enhancement**: Add admin setting to set a custom explore service URL
* **Enhancement**: Add explore button and show explore button on startup
* **Enhancement**: Show a hint when no articles are available
* **Enhancement**: Add spiegel.de enhancer
* **Bugfix**: Fix compact view in firefox 34 and browsers using the new flexbox model
* **Bugfix**: Show spinner when autopaging
* **Bugfix**: Remove race condition when fetching articles that sometimes shows read articles if the user clicks show on only unread and the article request finishes first before the settings request is finished
* **Bugfix**: Ignore Atom updated tag if it is earlier than the published tag
* **Bugfix**: Do not log errors that a subscription could not be found when http cache says nothing changed

owncloud-news (4.1.3)
* **Enhancement**: Fade out folders and feeds instead of just hiding them when the unread count drops to 0 and only unread articles should be shown

owncloud-news (4.1.2)
* **Enhancement**: Order feeds and folder sorting by title and name rather than by id

owncloud-news (4.1.1)
* **Enhancement**: shortcut **a** scrolls to the currently active feed in the navigation
* **Enhancement**: next/previous feed/folder shortcuts now scroll to the active entry if it is not fully in view

owncloud-news (4.1.0)
* **Backwards incompatible change**: Time used for updating when using the Python updater is now subtracted from the given interval meaning: if you specify 30 seconds as interval and the update takes 25 seconds, it will sleep for 5 seconds before running the next update
* **Enhancement**: Show a message if ajax cron is used to inform people of improperly configured cron
* **Enhancement**: Allow to specify configuration file for Python updater
* **Enhancement**: Add short command line options for the Python updater
 * -u, --user
 * -p, --password
 * -i, --interval
 * -s, --timeout

owncloud-news (4.0.4)
* **Bugfix**: Do not indent slashdot.org italic elements
* **Bugfix**: Hide folders when only showing unread articles after marking all articles as read

owncloud-news (4.0.3)
* **Bugfix**: Fix unstarring and starring articles added before 4.x

owncloud-news (4.0.2)
* **Bugfix**: Fix CORS headers for getting the version via the API
* **Enhancement**: Transform YouTube urls to allow subscribing to channels and playlists

owncloud-news (4.0.1)
* **Bugfix**: Fix heise.de encoding issues

owncloud-news (4.0.0)
* **Backwards incompatible change**: Calculate item ids differently which leads to unpreventable readding of read articles
* **Backwards incompatible change**: Get rid of simplePieCacheDuration setting, remove this setting from your data/news/config/config.ini after upgrading to 4.\*.*
* **Backwards incompatible change**: Use three numbers for versioning because core bug with versions seems fixed
* **Enhancement**: Add maxRedirects setting in config.ini
* **Enhancement**: Add maxSize setting in config.ini
* **Enhancement**: Get rid SimplePie feed parser library and switch to PicoFeed because SimplePie is unmaintained and full of bugs
* **Enhancement**: Faster feed updates due to proper HTTP cache headers thanks to picoFeed
* **Enhancement**: Use ownCloud internal proxy settings
* **Enhancement**: Allow to turn off article purging by setting a negative number
* **Enhancement**: Make article cleanup faster
* **Bugfix**: Set the correct header for the FirefoxOS manifest.webapp
* **Bugfix**: Fix article cleanup on sqlite

owncloud-news (3.406)
* **Enhancement**: Make config.ini editable in the admin interface

owncloud-news (3.405)
* **Bugfix**: Fix mobile view for ownCloud 7
* **Enhancement**: Add shortcuts for jumping to next/previous folder
* **Enhancement**: Add keyboard shortcuts overview
* **Enhancement**: More space for checkboxes in settings overview

owncloud-news (3.404)
* **Bugfix**: Fix freeze when a folder is selected, the previous folder has 0 visible subfeeds and the **d** shortcut is pressed to jump the the previous feed

owncloud-news (3.403)
* **Bugfix**: Use correct route for python updater

owncloud-news (3.402)
* **New dependency**: Bump required ownCloud version to 7.0.3 (RC1 is also supported)
* **Bugfix**: Use **News** as the app navigation entry name across all languages to fix translation errors and because it makes sense

owncloud-news (3.401)
* **New dependency**: SimpleXML
* **Enhancement**: Added Slashdot.org enhancer to get rid of tons of advertising that create a lot of whitespace when using adblock
* **Enhancement**: When a folder or feed of a folder is selected, select that folder in the add new feed section

owncloud-news (3.302)
* **Bugfix**: Fix text overflow for subscriptions and starred feed
* **Bugfix**: Styles for h4, h5 and h6
* **Bugfix**: Support 7.0.3 alpha release
* **Enhancement**: Minify CSS
* **Enhancement**: Minify JavaScript

owncloud-news (3.301)
* **New dependency**: ownCloud >= 7.0.3
* **Security**: Fix possible [XEE](https://www.owasp.org/index.php/XML_External_Entity_(XXE)_Processing) due to race conditions on php systems using **php-fpm**
* **Bugfix**: Fix issue that prevented going below 1 unread count in the window title
* **Enhancement**: Show a button to refresh the page instead of reloading the route for pull to refresh

owncloud-news (3.202)
* **Security**: Fix [XEE](https://www.owasp.org/index.php/XML_External_Entity_(XXE)_Processing) on systems with libxml < 2.9 which allows attackers to add a malicious feeds that can include any file content that is readable by the webserver
* **Enhancement**: Provide manifest to make News an installable web app on Firefox OS
* **Enhancement**: Switch keep unread and star icon

owncloud-news (3.201)
* **New dependency**: Minimum libxml version: 2.7.8
* **Bugfix**: Move open website icon in compact view to the left of the title
* **Bugfix**: SimplePie: Do not break if url encoded links contain non ASCII chars
* **Bugfix**: Favicon should stay in place if you expand an article in compact view
* **Bugfix**: Go back to debug level logging for feed updates
* **Bugfix**: Fix heise.de feeds

owncloud-news (3.105)
* **Bugfix**: Various wording fixes
* **Bugfix**: Do not use Import/Export caption for settings buttons to avoid UI bugs in translated versions
* **Bugfix**: Catch all exceptions for feed update to not also not fail completely after db errors
* **Bugfix**: Register error handler only once
* **Bugfix**: Fix German translation
* **Bugfix**: Load app config also when in cron mode
* **Bugfix**: Log feed create and update errors to owncloud log as error because debug is broken

owncloud-news (3.104)
* **Bugfix**: Backport ownCloud CSS z-index fix to ownCloud 7 for settings popup that made it difficult to access the administration tab

owncloud-news (3.103)
* **Bugfix**: Turn all errors into exceptions to prevent failing all feed updates if one update runs into an error

owncloud-news (3.102)
* **Bugfix**: Fix z-index for stable7 so menu buttons dont overlap content in mobile view
* **Bugfix**: Use public namespace for template script and style template functions

owncloud-news (3.101)
* **Bugfix**: Fix remove YouTube autoplay on libxml versions < 2.6
* **Enhancement**: Backport to ownCloud 7

owncloud-news (3.003)
* **Bugfix**: Correctly toggle title of star and keep unread icons
* **Bugfix**: Fix bug that prevented the webinterface's update every 60 seconds
* **Enhancement**: Less padding right on mobile phone
* **Enhancement**: Expanded view: remove date on mobile phone
* **Enhancement**: Compact view: click on title should remove ellipsis
* **Enhancement**: Ignore autoPurgeMinimumInterval setting if it is below 60 seconds since anything lower than that may hurt user experience
* **Enhancement**: Remove YouTube autoplay from all articles

owncloud-news (3.002)
* **Bugfix**: If a folder is selected, the f and d shortcuts will jump to the previous or next folder subfeeds
* **Bugfix**: Fix o shortcut in expanded view
* **Bugfix**: Make **em** tag cursive and black
* **Enhancement**: Cut mark read timeout in half
* **Enhancement**: Show full unread count when hovering over the unread count

owncloud-news (3.001)
* **New dependency**: Minimum ownCloud version: 8
* **New dependency**: Minimum PHP version: 5.4
* **Breaking Change**: Plugin API: BusinessLayer has been renamed to Service, (FeedBusinessLayer -> FeedService) and different exceptions are now thrown to make failure better distinguishable, accessing the BusinessLayer links to the Service equivalents to keep compability
* **Bugfix**: Disable drag and drop if a feed is in an invalid state
* **Bugfix**: Focus scrollable area if the page is loaded initially
* **Bugfix**: Immediate feedback if folder/feed exists already on the client side
* **Bugfix**: Reload current folder if a feed is moved into or out of it
* **Bugfix**: Pixel perfect folder and feed inputs
* **Bugfix**: Do not include starred count of deleted folders and feeds
* **Bugfix**: Display error messages when folder rename failed
* **Bugfix**: Enter works now as submitting a form for every input
* **Bugfix**: Import feeds from a very large OPML file in chunks to prevent server slowdown
* **Bugfix**: Folder names are not uppercased anymore due to possible naming conflicts caused by folders being created through the API
* **Bugfix**: Loading icon is now displayed until all feeds and folders are loaded
* **Enhancement**: Correctly float heise.de info box
* **Enhancement**: Allow to turn off marking read when scrolling
* **Enhancement**: Allow to order by oldest first
* **Enhancement**: Add clientside routing
* **Enhancement**: When importing OPML use the feed title if given
* **Enhancement**: Make hover buttons available under a menu button
* **Enhancement**: Slim down appstore build
* **Enhancement**: Allow to specifiy custom CSS rules for each feed
* **Enhancement**: Compact view: Title ellipsis
* **Enhancement**: Compact view: Show source as favicon
* **Enhancement**: Compact view: Add keep unread button
* **Enhancement**: Compact view: Expand item when jumping to it with a keyboard shortcut
* **Enhancement**: Move undo feed/folder deletion button into the navigation bar
* **Enhancement**: Add create folder form in addition to the subscribe form
* **Enhancement**: Optimize for mobile
* **Enhancement**: Move show unread articles setting into the settings area
* **Enhancement**: New add feed design
* **Enhancement**: API: add parameter to get items by oldest first
* **Enhancement**: Keyboard Shortcut: r to reload the current feed
* **Enhancement**: Keyboard Shortcut: f to load the next feed/folder
* **Enhancement**: Keyboard Shortcut: d to load the previous feed/folder
* **Enhancement**: Set useragent for fetching feeds
* **Enhancement**: Also support video enclosures
* **Enhancement**: Port clientside code from CoffeeScript to JavaScript
* **Enhancement**: Respect theme name in tab title

owncloud-news (2.003)
* Use correct url for folder and feed api update methods

owncloud-news (2.002)
* Better check for news app dependencies
* Security: Don't send CORS Allow-Credentials header to prevent CSRF

owncloud-news (2.001)
* Delete folders, feeds and articles if a user is deleted
* Also remember collapsed folders on postgres
* Fix bug that would prevent articles from being deleted if a folder is marked as deleted on sqlite and postgres
* Require ownCloud 6.0.3
* Remove html tags from feed titles
* Port to built in core App Framework and thus removing the need to install the App Framework
* Fix bug that would break news if a feed contains audio enclosures
* Prepare news to work with proxies once [Simple Pie is patched](https://github.com/simplepie/simplepie/pull/360)
* Update HTMLPurifier to incorporate security fixes from the [newest release](http://htmlpurifier.org/news/2013/1130-4.6.0-released)
* **New dependency**: python-requests library for the python updater script

owncloud-news (1.808)
* Also focus article area when clicking on all unread link
* Autofocus article area by default on load
* Instantiate only one itemcontroller, prevents tons of requests when autopaging
* Fix bug that would disable keyboard shortcuts after the star icon has been clicked

owncloud-news (1.807)
* Don't crash if an HttpException occurs in the python updater
* Add API call to rename a feed
* Don't collapse articles in compact mode if you select a new article to prevent the scroll position from changing

owncloud-news (1.806)
* Disable simple pie sanitation (we use HtmlPurifier) to speed up update
* Only purify articles if they will be added to the database
* Fix XSS vulnerability that was caused by not purifing the body of imported articles
* Also float the first picture in the first div left (fixes ugly images for golem.de feed)

owncloud-news (1.805)
* Hide editing tools in invalid feed dialog
* Use local copies of icons to reflect changes in oc 7

owncloud-news (1.804)
* Make it possible to rename folders
* Do not show rename action for feeds that could not be added
* Trim URL in invalid feed modal
* Article enhancer for
  - niebezpiecznik.pl

owncloud-news (1.803)
* Use the feed link if an item doesn't specificy a link
* Don't fail if article url does not exist but fall back to feed url
* Article enhancers for
  - nerfnow.com
  - sandraandwo.com
  - geek-and-poke.com
  - nichtlustig.de
  - thegamercat.com
  - twokinds.keenspot.com

owncloud-news (1.802)
* Increase performance by not making auto page requests anymore if the last result didn't contain any articles

owncloud-news (1.801)
* Add ability to rename feeds
* Compact view
* Add shortcut to expand items in compact view

owncloud-news (1.605)
* Adding feeds does not block the input box any more
* Always display empty folders
* Better description for hiding/showing read articles
* Make it easier to add simple article enhancers by moving the data into a JSON configuration file
* Add regex based item enhancers
* Article enhancers for
  - buttersafe.com
  - twogag.com
  - cad-comic.com
  - penny-arcade.com
  - leasticoulddo.com
  - escapistmagazine.com
  - trenchescomic.com
  - lfgcomic.com
  - sandraandwoo.com
  - theoatmeal.com
  - loldwell.com
  - mokepon.smackjeeves.com

owncloud-news (1.604)
* Use 64bit integers to prevent running out of ids after a year for large installations
* Fix postgres feed queries with correct group by
* Article enhancers now transform relative links on a page to absolute links

owncloud-news (1.603)
* Fix JavaScript errors which prevented the translation elements from being removed

owncloud-news (1.602)
* Remove removed class from container
* Go back to allow feeds per url from input
* Added ThemeRepublic.net Enhancer
* Remove unsigned from articles_per_update column to not fail with a weird error
* Manually convert &apos; to ' in title and author fields of articles because its not build into PHP
* Fix localisation of app name in tab title

owncloud-news (1.601)
* Remove Google Reader import
* Replace Google Reader import with export and import of unread and starred articles
* Autopurge limit is now added to the number of articles each feed gets when it updates
* Fix CORS headers for OPTIONS request deeper than one level
* Use before and after update cleanup hooks to make sure that read items are not turned unread again after an update. This breaks custom updaters. The updater script has been adjusted accordingly
* Implement pull to refresh
* Use Bower for JavaScript dependency management

owncloud-news (1.404)
* Fix bug on postgres databases that would not delete old articles

owncloud-news (1.403)
* Respect encoding in feed enhancers
* Hotfix for update on posgresql databases

owncloud-news (1.402)
* Add possibility of adding more than one xpath for article enhancer
* Fix bug that wouldn't delete old read articles

owncloud-news (1.401)
* Add possibility to hook up article enhancers which fetch article content directly from the web page
* Add article enhancer for explosm.net to directly fetch comics and shorts
* Possible backwards incompatible change by using the link provided by simplepie instead of the user for the url hash. This prevents duplication of the feed when adding a slightly different feed url which points to the same feed and allows a speedup from O(n) to O(1) for article enhanchers
* Add an option route for the API which handles the CORS headers to allow webapplications to access the API
* Also allow youtube and vimeo embeds that start with // and https:// instead of only allowing http
* ownCloud 6 compability fixes
* Throw proper error codes when creating invalid folders through the API
* More whitespace to fit ownCloud 6 design better
* Increased unread count from 99+ to maximum of 999+ because there is now more space
* Use a configuration file in data/news/config/config.ini to not overwrite uservalues on update
* Fix bug in python updater api that would trigger a method not allowed error
* Add first run page that shows all options expanded if there are no feeds and the app is launched for the first time

owncloud-news (1.206)
* Also handle URLErrors in updater script that are thrown when the domain of a feed is not found

owncloud-news (1.205)
* Also allow magnet urls in articles
* When jumping to the next item after the last one, also mark the last item as read

owncloud-news (1.204)
* Fix problem that caused python updater script to exit because of maximum recursion
* Add an option to testrun an update with the updater script

owncloud-news (1.203)
* Decode the title twice to fix special characters in HTML content in the title, author and email
* Scroll to the bottom once you hit the show all button to prevent tedious scrolling
* Add an API to make ownCloud cron updates optionally. This can be used to write an update script which can be threaded to dramatically speed up fetching of feeds and reduce the used memory to run the update
* Add a Python update script which threads the updates
* Make it possible to turn off cron updates
* Strip all HTML tags from the author and title
* Sanitize urls on the server side to prevent clients from being affected by XSS
* Use a default batch value for the API
* Don't fail to import OPML which uses the title instead of text attribute (i.e. OPML created by Thunderbird)

ownCloud-news (1.202)
* Fixed a bug in the API routes that would request an uneeded id when creating a feed
* Log unimportant errors on debug level instead of error level

ownCloud-news (1.201)
* Add shortcut 'o' which opens the current article in a new tab
* Speed up updating of feeds by more than 100% by not fetching favicons and unneeded https/http variants
* Moved to new RESTful API to fix API bugs
* Fix bug that wouldnt mark items as read if they were set read in a folder, feed or all read request
* Style blockquotes
* Fixed small CSS bug that would remove the bottom margin of every last element
* Increased allowed feed timeout from 10 to 60 seconds
* Make it possible for plugins to turn off mark read on scroll
* Removed HTML Purifier unit tests which made it possible to trigger XSS using a crafted URL
* Don't update existing articles anymore if the pubdate changes to prevent weird update behaviour
* If articles dont provide a pubdate, use the date when the article was saved in the database
* Display download link if audio file is not playable

ownCloud-news (1.001)
* Also use monospace for pre tag
* Fix bug that would prevent feed updates when feeds or folders are deleted

ownCloud-news (0.104)
* Also html decode the links to the page to not break on nyaa torrents

ownCloud-news (0.103)
* Fixed a bug that prevented deleting feeds when a folder was deleted

ownCloud-news (0.102)
* Fix marking read of all articles and folders on mysql and postgres
* Fix bug that would still show items after its feed or folder has been marked as deleted
* Fix bug that would show invalid unread count for feeds whose folders were deleted

ownCloud-news (0.101)

* show 99+ as max unread count
* only show delete button if feed is active
* Fix a bug that would show the loading sign when updating the web ui and would reload all items while reading
* More accurate padding when hovering over a feed
* Require 5.0.6 which includes a fix for the core API
* Don't highlight the tab title when there are no new unread feeds
* Make only one http request for reading all items and all items of a folder
* Fix bug that would prevent marking a feed as read when its created and no other feeds are there
* Fix bug that would prevent readding of a feed when a folder containing the feed was deleted
* Also send newest item id in the api when creating a feed
* Fix a bug that would mark the items on the right side as read regardless of feed or folder id
* Fix a bug that would a feed from being added when he was deleted and then another feed was deleted
* When a feed is deleted and not undone in 10 seconds and the window is closed, delete him
* Fix bug that would make links containing hashes unclickable
* Fix bug that broke the News app on postgresql
* Fix bug that prevented the API from serving items

ownCloud-news (0.98)

* Fix XSS vulnerability in sanitation for json import
* Fix XSS vulnerability in feed and title link

ownCloud-news (0.97)

* Fix XSS vulnerability in sanitation
* Properly show embedded vimeo and youtube videos

ownCloud-news (0.96)

* Always open links in new tabs
* Better exception handling for controllers
* Implemented API
* Fixed a bug that would prevent update of the News app
* Log feed update errors
* Make add website button less obstrusive
* Fixed problem with sites that updated too frequently like youtube
* Also update folders

ownCloud-news (0.95)

* Fix a bug that would cause PHP 5.3 to fail while parsing utf-8
* Reverted the keep unread checkbox styling from a button back to a normal checkbox
* Fix an issue that prevented scrolling when drag and dropping a feed in to a new folder
* Do not mark items as read that have not yet been displayed to the user
* Autopage if there are 10 items left instead of 4 times the scroll area height, fixes a bug that would not load new items if the entry was too big
* Prefer website favicon over channel image, fixes wordpress blog favicons
* Add all businesslayer methods for the current API spec
* API Specification Draft
* Fix a bug that would cause words in the headlines to always be wrapped
* Fix a bug that would cause the ellipsis on the "Add Website" entry to be too short
* Provide undo dialog for feed and folder deletion
* Do not preload audio in podcast feed
* Use utf-8 charset header in JSON responses to prevent broken headlines
* Move the rss cache files into the ownCloud data directory
* Autopurge: limit read articles per feed instead of using a global limit
* Use tooltips for delete and mark read button
* Also load the newest unread and starred count when a feed is loaded
* Do not request updates from the client but only use the background job to make the app faster
* Add a way to import articles from Google Reader Takeout Archive
* Fix a bug in favicon fetcher that would not fetch certain favicons
* Add OPML export
* Show translated relative dates for articles
* Show immediate feedback when adding a feed or folder
* Add keyboard shortcuts
* Do not show unread articles feed when there are no feeds
* Filter HTML tags from headlines and authors
* If the article author has no name use the mail
* Show full feed name on hover
* If feed has no name, use its URL
* Do not update articles all the time that have no pubdate
* Prevent app from making ownCloud unusable if the App Framework is not installed
* Focus the articles area when a feed is being clicked so page up/page down work
* Use a delay for drag and drop to make experience on Mac OS X better
* Show unread count in the tab title
