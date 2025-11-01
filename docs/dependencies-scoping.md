# Dependency Scoping for News App

This directory contains the configuration for scoping PHP dependencies using php-scoper.

## Problem

The News app and Mail app both use HTMLPurifier, which can cause conflicts when both apps are installed
because PHP cannot handle the same class being loaded from different locations with different versions.

## Solution

We use [php-scoper](https://github.com/humbug/php-scoper) to prefix all vendor dependencies with the
`OCA\News\Vendor` namespace, isolating them from other apps.

## Usage

After running `composer install`, you need to run the scoping process:

```bash
composer scope-dependencies
```

This will:
1. Install php-scoper in the vendor-bin directory
2. Run php-scoper to prefix all dependencies
3. Organize the scoped dependencies into `lib/Vendor/`
4. Regenerate the autoloader

## Files

- `scoper.inc.php` - Configuration for php-scoper
- `lib-vendor-organizer.php` - Helper script to organize scoped dependencies
- `vendor-bin/php-scoper/composer.json` - php-scoper dependency declaration

## Development

The `lib/Vendor/` directory is gitignored as it contains generated code. Each developer needs to run
`composer scope-dependencies` after `composer install`.

## Build Process

For the app store package, the Makefile handles running `composer install` with `--no-dev`, which will
not trigger the scoping scripts. You need to run `composer scope-dependencies` manually before building
the package.
