# Changelog
All notable changes to this project will be documented in this file.

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