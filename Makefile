# Makefile for building the project
app_name=$(notdir $(CURDIR))
build_directory=$(CURDIR)/build/artifacts/source
package_name=$(build_directory)/$(app_name)

all: build

.PHONY: build
build:
	make composer
	make npm

.PHONY: composer
composer:
	curl -sS https://getcomposer.org/installer | php
	php composer.phar install --prefer-dist
	php composer.phar update --prefer-dist
	rm -f composer.phar

.PHONY: npm
npm:
	cd js && npm run build

.PHONY: clean
clean:
	rm -rf ./build

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

.PHONY: test
test:
	cd js && npm run test
	phpunit -c phpunit.xml
	phpunit -c phpunit.integration.xml
