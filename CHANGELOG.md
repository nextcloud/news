# Changelog
All notable changes to this project will be documented in this file.
The format is mostly based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), older entries don't fully match.

You can also check [on GitHub](https://github.com/nextcloud/news/releases), the release notes there are generated automatically and include every pull request.

# Unreleased
## [25.x.x]
### Changed

### Fixed

# Releases
## [25.0.0-alpha10] - 2024-10-14
### Changed
- Require NC 29 or 30, dropped support for NC 28
- Require PHP 8.2 or higher
### Fixed
- Scroll position is not reset when switching between articles & feeds (#2548)
- Unread counter does not count down when folders or feeds are marked read (#2800)
- Query fetching status didn't work (#2800)
- Keyboard shortcuts are active even when searching (#2738)

## [25.0.0-alpha9] - 2024-10-03
### Fixed
- Use updated user agent when fetching feeds and favicons (#2788)
- Allow feed title to be null in DB. (#2745)
- Store HTTP last modified date from response header (#2724)
- Admin settings could not be saved (#2533)

## [25.0.0-alpha8] - 2024-07-07
### Changed
- Add support for moving feeds to another folder from the sidebar feed menu (#2707)
- Persist the filter state and show unread items by default (#2704)

### Fixed
- Fix undefined item when using `j` and `k` keyboards shortcuts in an empty feed (#2689)

## [25.0.0-alpha7] - 2024-06-10
### Changed
- added alternative development environment (#2670)
- Implement `j` and `k` keyboards shortcuts for navigating through feed items (#2671)
- Implement `s`, `i` and `l` keyboards shortcuts for staring current feed item (#2677)
- Implement `o` keyboards shortcut for opening the URL of current feed item (#2677)
- Implement `u` keyboards shortcut for marking current feed item read/unread (#2677)
- Implement highlighting of active feed item (#2677)

# Releases
## [25.0.0-alpha6] - 2024-05-07
### Changed
- Improve layout of feed item row (#2569)

### Fixed
- Reset content scroll position when feed item is changed (#2569)
- Fix link to feed in article header (#2569)

## [25.0.0-alpha5] - 2024-04-01
### Changed
- make occ news:updater:job exit with code 2 if last update was too long ago (#2590)
- Fix deprecated variable reference in ExportController.php (#2602)
- Add support for Nextcloud 29 (#2611)

## [25.0.0-alpha4] - 2023-01-25
### Changed
- Add DB index for news_feeds.deleted_at (#2526)

### Fixed
- PostgreSQL implement fix for marking over 65535 unread items as "read" (#2557)

## [25.0.0-alpha3] - 2023-12-24
### Changed
- Changed default page when starting app (#2515)
- Downgrade feed-io to 5.3.1 (#2497)

### Fixed
- Fix search support for Nextcloud 28 (#2432)

## [25.0.0-alpha2] - 2023-11-08
### Changed
- Add support for Nextcloud 28
- Use Nextcloud vue components for item list and article view (#2401)
- Fix aspect ratio of article images (#2401)

### Fixed
- Adjust search urls to match changed Vue routes (#2408)

## [25.0.0-alpha1] - 2023-10-24
### Changed
- Major Rewrite of the Frontend with Vue JS (#748)
  For comments and suggestions for the new UI, please use this: https://github.com/nextcloud/news/discussions/2388
- Set User Agent for curl in Scraper (#2380)
- Drop support for Nextcloud 26, Supported 27

## [24.0.0] - 2023-09-26
No major changes compared to 24.0.0-beta1.

## [24.0.0-beta1] - 2023-08-26
### Changed
- Drop support for Nextcloud 25, Supported: 26, 27 (#2316)
- Add a new command for occ `./occ news:updater:job` allows to check and reset the update job (#2166)
- Check for available http(s) compression options and use them (gzip, deflate, brotli) (#2328)
- Change and unify [cache](https://nextcloud.github.io/news/install/#cache) to use the instance ID of Nextcloud (#2331)

## [23.0.0] - 2023-08-16
No notable changes compared to 23.0.0-beta1

## [23.0.0-beta1] - 2023-08-09
### Changed
- Drop support for PHP 7.4 new min. version is php 8.0 (#2237)
- Upgrade feed-io to v5.1.3 (#2238)
### Fixed
- Some feeds missing items (#2236)

## [22.0.0] - 2023-07-23
### Changed
- Support deflate and gzip compression for HTTP response bodies (#2269)
- Broke apart old FAQ into different guides. Deprecated old FAQ (#2285)

## [22.0.0-beta2] - 2023-06-18
### Changed
-  allowEvalScript set to true (#2262)

## [22.0.0-beta1] - 2023-05-18
### Changed
- Drop support for Nextcloud 24 (#2223)
- Add support for Nextcloud 27 (#2223)

## [21.2.0] - 2023-05-06
### Changed
- Improve visibility of links in dark theme (#2215)

## [21.2.0-beta4] - 2023-04-16
### Fixed
- Fix audio player floating when scrolling in NC25+ (#2142)
- Fix sorting of folder names in select when adding subscription (#2090)

## [21.2.0-beta3] - 2023-04-16
### Changed
- Improve performance of item updates (#1322)
### Fixed
- Fix display issue in NC26+ (#2192)

## [21.2.0-beta2] - 2023-04-05
### Fixed
- Fix last_modified not updated when all items are marked as read (#2183)

## [21.2.0-beta1] - 2023-03-23
### Changed
- Use httpLastModified field for If-Modified-Since header when fetching feed updates (#2119)

## [21.1.0] - 2023-03-20
No notable changes compared to 21.1.0-beta1

## [21.1.0-beta1] - 2023-03-13
### Changed
- Remove unused background job OCA\News\Cron\Updater (#2137)
- (Nextcloud 26+) Add info card to the admin settings, showing last job execution (#2141)

## [21.0.0] - 2023-02-28
No notable changes compared to 21.0.0-beta1

## [21.0.0-beta1] - 2023-02-14
### Changed
- Drop support for Nextcloud 23 (#2077 )
- Make the "open" keyboard shortcut work faster (#2080)
- Implemented search for articles, results can only link to the feed. (#2075)
### Fixed
- Stop errors from the favicon library over empty values (#2096)

## [20.0.1] - 2023-01-19
### Fixed
- SyntaxError triggered when full-text is enabled with some items. (#2048, #2053)

## [20.0.0] - 2022-12-14
### Changed
- Drop support for PHP 7.3 (#2008)
- Dependency updates

## [19.0.1] - 2022-12-01
### Changed
- Dependency updates

## [19.0.0] - 2022-10-25
### Fixed
- Fix nested scrollbars in navigation (#411, #1958)

## [19.0.0-beta2] - 2022-10-23
### Fixed
- Fixed various keyboard navigation issues (#1953)
- Fix cron job warning notification layout on NC25 (#1953)

## [19.0.0-beta1] - 2022-10-22
### Changed
- Drop support for Nextcloud 22, NC 22 has reached it's end of life. (#1950)
- Add support for Nextcloud 25 (#1950)
### Fixed
- Corrected article compact title bar position in NC25 (#1944)
- Fixed "Mark read through scrolling" in NC25 and NC24 (#1944)

## [18.3.0] - 2022-10-21
### Fixed
- Remove setting for minimum purge interval since it is not used. (#1935)

## [18.3.0-beta1] - 2022-10-10
### Changed
- New administrator setting for deleting unread items automatically (#1931)

## [18.2.0] - 2022-09-28
### Fixed
- Fix the highlighted item when reverse ordering is selected (#1838)

## [18.2.0-beta2] - 2022-09-07
Fix for the read all function and spelling fixes.

## [18.2.0-beta1] - 2022-08-30
### Changed
- Ported the admin settings to vue (#2353)

### Fixed
- Fix PHP 8.1 deprecations (#1861)
- Document api item types (#1861)
- Fix deprecation warnings from Nextcloud server (#1869)
- Fix when marking all items as read, all items of the user are used in the sql query (#1873)
- Fix adding feed via the web-ui that was just deleted causing an error (#1872)

## [18.1.1] - 2022-08-12
### Changed
- Change autodiscover to only run after fetching the given url has failed (#1860)

## [18.1.1-beta1] - 2022-07-04
### Fixed
- Fix export of unread and starred articles failing due to postgres error (#1839, #1249)
- Fix broken API v1.3 (#1841)

## [18.1.0] - 2022-06-10
Due to #1766 some Feeds might now have items that have `null` set as author instead of `""` clients need to handle this.

## [18.1.0-beta2] - 2022-05-31
### Changed
-  If items of feed do not provide an author fallback to feed author (#1803)

## [18.1.0-beta1] - 2022-05-29
### Changed
- Add API v1.3 adding routes for starring/unstarring items by id and general fixes (#1727)
  https://nextcloud.github.io/news/api/api-v1-3/
- Improve styling of tables in articles (#1779)
- Allow fetching feeds that omit guid by using link as stand-in (#1785)

### Fixed
- Fix updated api not returning any item after marking item as read (#1713)
- Fix deprecation warning for strip_tags() on a null value (#1766)
- Fix selected item being set incorrectly when using default ordering or newest first ordering (#1324)
- Fix doubling the height of the content area (#1796)

## [18.0.1] - 2022-04-22
No major changes since the beta versions.

## [18.0.1-beta3] - 2022-04-18
### Fixed
- Fix import of items when feed does not exist (#1742)
- Fix malformed feeds (without GUIDs) stopping the update process (#1738)

## [18.0.1-beta2] - 2022-03-22
### Fixed
- Fix no item marked as read by Folder API due to mismatch in parameter name (#1703)

## [18.0.1-beta1] - 2022-03-09
### Fixed
- Fix only one item marked as read by Feed API (#1687)

## [18.0.0] - 2022-02-23
### Changed
- Change shortcut descriptions. (#1669)

### Fixed
- Fix spaces in passwords getting replaced with "+" (#1678)

## [18.0.0-beta1] - 2022-02-16
### Changed
- Drop support for Nextcloud 21

## [17.0.1] - 2021-12-08
### Fixed
- Fix catching network errors while fetching feed logos. (#1601)

## [17.0.0] - 2021-11-29
### Fixed
- fix link-icon-overlap in mobile-view (#1579)

## [17.0.0-beta1] - 2021-11-18
### Changed
- Drop support for Nextcloud 20 (#1514)
- Use better sql commands, that were not possible with Nextcloud 20 (#1514)
- Add support for Nextcloud 23 (#1585)

## [16.2.1] - 2021-11-15
### Fixed
- Catch network errors while fetching feed logos. (#1572, #1570)

## [16.2.0] - 2021-11-03
No notable changes compared to the beta versions.

## [16.2.0-beta2] - 2021-10-23
### Changed
- Updated "New Folder" and "All articles" icons to differentiate them from "Subscribe" and "All articles". (#1542)

### Fixed
- Mark the latest post in a feed as read when clicking on the right arrow key. (#1546)

## [16.2.0-beta1] - 2021-10-18
### Changed
- Add changelog and DCO notice to CONTRIBUTING.md (#1521)
- Download feed logos via guzzle to have better error handling (#1533)

## [16.1.0] - 2021-10-07
### Changed
- Remove dependency's large test files from release (#1519)
- Fix spelling of "receive" in log files (#1520)

Note: Nextcloud 20 support will be dropped in Oct 2021, this is very likely the last version to support Nextcloud 20. This also means that PHP 7.2, will no longer be supported by news.

# Releases
## [16.1.0-beta1] - 2021-09-02
### Changed
- Added new `news:updater:update-user` command to update the feeds of a single user (#1360).

### Fixed
- Removed spurious requests for `.../apps/news/%7B%7B%20::Content.getFeed(item.feedId).faviconLink%20%7D%7D` (#1488)

## [16.0.1] - 2021-08-02
### Changed
- Reimplemented relative time formatting as a filter (#1450)

### Fixed
- Set icon offset explicitly for navigation items (#1465)

## [16.0.0] - 2021-06-16
There are no additional changes compared to the latest beta.

### Changed
- News now requires a 64bit OS
- v2 API implementation (folder part)
- Implemented sharing news items between nextcloud users (#1191)
- Updated the news items table in DB to include sharer data (#1191)
- Added route for sharing news items (#1191)
- Added share data in news items serialization (#1191)
- Added tests for the news items share feature (#1191)
- Added sharing articles with nextcloud users (#1217)
- Added sharing articles on social media (Facebook, Twitter) or mail (#1217)
- Allow installation on Nextcloud v22
- Remove deprecated API endpoints and occ command (#935)
  - /api/v1-2/user
  - /api/v1-2/user/avatar
  - ./occ news:updater:all-feeds
- added feed search (#1402)

### Fixed
- allow calling `/items?getRead=false` without a feed/folder (#1380 #1356)
- newestId does not return newest ID but last updated (#1339)
- removed reference for deleted repair-steps (#1399)
- Fix NotNullConstraintViolation when sharing news items with users (#1406)

## [16.0.0-beta3] - 2021-06-16
### Changed
- added feed search (#1402)
### Fixed
- removed reference for deleted repair-steps (#1399)
- Fix NotNullConstraintViolation when sharing news items with users (#1406)

## [16.0.0-beta2] - 2021-06-01
### Changed
- Allow installation on Nextcloud v22
- Remove deprecated API endpoints and occ command (#935)
  - /api/v1-2/user
  - /api/v1-2/user/avatar
  - ./occ news:updater:all-feeds

### Fixed
- allow calling `/items?getRead=false` without a feed/folder (#1380 #1356)
- newestId does not return newest ID but last updated (#1339)

## [15.4.5] - 2021-05-26
### Fixed
- newestId does not return newest ID but last updated (#1339)

## [16.0.0-beta1] - 2021-05-22
### Changed
- News now requires a 64bit OS
- v2 API implementation (folder part)
- Implemented sharing news items between nextcloud users (#1191)
- Updated the news items table in DB to include sharer data (#1191)
- Added route for sharing news items (#1191)
- Added share data in news items serialization (#1191)
- Added tests for the news items share feature (#1191)
- Added sharing articles with nextcloud users (#1217)
- Added sharing articles on social media (Facebook, Twitter) or mail (#1217)

## [15.4.4] - 2021-05-21
### Fixed
- allow calling `/items?getRead=false` without a feed/folder

## [15.4.3] - 2021-05-05
### Fixed
- mitigate 32-bit issues by using `float` instead of `int` for microseconds (#1320)

## [15.4.2] - 2021-05-03
### Fixed
- revert accidentally merged dependency updates (#1332)

## [15.4.1] - 2021-05-03
### Fixed
- content of atom feeds is missing (#1325)
- Fix some of the favicon fetching errors (#1319)

## [15.4.0] - 2021-04-26
### Known Issue
If you use a 32bit OS your will run into #1320

See previous notes for a full overview.
### Fixed
- Fix search results not redirecting to the news app

## [15.4.0-rc1] - 2021-04-16
### Fixed
- Check category label for null (#1282)
- Do not return non-matching search items
- Resolve an issue with webservices missing items

## [15.4.0-beta3] - 2021-04-03
### Fixed
- Allow negative limits (#1275)
- Use boolean to check bool fields (#1278)

## [15.4.0-beta3] - 2021-04-03
### Changed
- Add BATS as integration tests (#1213)
- Update FeedFetcher to import categories from feeds (#1248)
- Update serialization of item to include categories (#1248)
- Make PHPStan stricter (#955)
- Search: Add folder search (#1215)
- Improve test coverage (#1263)
- Allow directly adding a feed without going through the discovery process (#1265)

### Fixed
- Do not show deleted feeds in item list (#1214)
- Fix update queries (#1211)

## [15.4.0-beta2] - 2021-02-27
### Fixed
- Item list not using ID for offset 2 (#1200)

## [15.4.0-beta1] - 2021-02-23
### Changed
- Remove outdated item DB code. ( #1056)
- Stop returning all feeds after marking folder as read. (#1056)
- Always fetch favicon (#1164)
- Use feed logo instead of favicon if it exists and is square (#1164)
- Add CI for item lists (#1180)

### Fixed
- Item list throwing error for folder and "all items" (#1180)
- Articles with high IDs can be placed lower than articles with low IDs (#1147)
- Feeds are accidentally moved on rename (#1189)
- Item list not using ID for offset (#1188)

## [15.3.2] - 2021-02-10
No changes compared to RC2

## [15.3.2-rc2] - 2021-02-10
### Fixed
- Missing certificate in signature file (#1143)

## [15.3.2-rc1] - 2021-02-10

### Fixed
- Refetching of already read articles after purging (#1142)

## [15.3.1] - 2021-02-06

### Changed
- New release without any changes compared to 15.3.0

## [15.3.1-rc3] - 2021-02-06

### Changed
- re-re-re-release of 15.3.0

## [15.3.1-rc2] - 2021-02-06

### Changed
- re-re-release of 15.3.0

## [15.3.1-rc1] - 2021-02-05

### Changed
- re-release of 15.3.0

## [15.3.0] - 2021-02-05

### Changed
- DB: Remove unused fields and migrate last_modified to signed, to support dates before 1970

### Fixed
- Release: create signature file (#1117)
- Articles are refetched after purging leaving them unread again (#1122)

## [15.2.2] - 2021-02-02

### Fixed
- Remove a .git dir from the release archive

## [15.2.1] - 2021-02-02

### Fixed
- Purging error "Undefined index: articlesPerUpdate"
- Clean up install files

## [15.2.0] - 2021-02-02

### Changed
You can now delete unread items via occ:
`occ news:updater:after-update --purge-unread [<purge-count>]`

### Fixed
- Item purger does not work with PostgreSQL (#1094)
- Export starred/unread correctly (#1010)

## [15.2.0-rc1] - 2021-01-31

### Changed
- Use signed integer for pubdate (#997)
- revert alternating row colors and increase row height (#1012)

### Fixed
- Fetch feed after creation (#1058)
- Implement missing item purger (#1063)
- Update FeedIO Response call and add tests
- Improve Psalm tests and dependency definition

## [15.2.0-beta2] - 2021-01-17

### Fixed
- opened state of folders is not restored (#1040)
- Argument 3 passed to OCA\News\Db\ItemMapper::makeSelectQuery() must be of the type bool, array given (#1044)
- Argument 2 passed to OCA\News\Db\ItemMapper::findAllNewFeed() must be of the type int, string given (#1049)

## [15.2.0-beta1] - 2021-01-11

### Changed
- Remove outdated feed DB code
- add background & hover for entries
- Improve spacing of open articles in compact mode (nextcloud/news#1017)

### Fixed
- `MissingNamedParameter` exception after upgrading to NC 21 beta5 (#1030)

## [15.1.1] - 2020-12-27

### Changed
- Remove PHPunit based integration tests

### Fixed
- Argument 2 passed to OCA\News\Db\FeedMapper::find() must be of the type int, string given #996

## 15.1.1-rc2

### Changed
- add background & hover for entries for compact mode

### Fixed
- Handle unauthorized users #985
- Call to undefined method OCA\News\Db\FeedMapperV2::find() #981

## 15.1.1-rc1

### Changed
- Remove outdated folder DB code

### Fixed
- Export Unread/Starred Articles gives Error Message #963
- Some events don't appear in feed #921

## 15.1.0

### Changed
- This version brings some major changes, be aware that some clients may not support this news version.

## 15.1.0-rc3

### Fixed
- Fix API allows access to folders of other users

## 15.1.0-rc2

### Changed
- Remove deprecated YouTube playlist API, playlists are no longer supported by news
- Locale-aware sorting for folders and feeds
- Deprecate User API: https://github.com/nextcloud/news/blob/master/docs/externalapi/Legacy.md#user

### Fixed
- Fix empty unread item count

## 15.1.0-rc1

### Changed
- Added changelog enforcer action
- Stop overloading DB ids
- Unittest commands and utilities
- Upload codecoverage to codecov.io
- Use foreign keys in db
- Fix delete api not working
- Move controllers to use V2 services

## 15.0.6

### Changed
- New release approach to prevent mistakes
- Re-release of 15.0.5

## 15.0.6-rc5

### Fixed
- Fix the new release process

## 15.0.6-rc4

### Fixed
- Fix the new release process

## 15.0.6-rc3

### Fixed
- Fix the new release process

## 15.0.6-rc2

### Fixed
- Fix the new release process

## 15.0.6-rc1

### Changed
- Updated dependencies
- New release process

## 15.0.5

### Fixed
- Fix exception when title is null #869

## 15.0.4

### Changed
- Update Explore page feeds and design #860

### Fixed
- Fix usage of at() in unittests #864
- Fix minor issues, prepare for foreign keys and check feeds #862
- Fix multiple results for guid_hash #861
- Fix missing type info of entities #858

## 15.0.3

### Changed

- Trim whitespaces in item titles #831
- update only relevant item fields #830

### Fixed

- Fix 'news:updater:after-update' command #832
- Define microtime as string #836
- Fix Application class loading in config #833

## 15.0.2

### Fixed

- Fix failing cron update #823

## 15.0.1

### Changed

- Update feed-io to v4.7.10

### Fixed

- Fix false cron notification #823
- Fix cron updater not working #819 #824
- Fix invalid UserId when logged out #822
- Fix autoPurge not working #824
- Fix undefined class constant 'Name' #824

## 15.0.0

### Changed

- Update feed-io to v4.7.9
- Feed autodiscovery #806
- Drop support before nextcloud 20 #794
- Move to modern SQL syntax #750
- Add management commands #804 #750
```shell script
./occ news:opml:export <userID>

./occ news:folder:add <userID> <name>
./occ news:folder:list <userID>
./occ news:folder:delete <userID>

./occ news:feed:add <userID> <URL>
./occ news:feed:list <userID>
./occ news:feed:delete <userID>
```

## 14.2.2

### Changed
- added support for Nextcloud 20 #781

### Fixed
- Update interval not saved to config file #783

## 14.2.1

### Fixed
- Update Interval documentation fixes #773
- Fix crash if full-text if feed doesn't provide an url #774
- Fix admin page not saving settings #776

## 14.2.0

### Changed
- JS library updates #737 #741
- Allow data URI scheme inside the body of an item #733
- Update to new BackgroundJob logic #704
- Update feed-io to v4.7.8

### Fixed
- fixed double escaped intro (not rendering html) #694
- translation updates and fixes
- import crashing on wrong guid_hash #770

## 14.1.11

### Changed
- Re-release of 14.1.10

## 14.1.10

### Changed
- Update feed-io to v4.7.1

## 14.1.9

### Changed
- Re-release of 14.1.8

## 14.1.8

### Changed
- Update feed-io to v4.7.0
- Update js dependencies

### Fixed
- Do not create spurious links in item body (#699)

## 14.1.7

### Changed
- Update feed-io to v4.6.0
- Update js dependencies

## 14.1.6

### Fixed
- Fixed 'If-Modified-Since' causing BAD REQUEST (#684)

## 14.1.5

### Fixed
- Fixed active menu item and reload for unread (#674)

## 14.1.4

### Changed
- Update for Nextcloud 19 (#627)
- Create Czech feed examples (#664)
- Always show the unread articles "folder" (#662)
- Update feed-io to v4.5.7

## 14.1.4-rc1

### Changed
- Basic Media-RSS support (#599)
- Database index improvements (#637)

### Fixed
- Call to a member function getUrlHash() on null" when adding a feed (#640)
- Don't install symfony/console via composer (#636)
- Fix for for ONLY_FULL_GROUP_BY (see #406) (Issue #80) (#407)
- Catch invalid feeds (#646)

## 14.1.3

## Changed
- Update feed-io to v4.5.3

## 14.1.2

## Changed
- Updated js packages

### Fixed
- Signature was missing

## 14.1.1

### Changed
- feed-io updated to v4.5.1 #613
- Automatically convert youtube channel urls in feed url #612

###  Fixed
- Scraper breaks feed fetching when it fails #606

## 14.1.0

### Changed
- Minimum PHP version is now 7.2
- Reimplement full-text scraping #563
- Update for nextcloud 18 #593 #583
- Update httpLastModified from the feed response #594

## 14.0.2

### Changed
- Get content:encoded of item if available #565
- update js and php dependencies #575

### Fixed
- Generate enclosure div only for audio & video #567

## 14.0.1

### Changed
- update js and php dependencies
- add Lifehacker RSS feed #557

## 14.0.0

### Changed
- Dropped support for Nextcloud 14 & 15 #494
- Switched to feedio 4.3 #494
- News now requires PHP 7.1 #494
- Removed UTF-8 warning (now included in server) #497
- UI improvements #505 #504 #467
- Add the 'Accept' header to requests #525

## 13.1.6

### Changed
- Replaced "Advanced settings" for feed credentials by a checkbox #488

### Fixed
- Fixed signature error caused by favicon cache #485

## 13.1.5

### Changed
- Added new android (and iOS) client 'newsout' to list #475
- News requires php 7.0 please update to 7.1+ if possible #476

### Fixed
- Fixed some feeds with a empty body #474
- Restored full text by default for some feeds #479
- Some smaller adjustments for the design #463 #464

## 13.1.4

### Fixed
- Another fix for failed updates #448
- Missing background back on news titles #451
- Encoding errors thrown by simplexml #457
- Allow '0' as last Modified date #458

## 13.1.3

### Fixed
- Rebuild of version 13.1.2 due to packaging failure

## 13.1.2

### Changed
- The active item is now highlighted by a thin orange line #434

### Fixed
- Highlight in compact mode #109
- Prevent raw angular templates from flashing on page load #429
- HTML elements where not rendered #428
- Provide UserAgent to prevent HTTP 403 errors #428

## 13.1.1

### Fixed
- Issue with reading a timestamp in a feed #413
- Passing credentials to a feed #419
- Background color everywhere #410
- Updater stopping when one feed fails #408

## 13.1.0

### Changed
- Switch from picoFeed to feed-io #258 #282
- Official codestyle is now PSR-2 #382
- `news:updater:all-feeds` now returns folderId #388
- new command `news:generate-explore` #402

### Fixed
- Broken multi-line news titles #395
- Better support for the dark-theme #392 #377
- Horizontal scrollbar in compact view #370 #389

## 13.0.3

### Changed
- Dependencies update #365 #364
- New feed #360
- Translations updates

### Fixed
- Broken on 32 bit systems #350 #355

## 13.0.2

### Changed
- Support for php 7.2 added
- Code refactoring
- Switched user agent to a more readable one #328
- Date added to export .opml #345
- Sticky compact mode headers #338

### Fixed
- Fix broken signature issue #347

### Known issues
- Broken on 32 bit systems #350

## 13.0.1

### Fixed
- Fixed design on mobile and desktop
- Compact view fixes for Firefox

### Known issues
- Integrity error for missing .htaccess file

## 13.0.0

### Changed

- Nextcloud 14 compatibility #312, #319
- API support for new login flow #303
- Dropped support for Nextcloud 13

## 12.0.4

### Fixed

- Re-release of 12.0.3 but without signature, because it was broken.

## 12.0.3

### Fixed

- Packaging problem in 12.0.2

## 12.0.2

### Fixed

- Fix the error RSS source overlay #281
- MySQL UTF8Mb4 link to current docs #285
- Remove development dependencies from archive #288

### Changed

- Add newsboat compatible client to README.md #256
- Move transifex config for updated l10n script #267
- Core: Fix compatibility with nextcloud codestyle #280
- JS: Fix gulp issues with new node versions #293

## 12.0.1

### Fixed

- Fix various styling and usage issues introduced with beta changes

## 12.0.0

### Changed

- Replaced url of utf8mb4 instructions to stack exchange with nextcloud-specific page, #181
- Dropped support for Nextcloud 12

## 11.0.5

### Fixed

- Fix order of update scripts for cron updates, #172

## 11.0.4

### Fixed

- Do not fail MySql utf-8 support check on other platforms for various reasons, #163
- Include the correct files for displaying warnings, #166

## 11.0.3

### Fixed

- Display database charset warning inside the app instead of failing installation/update

## 11.0.2

### Fixed

- Fail early when an incorrectly configured MySql/MariaDB instance is found to prevent update errors and data loss
- Do not mark articles read when shift + a + ctrl/meta/alt is pressed
- Re-order mark read to first position

## 11.0.1

### Fixed

- Fix admin section on latest master (fix only works with a version newer than 12.0.0-beta4), #145
- Do not show tooltip when hovering over headlines in compact mode, #151

## 11.0.0

### Removed

- Dropped support for Nextcloud 11

### Fixed

- Fix articles appearing as unread when updating to Nextcloud 12 beta
- Partly fix frontend styles that were changed in 12

## 10.2.0

### Added

- Experimental support for Nextcloud 12

### Changed

- Update picoFeed and HTMLPurifier libs to the latest version

### Fixed

- Parse CSRF token directly from source rather than using a global variable which never got updated. This fixes the login warnings that appeared after you left the News app open for a longer period of time which forced you to reload the page.

## 10.1.0

### Added
- Show favicons in expanded mode's subtitle section if a folder is viewed

### Changed
- Updated PHP and JS libraries

## 10.0.0

### Removed
- Dropped support for Nextcloud 10

### Added
- Include changelog in release download
- More App Store improvements

### Fixed
- Switch to new update API
- Do not fail to mark items as read if they do not exist on the server anymore when using the API
- Show "Settings" label for settings area button
- Changed explore url colors to fit Nextcloud theme

## 9.0.4

### Fixed
- Pad API last modified timestamp to milliseconds in updated items API to return only new items. API users however need to re-sync their complete contents, #24
- Do not pad milliseconds for non millisecond timestamps in API

## 9.0.3
### Security
- Prevent browsers like Chrome from auto-filling your Nextcloud login credentials into Basic Auth form. This could lead users to accidentally saving their credentials in the database and disclosing them to the feed source when the feed is added/updated

## 9.0.2

### Fixed
- Do not return millisecond lastModified timestamps in API, #20

## 9.0.1

### Removed
- Drop PHP 64bit requirement due to helpful suggestions

## 9.0.0

### Removed
- Removed support for Nextcloud 9 and older
- Removed support for PHP 32bit
- Removed ability to update to 9.0.0 from versions prior to 8.8.0 due complex database schema changes

### Added
- Further cleanups for the Nextcloud app store

### Fixed
- Fix cronjob updates on Nextcloud 10
- Limit iframes to 100% width, #10

## 8.8.3

### Added
- Cleanups for the Nextcloud app store
