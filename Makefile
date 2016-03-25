# This file is licensed under the Affero General Public License version 3 or
# later. See the COPYING file.
# @author Bernhard Posselt <dev@bernhard-posselt.com>
# @copyright Bernhard Posselt 2012, 2014

# Generic Makefile for building and packaging an ownCloud app
#
# Dependencies:
# * make
# * curl: if phpunit and composer are not installed to fetch the files from the web
# * tar: for building the archive
# * npm: for building and testing everything JS

app_name=$(notdir $(CURDIR))
build_directory=$(CURDIR)/build/artifacts/source
package_name=$(build_directory)/$(app_name)
npm=$(shell which npm)

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
	curl -sS https://getcomposer.org/installer | php
	php composer.phar install --prefer-dist
	php composer.phar update --prefer-dist
	rm -f composer.phar
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
	curl -sSOL https://phar.phpunit.de/phpunit.phar
	php phpunit.phar -c phpunit.xml
	php phpunit.phar -c phpunit.integration.xml
	rm -f phpunit.phar
else
	phpunit -c phpunit.xml
	phpunit -c phpunit.integration.xml
endif
