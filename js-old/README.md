# JavaScript Development
JavaScript is built and minified using gulp.

Therefore you need to install **Node.js 6+ and npm**. Then use npm to install **gulp-cli**:

	sudo npm -g install gulp-cli

Then install the local dependencies by running:

	npm install

## Tasks
The following tasks are available:

* **Build the JavaScript**:

        gulp

* **Watch for changes and build JavaScript**:

        gulp watch

* **Run JavaScript unit tests**:

        gulp karma

* **Watch for changes and run JavaScript unit tests**:

        gulp watch-karma
