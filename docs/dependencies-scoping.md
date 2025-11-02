# PHP Dependency Scoping

## Overview

The News app uses **dependency scoping** to isolate its PHP dependencies and prevent conflicts with other Nextcloud apps. All vendor dependencies are prefixed with the `OCA\News\Vendor` namespace and stored in `lib/Vendor/` instead of the standard `vendor/` directory.

## Why Scoping?

**Problem:** When multiple Nextcloud apps use the same PHP library (e.g., different versions of feed-io), PHP cannot handle duplicate class declarations, leading to fatal errors.

**Solution:** Each app gets its own isolated copy of dependencies with a unique namespace prefix, preventing conflicts.

## How It Works

### Scoping Process

When you run `composer scope-dependencies`:

1. **Install php-scoper** (in isolation via bamarni/composer-bin-plugin)
2. **Scan dependencies** using `composer show --tree --no-dev`
3. **Prefix namespaces** with `OCA\News\Vendor` using php-scoper
4. **Organize files** into `lib/Vendor/` directory structure
5. **Generate autoloader** for scoped dependencies

### Configuration Files

- **scoper.inc.php**: Defines which dependencies to scope and excludes PSR standards
- **lib-vendor-organizer.php**: Moves scoped code from `build/` to `lib/Vendor/`
- **vendor-bin/php-scoper/composer.json**: Declares php-scoper dependency

## Usage

### Building the App

```bash
# Install dependencies and run scoping
make build

# Or manually
composer install --no-dev
composer scope-dependencies
```

### Development

```bash
# Install dev dependencies with scoping
composer install
composer scope-dependencies

# Run tests (automatically scopes if needed)
make test
```

### Scoped Dependencies

All these are scoped under `OCA\News\Vendor`:

- **feed-io** - RSS/Atom feed parsing
- **Favicon** - Favicon fetching
- **fivefilters/Readability** - Article extraction
- **League/Uri** - URI manipulation
- **GuzzleHttp/Psr7** - PSR-7 HTTP messages
- **php-http/** - HTTP client adapter
- And all their transitive dependencies

**Exception:** `Psr\Log` is NOT scoped as it's a standard PSR interface.

## Implementation Details

### Makefile Integration

- `make build` automatically runs scoping after composer install
- `make test` checks if scoping is needed before running tests
- `make appstore` packages scoped dependencies correctly

### Appstore Package

The appstore target:
- Copies `lib/` with scoped dependencies
- Removes original vendor files (now scoped in lib/Vendor/)
- Keeps vendor/autoload.php and vendor/composer/
- Includes composer.json for autoload configuration
- Cleans up test files and .git directories from scoped deps

### Code Updates

All imports in lib/ and tests/ use the scoped namespace:

```php
// Before
use FeedIo\FeedIo;
use Favicon\Favicon;

// After  
use OCA\News\Vendor\FeedIo\FeedIo;
use OCA\News\Vendor\Favicon\Favicon;
```

### Guzzle Compatibility

Guzzle is provided by Nextcloud server and is not scoped. The `FeedIoClient` class wraps unscoped PSR-7 responses into scoped ones for compatibility with scoped feed-io.

## Benefits

✅ **No Conflicts** - Apps can use different versions of the same library  
✅ **Independent** - News doesn't affect other apps  
✅ **Maintainable** - Clear namespace prefixing pattern  
✅ **Standard** - Uses php-scoper, a well-maintained tool

## References

- [php-scoper documentation](https://github.com/humbug/php-scoper)
- [Original PR #3101](https://github.com/nextcloud/news/pull/3101)
- [Implementation PR #3384](https://github.com/nextcloud/news/pull/3384)
