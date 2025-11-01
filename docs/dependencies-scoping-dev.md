# Dependency Scoping for Developers

## Overview

The News app uses **dependency scoping** to isolate its PHP dependencies and prevent conflicts with other Nextcloud apps. This is especially important for libraries like HTMLPurifier that are used by multiple apps.

All vendor dependencies are prefixed with the `OCA\News\Vendor` namespace and stored in `lib/Vendor/` instead of the standard `vendor/` directory.

## Why Scoping?

**Problem:** When multiple Nextcloud apps use the same PHP library (e.g., HTMLPurifier), PHP cannot handle duplicate class declarations, leading to fatal errors:
```
Fatal error: Cannot declare class HTMLPurifier, because the name is already in use
```

**Solution:** Each app gets its own isolated copy of dependencies with a unique namespace prefix, preventing conflicts.

## Quick Start

### Initial Setup

After cloning the repository:

```bash
# Install dependencies
composer install

# Generate scoped dependencies
composer scope-dependencies
```

This creates the `lib/Vendor/` directory with all dependencies properly namespaced.

### Daily Development

The scoped dependencies are **gitignored** (generated code), so you need to regenerate them:

```bash
# After pulling changes or switching branches
composer install
composer scope-dependencies

# Or use make commands (which handle scoping automatically)
make build
make test
```

## How It Works

### 1. Scoping Process

When you run `composer scope-dependencies`:

1. **Install php-scoper** (in isolation via bamarni/composer-bin-plugin)
2. **Scan dependencies** using `composer show --tree`
3. **Prefix namespaces** with `OCA\News\Vendor` using php-scoper
4. **Organize files** into `lib/Vendor/` directory structure
5. **Generate autoloader** with custom HTMLPurifier autoloader
6. **Clean up** original vendor code (e.g., `vendor/ezyang`)

### 2. Configuration Files

**scoper.inc.php**
- Defines which dependencies to scope
- Excludes platform dependencies (PHP extensions)
- Excludes PSR standards (Psr\Log)
- Configures the namespace prefix

**lib-vendor-organizer.php**
- Moves scoped code from `build/` to `lib/Vendor/`
- Handles different autoload strategies (PSR-4, classmap, files)
- Creates custom HTMLPurifier autoloader (non-PSR-4 naming)
- Strips namespace prefix from directory paths

**composer.json**
```json
{
  "autoload": {
    "psr-4": {
      "OCA\\News\\": "lib/"
    },
    "files": [
      "lib/Vendor/HTMLPurifier.composer.php",
      "lib/Vendor/HTMLPurifier.autoload.php"
    ]
  },
  "scripts": {
    "scope-dependencies": [
      "@composer bin php-scoper install --ignore-platform-reqs",
      "rm -Rf build",
      "vendor/bin/php-scoper add-prefix --force",
      "rm -Rf lib/Vendor",
      "@php lib-vendor-organizer.php build/ lib/Vendor/ OCA\\\\News\\\\Vendor",
      "rm -Rf vendor/ezyang",
      "composer dump-autoload"
    ]
  }
}
```

### 3. Makefile Integration

The Makefile automatically handles scoping where needed:

```makefile
# Build process includes scoping
.PHONY: build
composer:
	$(composer) install --prefer-dist --no-dev
	$(composer) scope-dependencies

# Tests ensure scoping is done
.PHONY: unit-test
unit-test: scope-if-needed
	./vendor/phpunit/phpunit/phpunit -c phpunit.xml --no-coverage

# PHP test dependencies with scoping
.PHONY: php-test-dependencies
php-test-dependencies:
	$(composer) update --prefer-dist
	$(composer) scope-dependencies
```

## Using Scoped Dependencies

### Importing Classes

All vendor dependencies must use the `OCA\News\Vendor` namespace prefix:

```php
<?php
// ❌ Wrong - Do NOT use original namespaces
use HTMLPurifier;
use FeedIo\FeedIo;
use Favicon\Favicon;

// ✅ Correct - Use scoped namespaces
use OCA\News\Vendor\HTMLPurifier;
use OCA\News\Vendor\FeedIo\FeedIo;
use OCA\News\Vendor\Favicon\Favicon;
use OCA\News\Vendor\fivefilters\Readability\Readability;
use OCA\News\Vendor\League\Uri\Uri;
```

### Example: Using HTMLPurifier

```php
<?php
namespace OCA\News\Service;

use OCA\News\Vendor\HTMLPurifier;
use OCA\News\Vendor\HTMLPurifier_Config;

class ContentService {
    public function purifyHtml(string $html): string {
        $config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);
        return $purifier->purify($html);
    }
}
```

### Scoped Dependencies List

All these are scoped under `OCA\News\Vendor`:

- **HTMLPurifier** - HTML sanitization (special handling for underscore classes)
- **FeedIo** - RSS/Atom feed parsing
- **Favicon** - Favicon fetching
- **fivefilters/Readability** - Article extraction
- **League/Uri** - URI manipulation
- **GuzzleHttp/Psr7** - PSR-7 HTTP messages
- **php-http/guzzle7-adapter** - HTTP client adapter
- And all their transitive dependencies

**Exception:** `Psr\Log` is NOT scoped as it's a standard PSR interface.

## Development Workflow

### Adding a New Dependency

1. Add to `composer.json`:
   ```bash
   composer require vendor/package
   ```

2. Run scoping:
   ```bash
   composer scope-dependencies
   ```

3. Use with scoped namespace:
   ```php
   use OCA\News\Vendor\Vendor\Package\ClassName;
   ```

4. Test:
   ```bash
   make test
   ```

### Updating Dependencies

```bash
# Update composer.lock
composer update

# Re-run scoping
composer scope-dependencies

# Test everything
make test
```

### Removing a Dependency

1. Remove from `composer.json`:
   ```bash
   composer remove vendor/package
   ```

2. Re-run scoping:
   ```bash
   composer scope-dependencies
   ```

3. Remove all import statements
4. Test to ensure nothing breaks

## Testing

### Running Tests

```bash
# Full test suite (includes scoping)
make test

# Just unit tests (auto-scopes if needed)
make unit-test

# Code style
make phpcs

# Static analysis
make phpstan

# JavaScript tests
make js-test
```

### Testing with Other Apps

To verify no conflicts:

1. Install News app
2. Install Mail app (also uses HTMLPurifier)
3. Enable both apps
4. Navigate between them
5. Test core functionality

No fatal errors should occur.

### Troubleshooting

**Class not found errors:**
```bash
# Regenerate scoped dependencies
rm -rf lib/Vendor
composer scope-dependencies
composer dump-autoload
```

**Scoping fails:**
```bash
# Clean and rebuild
make distclean
composer install
composer scope-dependencies
```

**Tests fail after scoping:**
```bash
# Check imports use OCA\News\Vendor prefix
grep -r "use.*HTMLPurifier" lib/ tests/
# Should show: use OCA\News\Vendor\HTMLPurifier;
```

**Permission errors:**
```bash
# Fix permissions
chmod -R 755 vendor-bin/
chmod -R 755 lib/
```

## Tool Configuration

### PHPStan

Excludes scoped vendor code from static analysis:

```yaml
# phpstan.neon.dist
parameters:
  excludePaths:
    - %currentWorkingDirectory%/lib/Vendor/*
```

### PHP_CodeSniffer

Ignores scoped vendor code:

```bash
phpcs --ignore=lib/Vendor/* lib
```

### .gitignore

Scoped dependencies are generated code:

```
/lib/Vendor/*
```

### Vale (Documentation Linting)

```
# .valeignore
lib/Vendor/
```

## CI/CD Integration

### GitHub Actions / GitLab CI

```yaml
- name: Install dependencies
  run: |
    composer install --no-dev
    composer scope-dependencies

- name: Run tests
  run: make test
```

### Building App Store Package

```bash
# Makefile handles this automatically
make appstore

# Manual process:
composer install --no-dev
composer scope-dependencies
# ... package files ...
```

## Common Pitfalls

### ❌ Don't: Commit scoped code
```bash
# lib/Vendor/* is gitignored
git add lib/Vendor/  # This will fail
```

### ❌ Don't: Use unscoped imports
```php
use HTMLPurifier;  // Wrong!
```

### ❌ Don't: Edit files in lib/Vendor/
```php
// lib/Vendor/ is generated - changes will be lost
```

### ✅ Do: Run scoping after dependency changes
```bash
composer install
composer scope-dependencies
```

### ✅ Do: Use scoped namespaces
```php
use OCA\News\Vendor\HTMLPurifier;
```

### ✅ Do: Test with other apps
Test News alongside Mail app to verify no conflicts.

## Performance Impact

Scoping has **minimal performance impact**:

- Autoloading works the same way
- No additional runtime overhead
- Slightly larger codebase (duplicate dependencies)
- Scoping process adds ~10-15s to build time

## Benefits

✅ **No Conflicts** - Apps can use different versions of the same library  
✅ **Independent** - News doesn't affect other apps  
✅ **Maintainable** - Clear namespace prefixing pattern  
✅ **Reversible** - Can be removed if Nextcloud solves this differently  
✅ **Standard** - Uses php-scoper, a well-maintained tool

## Further Reading

- [php-scoper documentation](https://github.com/humbug/php-scoper)
- [Composer bin plugin](https://github.com/bamarni/composer-bin-plugin)
- [PSR-4 Autoloading](https://www.php-fig.org/psr/psr-4/)
- [Original issue #3101](https://github.com/nextcloud/news/pull/3101)

## Getting Help

If you encounter issues:

1. Check the [troubleshooting section](#troubleshooting)
2. Review recent changes: `git log --oneline`
3. Check logs: `tail -f data/nextcloud.log`
4. Create a [GitHub Discussion](https://github.com/nextcloud/news/discussions)
5. Report bugs with full error messages and logs
