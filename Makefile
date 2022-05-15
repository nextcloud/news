# This file is licensed under the Affero General Public License version 3 or
# later. See the COPYING file.
# @author Bernhard Posselt <dev@bernhard-posselt.com>
# @copyright Bernhard Posselt 2016

# Generic Makefile for building and packaging a Nextcloud app which uses npm and
# Composer.
#
# Dependencies:
# * make
# * which
# * curl: used if phpunit and composer are not installed to fetch them from the web
# * tar: for building the archive
# * npm: for building and testing everything JS
#
# If no composer.json is in the app root directory, the Composer step
# will be skipped. The same goes for the package.json which can be located in
# the app root or the js/ directory.
#
# The npm command by launches the npm build script:
#
#    npm run build
#
# The npm test command launches the npm test script:
#
#    npm run test
#
# The idea behind this is to be completely testing and build tool agnostic. All
# build tools and additional package managers should be installed locally in
# your project, since this won't pollute people's global namespace.
#
# The following npm scripts in your package.json install the npm dependencies
# and use gulp as build system (notice how everything is run from the
# node_modules folder):
#
#    "scripts": {
#        "test": "node node_modules/gulp-cli/bin/gulp.js karma",
#        "prebuild": "npm install",
#        "build": "node node_modules/gulp-cli/bin/gulp.js"
#    },

app_name:=$(notdir $(CURDIR))
build_tools_directory:=$(CURDIR)/build/tools
source_build_directory:=$(CURDIR)/build/source/$(app_name)
source_artifact_directory:=$(CURDIR)/build/artifacts/source
source_package_name:=$(source_artifact_directory)/$(app_name)
appstore_build_directory:=$(CURDIR)/build/appstore/$(app_name)
appstore_artifact_directory:=$(CURDIR)/build/artifacts/appstore
appstore_package_name:=$(appstore_artifact_directory)/$(app_name)
appstore_sign_dir=$(appstore_build_directory)/sign
cert_dir=$(HOME)/.nextcloud/certificates
npm:=$(shell which npm 2> /dev/null)
composer:=$(shell which composer 2> /dev/null)
ifeq (,$(composer))
	composer:=php $(build_tools_directory)/composer.phar
endif

#Support xDebug 3.0+
export XDEBUG_MODE=coverage

all: build

# Fetches the PHP and JS dependencies and compiles the JS. If no composer.json
# is present, the composer step is skipped, if no package.json or js/package.json
# is present, the npm step is skipped
.PHONY: build
build:
	$(MAKE) composer
	$(MAKE) npm

# Installs and updates the composer dependencies. If composer is not installed
# a copy is fetched from the web
.PHONY: composer
composer:
ifeq (, $(shell which composer 2> /dev/null))
	@echo "No composer command available, downloading a copy from the web"
	mkdir -p $(build_tools_directory)
	curl -sS https://getcomposer.org/installer | php
	mv composer.phar $(build_tools_directory)
endif
	$(composer) install --prefer-dist --no-dev

# Installs npm dependencies
.PHONY: npm
npm:
ifneq (, $(npm))
	$(npm) run build
else
	@echo "npm command not available, please install nodejs first"
	@exit 1
endif

# Removes the appstore build
.PHONY: clean
clean:
	rm -rf ./build

# Reports PHP codestyle violations
.PHONY: phpcs
phpcs:
	./vendor/bin/phpcs --standard=PSR2 --ignore=lib/Migration/Version*.php lib

# Reports PHP static violations
.PHONY: phpstan
phpstan:
	./vendor/bin/phpstan analyse --level=1 lib

# Same as clean but also removes dependencies installed by composer and
# npm
.PHONY: distclean
distclean: clean
	rm -rf vendor
	rm -rf node_modules
	rm -rf js/node_modules

# Builds the source and appstore package
.PHONY: dist
dist:
	make distclean
	make build
	make source
	make appstore

# Builds the source package
.PHONY: source
source:
	rm -rf $(source_build_directory) $(source_artifact_directory)
	mkdir -p $(source_build_directory) $(source_artifact_directory)
	rsync -rv . $(source_build_directory) \
	--exclude=/.git/ \
	--exclude=/.idea/ \
	--exclude=/build/ \
	--exclude=/js/node_modules/ \
	--exclude=*.log
ifdef CAN_SIGN
	$(sign) --path "$(source_build_directory)"
else
	@echo $(sign_skip_msg)
endif
	tar -cvzf $(source_package_name).tar.gz -C $(source_build_directory)/../ $(app_name)

# Builds the source package for the app store, ignores php and js tests
.PHONY: appstore
appstore:
	rm -rf $(appstore_build_directory) $(appstore_sign_dir) $(appstore_artifact_directory)
	install -d $(appstore_sign_dir)/$(app_name)
	cp -r \
	"appinfo" \
	"css" \
	"img" \
	"l10n" \
	"lib" \
	"templates" \
	"vendor" \
	$(appstore_sign_dir)/$(app_name)

	# remove composer binaries, those aren't needed
	rm -rf $(appstore_sign_dir)/$(app_name)/vendor/bin
	# the App Store doesn't like .git
	rm -rf $(appstore_sign_dir)/$(app_name)/vendor/arthurhoaro/favicon/.git
	# remove large test files
	rm -rf $(appstore_sign_dir)/$(app_name)/vendor/fivefilters/readability.php/test

	install "COPYING" $(appstore_sign_dir)/$(app_name)
	install "AUTHORS.md" $(appstore_sign_dir)/$(app_name)
	install "CHANGELOG.md" $(appstore_sign_dir)/$(app_name)

	#remove stray .htaccess files since they are filtered by nextcloud
	find $(appstore_sign_dir) -name .htaccess -exec rm {} \;

	# on macOS there is no option "--parents" for the "cp" command
	mkdir -p $(appstore_sign_dir)/$(app_name)/js/build $(appstore_sign_dir)/$(app_name)/js/admin
	cp js/build/app.min.js $(appstore_sign_dir)/$(app_name)/js/build
	cp js/admin/Admin.js $(appstore_sign_dir)/$(app_name)/js/admin

	# export the key and cert to a file
	mkdir -p $(cert_dir)
	php ./bin/tools/file_from_env.php "app_private_key" "$(cert_dir)/$(app_name).key"
	php ./bin/tools/file_from_env.php "app_public_crt" "$(cert_dir)/$(app_name).crt"

	@if [ -f $(cert_dir)/$(app_name).key ]; then \
		echo "Signing app files…"; \
		php ../../occ integrity:sign-app \
			--privateKey=$(cert_dir)/$(app_name).key\
			--certificate=$(cert_dir)/$(app_name).crt\
			--path=$(appstore_sign_dir)/$(app_name); \
		echo "Signing app files ... done"; \
	fi
	mkdir -p $(appstore_artifact_directory)
	tar -czf $(appstore_package_name).tar.gz -C $(appstore_sign_dir) $(app_name)


.PHONY: js-test
js-test:
	cd js && $(npm) run test

.PHONY: php-test-dependencies
php-test-dependencies:
	$(composer) update --prefer-dist

.PHONY: unit-test
unit-test:
	./vendor/phpunit/phpunit/phpunit -c phpunit.xml --coverage-clover build/php-unit.clover

# Command for running JS and PHP tests. Works for package.json files in the js/
# and root directory. If phpunit is not installed systemwide, a copy is fetched
# from the internet
.PHONY: test
test: php-test-dependencies
	$(MAKE) unit-test
	$(MAKE) phpcs
	$(MAKE) phpstan
	$(MAKE) js-test
	./bin/tools/generate_authors.php

.PHONY: feed-test
feed-test:
	./bin/tools/check_feeds.sh