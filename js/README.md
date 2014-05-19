# JavaScript Development
Before starting, install nodejs 0.10 and grunt-cli:

	sudo npm -g install grunt-cli

then run:

	npm install

## Building
Watch mode:

	grunt dev

Single run mode:

	grunt

## Testing
Watch mode:

	grunt phpunit
	grunt test

Single run mode:

	grunt phpunit
	grunt ci

### Running e2e tests
Install protractor and set up selenium:

	sudo npm install -g protractor
	sudo webdriver-manager update

then the tests can be started with:

	grunt e2e

