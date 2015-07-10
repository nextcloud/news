# JavaScript && CSS Development
Before starting, install nodejs and grunt-cli:

	sudo npm -g install grunt-cli

then run:

	npm install


## Building
This sets up a watcher on file change and compiles CSS and JS:

	grunt dev

If you don't want a watcher, just run:

	grunt

## Testing
Watch mode:

	grunt php
	grunt test

Single run mode:

	grunt phpunit
	grunt ci-unit

### Running e2e tests
Install protractor and set up selenium:

	sudo npm install -g protractor
	sudo webdriver-manager update

then the tests can be started with:

	grunt e2e

