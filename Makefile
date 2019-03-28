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
npm:=$(shell which npm 2> /dev/null)
composer:=$(shell which composer 2> /dev/null)
ifeq (,$(composer))
	composer:=php $(build_tools_directory)/composer.phar
endif

# code signing
# assumes the following:
# * the app is inside the nextcloud/apps folder
# * the private key is located in ~/.nextcloud/news.key
# * the certificate is located in ~/.nextcloud/news.crt
occ:=$(CURDIR)/../../occ
private_key:=$(HOME)/.nextcloud/$(app_name).key
certificate:=$(HOME)/.nextcloud/$(app_name).crt
sign:=php -f $(occ) integrity:sign-app --privateKey="$(private_key)" --certificate="$(certificate)"
sign_skip_msg:="Skipping signing, either no key and certificate found in $(private_key) and $(certificate) or occ can not be found at $(occ)"
ifneq (,$(wildcard $(private_key)))
ifneq (,$(wildcard $(certificate)))
ifneq (,$(wildcard $(occ)))
	CAN_SIGN=true
endif
endif
endif

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
	cd js && $(npm) run build
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
	./vendor/bin/phpcs --standard=PSR2 lib

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
	rm -rf $(appstore_build_directory) $(appstore_artifact_directory)
	mkdir -p $(appstore_build_directory) $(appstore_artifact_directory)
	./bin/tools/generate_authors.php
	cp -r \
	"appinfo" \
	"css" \
	"img" \
	"l10n" \
	"lib" \
	"templates" \
	"vendor" \
	"COPYING" \
	"AUTHORS.md" \
	"CHANGELOG.md" \
	$(appstore_build_directory)

	#remove stray .htaccess files since they are filtered by nextcloud
	find $(appstore_build_directory) -name .htaccess -exec rm {} \;

	# on macOS there is no option "--parents" for the "cp" command
	mkdir -p $(appstore_build_directory)/js/build $(appstore_build_directory)/js/admin
	cp js/build/app.min.js $(appstore_build_directory)/js/build
	cp js/admin/Admin.js $(appstore_build_directory)/js/admin
ifdef CAN_SIGN
	$(sign) --path="$(appstore_build_directory)"
else
	@echo $(sign_skip_msg)
endif
	tar -czf $(appstore_package_name).tar.gz -C $(appstore_build_directory)/../ $(app_name)


# Command for running JS and PHP tests. Works for package.json files in the js/
# and root directory. If phpunit is not installed systemwide, a copy is fetched
# from the internet
.PHONY: test
test:
	$(composer) update --prefer-dist
	cd js && $(npm) run test
	./vendor/phpunit/phpunit/phpunit -c phpunit.xml --coverage-clover build/php-unit.clover
	# \Test\TestCase is only allowed to access the db if TRAVIS environment variable is set
	env TRAVIS=1 ./vendor/phpunit/phpunit/phpunit -c phpunit.integration.xml
	$(MAKE) phpcs
	./bin/tools/generate_authors.php
