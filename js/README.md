# JavaScript Development
Before starting, install nodejs 0.10 and grunt-cli:

	sudo npm -g install grunt-cli

then run:

	npm install

The news app uses [Traceur](https://github.com/google/traceur-compiler) to transpile ES6 into ES5. If you want to take a look at the features see [the language features reference](https://github.com/google/traceur-compiler/wiki/LanguageFeatures#language-features).

### Iterators
The following iterators are defined and availabe:

* **items**:

	```js
	// iterate over object key and value
	for (let [key, value] of items(obj)) {
		console.log(`object key: ${key}, object value: ${value}`)
	}
	```
* **enumerate**:

	```js
	// iterate over list and get the index and value
	for (let [index, value] of enumerate(list)) {
		console.log(`at position: ${index}, value is: ${value}`)
	}
	```

## Building
Watch mode:

	grunt dev

Single run mode:

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

