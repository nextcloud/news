# README

[![Build Status](https://travis-ci.org/owncloud/news.svg?branch=master)](https://travis-ci.org/owncloud/news)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/owncloud/news/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/owncloud/news/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/owncloud/news/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/owncloud/news/?branch=master)

The News app is an RSS/Atom feed aggregator. It offers a [RESTful API](https://github.com/owncloud/news/wiki/API-1.2) for app developers. The source code is [available on GitHub](https://github.com/owncloud/news)

For further developer and user documentation please visit [the wiki](https://github.com/owncloud/news/wiki)

## Maintainers
* [Bernhard Posselt](https://github.com/Raydiation)
* [Alessandro Cosentino](https://github.com/cosenal)
* [Jan-Christoph Borchardt](https://github.com/jancborchardt) (Design)

## Sync Clients
are listed on the [ownCloud apps overview](https://github.com/owncloud/core/wiki/Apps)

## Dependencies
* ownCloud >= 7.0.3
* PHP >= 5.4
* libxml >= 2.7.8 (2.9 recommended)
* php-curl
* zlib (if installed from the appstore)
* SimpleXML

## Supported Operating Systems
All of the listed stable Linux distributions will be supported until their next stable version's first bugfix release is released (e.g. Debian 7 is supported until Debian 8.1 is released):

* Ubuntu 14.04
* Debian 7 (Wheezy)
* CentOS 7
* Arch Linux

## Supported Browsers
* Newest Firefox (Desktop, Android, Firefox OS)
* Newest Chrome/Chromium (Desktop, Android)

## Supported Databases
* PostgreSQL
* SQLite
* MySql

## Bugs

### Before reporting bugs

* We do not support Internet Explorer and Safari (Patches accepted though, except for IE < 10)
* get the newest version of the News app
* [check if they have already been reported](https://github.com/owncloud/news/issues?state=open)

If you are not able to add a feed because its XML *does not validate* (see [this issue](https://github.com/owncloud/news/issues/133) for an example),
check if:

* it is a valid RSS by running it through the [W3C validator](http://validator.w3.org/feed/)
* you are able to add the feed in other feed readers
* it runs without error through [SimplePie demo](http://www.simplepie.org/demo/)

In the case the third condition is not met, please file a bug on [SimplePie issue tracker](https://github.com/simplepie/simplepie).

### When reporting bugs

* Turn on debug level debug by adding **"loglevel" => 0,** to your **config/config.php** and reproduce the problem
* check **data/owncloud.log**

Please provide the following details so that your problem can be fixed:

* **data/owncloud.log** (important!)
* ownCloud version
* News version
* Browser and version
* PHP version
* Distribution

## Before you install the News app
Before you install the app check that the following requirements are met:

* You can use a cron or webcron to call Background Jobs in ownCloud
* Your **data/** directory is owned by your webserver user and write/readable
* You have installed **php-curl** and activated it in the **php.ini**
* You run **ownCloud 7**

Should you have upgraded from a prior version, disable the CSS and JavaScript caching by adding this to **owncloud/config/config.php**:

    DEFINE('DEBUG', true);

You can remove the line after a page reload


## App Store

* Go to the ownCloud apps page
* Activate the **News** app in the apps menu
* If the app fails to install, add this at the bottom of the file **config/config.php**:

	  $CONFIG["appcodechecker"] = false;

* [Set up ownCloud Background Jobs](http://doc.owncloud.org/server/7.0/admin_manual/configuration/background_jobs.html) to enable feed updates. A recommended timespan for feed updates is 15-30 Minutes.

The **News** App can be updated through the ownCloud apps page.


### Git (development version)
* The master branch will always be stable in conjunction with the latest master branch from ownCloud
* Clone the **News** app into the **/var/www/owncloud/apps/** directory:

	git clone https://github.com/owncloud/news.git

* If the app fails to install, add this at the bottom of the file **config/config.php**:

	  $CONFIG["appcodechecker"] = false;

* Activate the **News** app in the apps menu

* [Set up ownCloud Background Jobs](http://doc.owncloud.org/server/7.0/admin_manual/configuration/background_jobs.html) to enable feed updates. A recommended timespan for feed updates is 15-30 Minutes.

To update the News app use:

    cd /var/www/owncloud/apps/news
    git pull --rebase origin master


## Performance Notices
* Use MySQL or PostgreSQL for better database performance
* Use the [updater script to thread and speed up the update](https://github.com/owncloud/news/wiki/Cron-1.2)
* Feed updates on plattforms using **php-fpm are significantly slower** due to workarounds which are needed to deal with [libxml not being threadsafe](https://bugs.php.net/bug.php?id=64938)

## FAQ

### How do I reset the News app
Delete the folder **owncloud/apps/news/** and **owncloud/data/news/**, then connect to your database and run the following commands where **oc\_** is your table prefix (defaults to oc\_)

```sql
DELETE FROM oc_appconfig WHERE appid = 'news';
DROP TABLE oc_news_items;
DROP TABLE oc_news_feeds;
DROP TABLE oc_news_folders;
```

### App is stuck in maintenance mode after failed update

Check the **owncloud/data/owncloud.log** for hints why it failed. After the issues are fixed, turn off the maintenance mode by editing your **owncloud/config/config.php** by setting the **maintenance** key to false:

    "maintenance" => false,

### All feeds are not updated anymore
[This is a bug in the core backgroundjob system](https://github.com/owncloud/core/issues/3221) deleting the **owncloud/data/cron.lock** file gets the cron back up running

Another way to fix this is to run a custom [updater script](https://github.com/owncloud/news/wiki/Cron-1.2)

### All feeds are not updated and theres no cron.lock
* Check if the cronjob exists with **crontab -u www-data -e** (replace www-data with your httpd user)
* Check the file permissions of the **cron.php** file and if **www-data** (or whatever your httpd user is called like) can read and execute that script
* Check if the cronjob is ever executed by placing an **error_log('updating')** in the [background job file](https://github.com/owncloud/news/blob/master/backgroundjob/task.php#L37). If the cronjob runs, there should be an updating log statement in your httpd log.
* If there is no **updating** statement in your logs check if your cronjob is executed by executing a different script
* If your cron works fine but owncloud's cronjobs are never executed, file a bug in [core](https://github.com/owncloud/core/)
* Try the [updater script](https://github.com/owncloud/news/wiki/Cron-1.2)


Configuration
-------------
All configuration values are set inside **owncloud/data/news/config/config.ini**

The configuration is in **INI** format and looks like this:

```ini
autoPurgeMinimumInterval = 60
autoPurgeCount = 200
simplePieCacheDuration = 1800
feedFetcherTimeout = 60
useCronUpdates = true
```


* **autoPurgeMinimumInterval**: Minimum amount of seconds after deleted feeds and folders are removed from the database. Values below 60 seconds are ignored
* **autoPurgeCount**: Defines the minimum amount of articles that can be unread per feed before they get deleted
* **simplePieCacheDuration**: Amount of seconds to cache feeds
* **feedFetcherTimeout**: Maximum number of seconds to wait for an RSS or Atom feed to load. If a feed takes longer than that number of seconds to update, the update will be aborted
* **useCronUpdates**: To use a custom update/cron script you need to disable the cronjob which is run by ownCloud by default by setting this to false

Translations
------------
For translations in other languages than English, we rely on the [Transifex](https://www.transifex.com/) platform.

If you want to help with translating the app, please do not create a pull request. Instead, head over to https://www.transifex.com/projects/p/owncloud/resource/news/ and join the team of your native language.

If approved, the translation will be automatically ported to the code within 24 hours.


