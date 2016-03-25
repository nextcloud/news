# This file is licensed under the Affero General Public License version 3 or
# later. See the COPYING file.
# @author Bernhard Posselt <dev@bernhard-posselt.com>
# @copyright Bernhard Posselt 2012, 2014

# Generic Makefile for building and packaging an ownCloud app which uses npm and
# Composer.
#
# Dependencies:
# * make
# * curl: used if phpunit and composer are not installed to fetch them from the web
# * tar: for building the archive
# * npm: for building and testing everything JS
#
# If no composer.json is in the app root directory, the Composer step
# will be skipped. The same goes for the package.json which can be located in
# app root or the js/ directory.
#
# The npm command by launches the npm build script:
#
#   npm run build
#
# The npm test command launches the npm test script:
#
#	npm run test
#
# The idea behind this is to be completely testing and build tool agnostic. All
# build tools and additional package managers should be installed locally in
# your project, since this won't pollute people's global namespace.
#
# The following npm scripts in your package.json install and update the bower
# and npm dependencies and use gulp as build system (notice how everything is
# run from the node_modules folder):
#
#	"scripts": {
#	    "test": "node node_modules/gulp-cli/bin/gulp.js karma",
#	    "prebuild": "npm install && node_modules/bower/bin/bower install && node_modules/bower/bin/bower update",
#		"build": "node node_modules/gulp-cli/bin/gulp.js"
#	},
app_name=$(notdir $(CURDIR))
build_directory=$(CURDIR)/build/artifacts/source
package_name=$(build_directory)/$(app_name)
npm=$(shell which npm 2> /dev/null)

all: build

# Fetches the PHP and JS dependencies and compiles the JS
.PHONY: build
build:
	make composer
	make npm

# Installs and updates the composer dependencies. If composer is not installed
# a copy is fetched from the web
.PHONY: composer
composer:
ifeq (, $(shell which composer 2> /dev/null))
	@echo "No composer command available, downloading a copy from the web"
	mkdir -p build/tools
	curl -sS https://getcomposer.org/installer | php
	mv composer.phar build/tools/
	php build/tools/composer.phar install --prefer-dist
	php build/tools/composer.phar update --prefer-dist
else
	composer install --prefer-dist
	composer update --prefer-dist
endif

# Installs npm dependencies
.PHONY: npm
npm:
ifeq (,$(wildcard $(CURDIR)/package.json))
	cd js && $(npm) run build
else
	npm run build
endif

# Removes the appstore build
.PHONY: clean
clean:
	rm -rf ./build

# Same as clean but also removes dependencies installed by composer, bower and
# npm
.PHONY: distclean
distclean: clean
	rm -rf vendor
	rm -rf js/vendor
	rm -rf js/node_modules

# Builds the package for the app store
.PHONY: dist
dist:
	make clean
	make build
	make test
	mkdir -p $(build_directory)
	tar cvzf $(package_name).tar.gz ../$(app_name) \
	--exclude-vcs \
	--exclude=../$(app_name)/build \
	--exclude=../$(app_name)/js/node_modules \


# Command for running JS and PHP tests. Works for package.json files in the js/
# and root directory. If phpunit is not installed systemwide, a copy is fetched
# from the internet
.PHONY: test
test:
ifeq (,$(wildcard $(CURDIR)/package.json))
	cd js && $(npm) run test
else
	npm run test
endif
ifeq (, $(shell which phpunit 2> /dev/null))
	@echo "No phpunit command available, downloading a copy from the web"
	mkdir -p build/tools
	curl -sSL https://phar.phpunit.de/phpunit.phar -o build/tools/phpunit.phar
	php build/tools/phpunit.phar -c phpunit.xml
	php build/tools/phpunit.phar -c phpunit.integration.xml
else
	phpunit -c phpunit.xml
	phpunit -c phpunit.integration.xml
endif
