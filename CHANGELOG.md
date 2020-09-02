# Changelog
All notable changes to this project will be documented in this file.

## Unreleased

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
- Update to new BackgroudJob logic #704
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
- Prevent raw angluar templates from flashing on page load #429
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
