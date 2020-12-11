# Contributing
Read this when you want to:

* [file an issue (bug or feature request)](#Issues)
* [help translate the News file to your language](#Translation)
* [start programming and change the way the News app works](#development)
* [add cool new feeds to the feed explore section](#explore-feeds-section)

## General
* Be as precise in your issues as possible and make it as easy as possible to understand. 
* Follow the [code of conduct](https://nextcloud.com/code-of-conduct/). Being a dick and insulting people will get your posts deleted and issues locked.

### Before Reporting Bugs
* We do not support Internet Explorer and Safari (Patches accepted though, except for IE < 10)
* We do **not support the server-side encryption app** (use client side encryption instead)
* Get the latest version of the News app
* Disable all browser add-ons to make sure that it's not a plugin's fault (adblockers!)
* Clear your PHP opcode cache if you use any by restarting your webserver. This affects any version of PHP >=5.5
* [Check if they have already been reported](https://github.com/nextcloud/news/issues?state=open)
* [Check if your problem is covered in the FAQ section](https://github.com/nextcloud/news#faq)
**For feed parsing issues, check**:
* It is a valid RSS by running it through the [W3C validator](http://validator.w3.org/feed/)
* You are able to add the feed in other feed readers

### When reporting bugs
* Enable debug mode in your **config/config.php**:
 * Add the **debug** attribute to config array (if not already present) and set it to **true**:
 ```php
 <?php
 $CONFIG = array(
    // other options
    // ...
    'debug' => true,
 );
 ```

* Reproduce the Problem
* Check **data/nextcloud.log**
* Check your [browser's JavaScript console for errors](http://ggnome.com/wiki/Using_The_Browser_Error_Console) if it's a client-side issue

Please provide the following details so that your problem can be fixed:
* **data/nextcloud.log** (important!)
* Nextcloud version
* News version
* Browser and version
* PHP version
* Distribution and version

### When Requesting Features
Please always provide the use case in addition solution, e.g.:

* "If I read feed that has comics, the newest first ordering does not work well because I have to read from the bottom up"

is much more helpful than just writing:

* "Please add reverse ordering".


## Translation

For translations in other languages than English, we rely on the [Transifex](https://www.transifex.com/) platform.

If you want to help with translating the app, please do not create a pull request. Instead, head over to https://www.transifex.com/projects/p/nextcloud/resource/news/ and join the team of your native language.

If approved, the translation will be automatically ported to the code within 24 hours.


## Explore feeds section
You can help to improve our explore feeds section by [providing more feeds](https://github.com/nextcloud/news/tree/master/docs/explore)

## Development
You can first create a discussion where you explain why, what and how you want to make a change before writing any code. If you want to agree on a solution first.

### How to set up a development environment
To get started after [cloning the repository](https://github.com/nextcloud/news#installing-from-git-development-version), install the [build dependencies](https://github.com/nextcloud/news#development-dependencies) and run:

    make

in the app directory to fetch all dependencies and compile the JavaScript. The News app uses Composer for PHP dependencies, Gulp for building the JavaScript "binary" and Bower/npm as JavaScript package manager. For more information on JavaScript development [check out the README.md in the js folder](https://github.com/nextcloud/news/blob/master/js/README.md)

For running all tests suites you can run:

    make test

Packaging is done via:

    make dist

The packages are inside the top level **build/artifacts** folder

### Coding Style Guidelines
The PHP code should all adhere to [PSR-2](https://www.php-fig.org/psr/psr-2/).
*Note that this is a different codestyle than nextcloud itself uses.*
To test the codestyle you can run `make phpcs`.

For linting JavaScript, a [jshint file](https://github.com/nextcloud/news/blob/master/js/.jshintrc) is used that is run before compiling the JavaScript
