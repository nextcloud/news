# JavaScript Development
Before starting, install nodejs 0.10 and grunt-cli:

	sudo npm -g install grunt-cli

then run:

	npm install

The news app uses [Traceur](https://github.com/google/traceur-compiler) to transpile ES6 into ES5. If you want to take a look at the features see [the language features reference](https://github.com/google/traceur-compiler/wiki/LanguageFeatures#language-features).

* Modules can not be used since the code is inlined
* The following iterators are available: **items**

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
	grunt ci-unit

### Running e2e tests
Install protractor and set up selenium:

	sudo npm install -g protractor
	sudo webdriver-manager update

then the tests can be started with:

	grunt e2e

