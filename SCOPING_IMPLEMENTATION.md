# Dependency Scoping Implementation

## Overview

This implementation adds support for scoping PHP dependencies to avoid conflicts with other Nextcloud apps, specifically resolving the HTMLPurifier conflict between the News and Mail apps.

## Changes Made

### 1. Configuration Files

- **scoper.inc.php**: Configuration for php-scoper that:
  - Scans all dependencies using `composer show --tree`
  - Excludes platform dependencies, extensions, and the scoper itself
  - Prefixes all dependencies with `OCA\News\Vendor` namespace
  - Excludes `Psr\Log` from scoping (it's a PSR standard)

- **lib-vendor-organizer.php**: Helper script that:
  - Moves scoped dependencies from the `build/` directory to `lib/Vendor/`
  - Handles PSR-4, classmap, and files autoloading strategies
  - Strips the namespace prefix from the directory structure for cleaner paths

- **vendor-bin/php-scoper/composer.json**: Declares php-scoper as a binary dependency

### 2. Build Process

The composer.json now includes:
- `bamarni/composer-bin-plugin` as a dependency
- A `scope-dependencies` script that:
  1. Installs php-scoper in isolation
  2. Runs php-scoper to prefix all dependencies
  3. Organizes scoped code into `lib/Vendor/`
  4. Regenerates the autoloader

### 3. Code Updates

All PHP files that import vendor dependencies have been updated:
- `HTMLPurifier` → `OCA\News\Vendor\HTMLPurifier`
- `FeedIo\*` → `OCA\News\Vendor\FeedIo\*`
- `Favicon\*` → `OCA\News\Vendor\Favicon\*`
- `fivefilters\Readability\*` → `OCA\News\Vendor\fivefilters\Readability\*`
- `League\Uri\*` → `OCA\News\Vendor\League\Uri\*`

Files updated:
- lib/AppInfo/Application.php
- lib/Command/ExploreGenerator.php
- lib/Config/FetcherConfig.php
- lib/Fetcher/Client/FeedIoClient.php
- lib/Fetcher/FaviconDataAccess.php
- lib/Fetcher/FeedFetcher.php
- lib/Fetcher/Fetcher.php
- lib/Fetcher/IFeedFetcher.php
- lib/Scraper/Scraper.php
- lib/Service/FeedServiceV2.php
- lib/Service/ImportService.php
- tests/Unit/Command/ExploreGeneratorTest.php
- tests/Unit/Fetcher/FeedFetcherTest.php
- tests/Unit/Fetcher/FeedIoClientTest.php
- tests/Unit/Service/FeedServiceTest.php

### 4. Build Configuration

- **.gitignore**: Added `lib/Vendor/*` to exclude generated scoped code
- **Makefile**: Updated phpcs to ignore `lib/Vendor/*` when checking code style

## How to Use

### For Developers

After cloning and running `composer install`:

```bash
composer scope-dependencies
```

This will generate the scoped dependencies in `lib/Vendor/`.

### For CI/CD

The same command should be run after `composer install` in the build pipeline.

### For App Store Releases

The Makefile should be updated to run the scoping process before creating the appstore package.

## Known Limitations

Based on PR #3101, there are some known challenges:

1. **HTMLPurifier**: Uses non-PSR-4 class naming conventions which may require special handling
2. **Indirect dependencies**: Some dependencies like `League/Uri` may need explicit inclusion in the scoper configuration

These may require adjustments to the `scoper.inc.php` configuration file.

## Benefits

- **No conflicts**: Each app has its own isolated copy of dependencies
- **Version independence**: Apps can use different versions of the same library
- **Maintainable**: Changes are minimal and follow a clear pattern
- **Reversible**: Can be reverted by removing the scoping and reverting namespace changes

## Testing

To verify the implementation works:

1. Install dependencies: `composer install --no-dev`
2. Run scoping: `composer scope-dependencies`
3. Check that `lib/Vendor/` contains namespaced code
4. Run tests: `make test`
5. Build app package: `make appstore`
6. Install alongside Mail app and verify no conflicts

## References

- Original issue: Conflicts between News and Mail app when both use HTMLPurifier
- Inspiration: PR #3101 by @blizzz
- php-scoper documentation: https://github.com/humbug/php-scoper
