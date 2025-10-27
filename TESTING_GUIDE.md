# Testing Guide for Dependency Scoping

## Prerequisites

- PHP 8.2+
- Composer 2.0+
- Nextcloud instance with both News and Mail apps

## Step-by-Step Testing

### 1. Install Dependencies

```bash
cd /path/to/nextcloud/apps/news
composer install --no-dev
```

This will install:
- All News app dependencies
- bamarni/composer-bin-plugin
- php-scoper (in vendor-bin/)

### 2. Generate Scoped Dependencies

```bash
composer scope-dependencies
```

Expected output:
```
> @composer bin php-scoper install --ignore-platform-reqs
> rm -Rf build
> vendor/bin/php-scoper add-prefix --force
Adding ezyang/htmlpurifier
Adding php-feed-io/feed-io
Adding arthurhoaro/favicon
...
> rm -Rf lib/Vendor
> @php lib-vendor-organizer.php build/ lib/Vendor/ OCA\\News\\Vendor
Transformed namespace: ezyang/htmlpurifier/
Transformed namespace: FeedIo/
...
> composer dump-autoload
```

### 3. Verify Scoped Code

Check that `lib/Vendor/` directory exists and contains namespaced code:

```bash
ls -la lib/Vendor/
# Should show directories like:
# - ezyang/
# - FeedIo/
# - Favicon/
# - fivefilters/
# - League/
```

Verify namespace prefixing:

```bash
head -20 lib/Vendor/ezyang/htmlpurifier/library/HTMLPurifier.php
# Should contain: namespace OCA\News\Vendor;
```

### 4. Run Tests

```bash
# Run PHPUnit tests
make unit-test

# Run code style checks
make phpcs

# Run static analysis
make phpstan
```

All tests should pass without errors.

### 5. Test with Mail App

#### Setup
1. Install Nextcloud Mail app if not already installed
2. Ensure both News and Mail apps are enabled

#### Conflict Test
Before this PR, running both apps would cause errors like:
```
Fatal error: Cannot declare class HTMLPurifier, because the name is already in use
```

After this PR:
1. Enable News app
2. Enable Mail app
3. Navigate to News app - should work
4. Navigate to Mail app - should work
5. Go back to News app - should still work

No fatal errors should occur.

### 6. Functional Testing

Test core News functionality:
- Add a new feed
- Refresh feeds
- Mark items as read/unread
- Delete feeds
- Import OPML
- Export OPML
- Use full-text search

All features should work as before.

### 7. Performance Testing

Compare performance before/after:

```bash
# Time for feed update
time php occ news:updater:update-feed --all

# Check memory usage
php -d memory_limit=-1 occ news:updater:update-feed --all
```

Performance should be similar (scoping adds minimal overhead).

## Troubleshooting

### Scoping fails with "Cannot find dependencies"

**Solution:** Run `composer install` first to install all dependencies.

### "Class not found" errors after scoping

**Solution:** 
1. Check that all imports use `OCA\News\Vendor\` prefix
2. Run `composer dump-autoload` to regenerate autoloader

### Conflicts still occur with Mail app

**Solution:**
1. Verify scoping completed successfully
2. Check that `lib/Vendor/` contains scoped code
3. Clear Nextcloud cache: `php occ maintenance:repair`

### Permission errors when running scope-dependencies

**Solution:**
```bash
# Ensure vendor-bin directory is writable
chmod -R 755 vendor-bin/

# Ensure lib directory is writable
chmod -R 755 lib/
```

## Verification Checklist

- [ ] Dependencies installed successfully
- [ ] Scoping process completed without errors
- [ ] `lib/Vendor/` directory created and populated
- [ ] All tests pass
- [ ] No conflicts when both News and Mail apps are enabled
- [ ] Core functionality works (add/delete feeds, mark read, etc.)
- [ ] Performance is acceptable
- [ ] No fatal errors in Nextcloud logs

## Rollback Procedure

If issues occur, rollback with:

```bash
git checkout main
composer install --no-dev
```

This reverts to the pre-scoping version.

## Expected Results

✅ **Success Indicators:**
- No fatal errors about duplicate class declarations
- Both News and Mail apps work simultaneously
- All existing functionality preserved
- Tests pass
- Clean logs

❌ **Failure Indicators:**
- Fatal errors about duplicate classes
- Class not found errors
- Tests failing
- Features broken
- Performance degradation

## Support

If you encounter issues:
1. Check the logs: `tail -f data/nextcloud.log`
2. Review scoping output for errors
3. Verify all files were updated correctly
4. Report issues on GitHub with logs
