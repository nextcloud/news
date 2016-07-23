# Contributing
Read this when you want to:

* file an issue (bug or feature request)
* help translate the News file to your language
* start programming and change the way the News app works
* add cool new feeds to the feed explore section
* want to provide additional full text feed rules

## Issues
This section is split into two section:

* Everything that has to do with bugs
* How to request features

### Before Reporting Bugs

* We do not support Internet Explorer and Safari (Patches accepted though, except for IE < 10)
* We do **not support the server-side encryption app** (use client side encryption instead)
* Get the latest version of the News app
* Disable all browser add-ons to make sure that it's not a plugin's fault (adblockers!)
* Clear your PHP opcode cache if you use any by restarting your webserver. This affects any version of PHP >=5.5
* [Check if they have already been reported](https://github.com/nextcloud/news/issues?state=open)
* [Check if your problem is covered in the FAQ section](https://github.com/nextcloud/news#faq)

If you are not able to add a feed because its XML *does not validate* (see [this issue](https://github.com/nextcloud/news/issues/133) for an example),
check if:

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
* Check **data/owncloud.log**
* Check your [browser's JavaScript console for errors](http://ggnome.com/wiki/Using_The_Browser_Error_Console) if it's a client-side issue

Please provide the following details so that your problem can be fixed:

* **data/owncloud.log** (important!)
* Nextcloud version
* News version
* Browser and version
* PHP version
* Distribution and version

### When Requesting Features

Please always provide the usecase in addition solution, e.g.:

* "If I read feed that has comics, the newest first ordering does not work well because I have to read from the bottom up"

is much more helpful than just writing:

* "Please add reverse ordering".


## Translation

For translations in other languages than English, we rely on the [Transifex](https://www.transifex.com/) platform.

If you want to help with translating the app, please do not create a pull request. Instead, head over to https://www.transifex.com/projects/p/nextcloud/resource/news/ and join the team of your native language.

If approved, the translation will be automatically ported to the code within 24 hours.


## Explore feeds section
You can help to improve our explore feeds section by [providing more feeds](https://github.com/nextcloud/news/tree/master/docs/explore)

## Fulltext configurations

Nextcloud News uses [picoFeed web scrapers](https://github.com/fguillot/picoFeed/blob/master/docs/grabber.markdown). Simply create a new configuration file if needed and open a pull request on their repository. The News app syncs regularly with the most recent changes.

## Development

In general it's a good idea to **first create an issue where you explain why, what and how** you want to make a change **before writing any code**.

That way we can talk about the problem first and discuss the implementation (and of course help you with your code)

### How to set up a development environment

To get started after [cloning the repository](https://github.com/nextcloud/news#installing-from-git-development-version), install the [build dependencies](https://github.com/nextcloud/news#development-dependencies) and run:

    make

in the app directory to fetch all dependencies and compile the JavaScript. The News app uses Composer for PHP dependencies, Gulp for building the JavaScript "binary" and Bower/npm as JavaScript package manager. For more information on JavaScript development [check out the README.md in the js folder](https://github.com/nextcloud/news/blob/master/js/README.md)

For running all tests suites you can run:

    make test

Packaging is done via:

    make dist

The packages are inside the top level **build/artifacts** folder

### Contact Us!

We usually hang out on **irc.freenode.net** in the **#nextcloud-news** and **#nextcloud-dev** chat room. Just ping [BernhardPosselt](https://github.com/BernhardPosselt/) or [cosenal](https://github.com/cosenal) or write us a mail directly. Mail addresses are listed on our GitHub profiles.


### Coding Style Guidelines

* Use 4 spaces for indention. Why spaces? Because it looks the same on every machine and on the web where you can't normally control the tab width.
* Place the open curly braces on the same line as the parameter block, e.g.:
  ```php
  if (condition) {
      // code
  } else {
      // code
  }
  ```

* Place a space before and after the parameter block for if, else, for, foreach, function
* Everything should be in pascalCase except classes which should be in CamelCase
* For linting JavaScript, a [jshint file](https://github.com/nextcloud/news/blob/master/js/.jshintrc) is used that is run before compiling the JavaScript

