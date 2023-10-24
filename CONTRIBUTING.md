# Contributing

News is developed by volunteers in their free time with some support through the Nextcloud organization and community. To keep this project alive we need contributions from volunteers, the software is provided under the [AGPL v3.0 license](https://github.com/nextcloud/news/blob/master/COPYING).

Read this when you want to:

* [report a bug](#Issues)
* [help translate the News file to your language](#Translation)
* [start programming and change the way the News app works](#development)


## General
* Be as precise in your issues as possible and make it as easy as possible to understand, this heavily influences the possibility to actually help you.
* Follow the [code of conduct](https://nextcloud.com/code-of-conduct/). Being a dick and insulting people will get your posts deleted and issues locked.
* Please follow the issue template and fill all the sections as much as you can, if not we might close your issue without any comment.

### Feed issues
**For feed parsing issues, check**:
* It is a valid RSS by running it through the [W3C validator](http://validator.w3.org/feed/)
* Use the [feed issues](https://github.com/nextcloud/news/discussions/categories/feed-issues) section in discussions.

### Hints for reporting bugs
* We do not support Internet Explorer and Safari (Patches accepted though, except for IE < 10)
* Get the latest version of the News app, check the [Nextcloud App Store](https://apps.nextcloud.com/apps/news/releases).
* Disable all browser add-ons to make sure that it's not a plugin's fault (adblockers, especially cosmetic filters)
* Clear your PHP opcode cache if you use any by restarting your webserver.
* [Check if they have already been reported](https://github.com/nextcloud/news/issues?state=open)
* [Check if your problem is covered in the Troubleshooting section](https://nextcloud.github.io/news/troubleshooting/)

### Debugging issues
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

### When Requesting Features
Please always provide the use case in addition solution, e.g.:

* "If I read feed that has comics, the newest first ordering does not work well because I have to read from the bottom up"

is much more helpful than just writing:

* "Please add reverse ordering".

Features don't belong into the issues section, instead discuss them in [discussions](https://github.com/nextcloud/news/discussions/categories/features).

## Translation

For translations in other languages than English, we rely on the [Transifex](https://www.transifex.com/) platform.

If you want to help with translating the app, please do not create a pull request. Instead, head over to https://www.transifex.com/nextcloud/nextcloud/news/ and join the team of your native language.

If approved, the translation will be automatically ported to the code within 24 hours.

## Development
You can first create a [discussion](https://github.com/nextcloud/news/discussions/categories/features) where you explain why, what and how you want to make a change before writing any code. If you want to agree on a solution first.

This not required however.

### How to set up a development environment
To get started setup an developer [environment](https://docs.nextcloud.com/server/latest/developer_manual/getting_started/devenv.html). Inside the apps directory clone the news repository and enable the app with occ.
Make sure you have all [dependencies](https://github.com/nextcloud/news/blob/master/docs/install.md#build-dependencies) installed.

To build the app run:

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

### Developer Certificate of Origin (DCO)
When you commit your change, remember to sign off that you adhere to [DCO requirements](https://developercertificate.org/) as described by [Probot](https://probot.github.io/apps/dco/).

### Change log
Before you create a pull request, please remember to add an entry to [CHANGELOG.md](https://github.com/nextcloud/news/blob/HEAD/CHANGELOG.md), using the format [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).
