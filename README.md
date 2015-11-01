# README

[![Build Status](https://travis-ci.org/owncloud/news.svg?branch=master)](https://travis-ci.org/owncloud/news)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/owncloud/news/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/owncloud/news/?branch=master)


The News app is an RSS/Atom feed aggregator. It offers a [RESTful API](https://github.com/owncloud/news/wiki/API-1.2) for app developers. The source code is [available on GitHub](https://github.com/owncloud/news)

For further developer and user documentation please visit [the wiki](https://github.com/owncloud/news/wiki)

## Maintainers
* [Bernhard Posselt](https://github.com/BernhardPosselt)
* [Alessandro Cosentino](https://github.com/cosenal)
* [Jan-Christoph Borchardt](https://github.com/jancborchardt) (Design)

## Sync Clients
are listed on the [ownCloud apps overview](https://github.com/owncloud/core/wiki/Apps)

## Dependencies
* ownCloud >= 8.1
* libxml >= 2.7.8 (2.9 recommended)
* php-curl
* iconv
* SimpleXML
* PHP >= 5.5

## Supported Linux Distributions
Supported means that the distribution's default repository packages will work in conjunction with the News app and you won't have to add any 3rdparty repositories.

The following distros are supported:

* Ubuntu 14.04
* Debian 8 (Jessie)
* Arch Linux

## Supported Browsers
* Newest Firefox (Desktop, Android, Firefox OS)
* Newest Chrome/Chromium (Desktop, Android)

## Supported Databases
* PostgreSQL (recommended)
* MySql
* SQLite (discouraged)

## Bugs
Please read the [appropriate section in the contributing notices](https://github.com/owncloud/news/blob/master/CONTRIBUTING.md#issues)

## Installation/Update

### Before you install/update the News app
Before you install the app do the following:
* Check that your **owncloud/data/** directory is owned by your webserver user and that it is write/readable
* Check that your installation fullfills the [requirements listed in the README section](https://github.com/owncloud/news#dependencies)
* [Set up ownCloud Background Jobs](https://doc.owncloud.org/server/8.0/admin_manual/configuration_server/background_jobs_configuration.html) to enable feed updates. A recommended timespan for feed updates is 15-30 Minutes.
* If you are updating from a previous version read the [Update Notices](https://github.com/owncloud/news/blob/master/README.md#updating-notices)

Then proceed to install the app either from an archive (zip/tar.gz) or clone it from the repository using git

### Installing from archive
* Go to the [ownCloud News GitHub releases page](https://github.com/owncloud/news/releases) and download the latest release/archive to your server
* On your server, check if there is a folder called **owncloud/apps/news**. If there is one, delete it.
* Extract the downloaded archive to the **owncloud/apps/** folder.
* Remove the version from the extracted folder (e.g. rename **owncloud/apps/news-4.0.3/** to **owncloud/apps/news/**
* Activate the **News** app in the apps menu

### Installing from Git (development version)
* The master branch will always be stable in conjunction with the latest master branch from ownCloud
* In your terminal go into the **owncloud/apps/** directory and then run the following command:

        git clone https://github.com/owncloud/news.git

* If you are using a stable ownCloud release, stay with the [latest git tag release which is running on your version](https://github.com/owncloud/news/releases). To get an overview over all existing tags run:

        git tag

 You can switch to a release which will be supported on your installation by running:

      git checkout tags/TAG

 For instance to use the 5.2.8 release, run:

      git checkout tags/5.2.8

* Activate the **News** app in the apps menu

To update the News app use change into the **owncloud/apps/news/** directory using your terminal and then run:

    git pull --rebase origin master

## Performance Notices
* Use MySQL or PostgreSQL for better database performance
* Use the [updater script to thread and speed up the update](https://github.com/owncloud/news/wiki/Custom-Updater)
* Feed updates on plattforms using **php-fpm are significantly slower** due to workarounds which are needed to deal with [libxml not being threadsafe](https://bugs.php.net/bug.php?id=64938)

## Updating Notices

To receive notifications when a new News app version was released, simply add the following Atom feed in your currently installed News app:

    https://github.com/owncloud/news/releases.atom


### Updating from versions prior to 4

You need to do the following:

* Get rid of **simplePieCacheDuration** setting by removing this setting from your **owncloud/data/news/config/config.ini**.

### After updating from a version prior to 4 all my read articles reappear as unread and there are duplicates
We switched to a different feed parsing library which creates article ids differently than before. This means that the same article is not found in the database because it was generated with a different id and is thus readded. This should happen only once for each feed after the upgrade and there is no data loss. Unfortunately there is no fix for this since the id is a hash which can not be reversed, so a smooth transition is not possible.

### Updating from versions prior to 5.3.0

5.3.0 adds the possibility to search your articles. To do this efficiently however, the News app needs to generate an index. This is done automatically for new articles, but older articles need to be migrated. Because large installations have millions of articles, generating the search index has been offloaded to a separate command to prevent timeouts when upgrading the app. To make your old articles searchable run this command in your ownCloud top directory:

    ./occ news:create-search-indices

## FAQ

### I am getting Exception: Some\\Class does not exist erros in my owncloud.log
This is very often caused by missing or old files, e.g. by failing to upload all of the News app' files or errors during installation. Before you report a bug, please recheck if all files from the archive are in place and accessible.

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

### Feeds are not updated
* Check if the cronjob exists with **crontab -u www-data -e** (replace www-data with your httpd user)
* Check the file permissions of the **cron.php** file and if **www-data** (or whatever your httpd user is called like) can read and execute that script
* Check if the cronjob is ever executed by placing an **error_log('updating')** in the [background job file](https://github.com/owncloud/news/blob/master/cron/updater.php#L27). If the cronjob runs, there should be an updating log statement in your httpd log.
* If there is no **updating** statement in your logs check if your cronjob is executed by executing a different script
* If your cron works fine but owncloud's cronjobs are never executed, file a bug in [core](https://github.com/owncloud/core/)
* Try the [updater script](https://github.com/owncloud/news/wiki/Custom-Updater)

### Adding feeds that use self-signed certificates
If you want to add a feed that uses a self-signed certificate that is not signed by a trusted CA the request will fail with "SSL certficate is invalid". A common solution is to turn off the certificate verification **which is wrong** and **makes your installation vulnerable to MITM attacks**. Therefore **turning off certificate verification is not supported**.

If you have control over the feed in question, consider signing your certificate for free using [StartSSL](https://www.startssl.com/) or wait until September when [letsencrypt.com](http://letsencrypt.com/) goes online.

If you do not have control over the chosen feed, you should [download the certificate from the feed's website](http://superuser.com/questions/97201/how-to-save-a-remote-server-ssl-certificate-locally-as-a-file) and [add it to your server's trusted certificates](https://turboflash.wordpress.com/2009/06/23/curl-adding-installing-trusting-new-self-signed-certificate/). The exact procedure however may vary depending on your distribution.


Configuration
-------------
All configuration values are set inside **owncloud/data/news/config/config.ini** and can be edited in the admin panel.

The configuration is in **INI** format and looks like this:

```ini
autoPurgeMinimumInterval = 60
autoPurgeCount = 200
maxRedirects = 10
maxSize = 104857600
feedFetcherTimeout = 60
useCronUpdates = true
exploreUrl =
```


* **autoPurgeMinimumInterval**: Minimum amount of seconds after deleted feeds and folders are removed from the database. Values below 60 seconds are ignored
* **autoPurgeCount**: Defines the minimum amount of articles that can be unread per feed before they get deleted, a negative value will turn off deleting articles completely
* **maxRedirects**: How many redirects the updater should follow
* **maxSize**: Maximum feed size in bytes. If the RSS/Atom page is bigger than this value, the update will be aborted
* **feedFetcherTimeout**: Maximum number of seconds to wait for an RSS or Atom feed to load. If a feed takes longer than that number of seconds to update, the update will be aborted
* **useCronUpdates**: To use a custom update/cron script you need to disable the cronjob which is run by ownCloud by default by setting this to false
* **exploreUrl**: If given that url will be contacted for fetching content for the explore feed


Commands
--------
The following commands are available when using the **occ** file in the top directory:

* **Show help and available commands**:

  ./occ

* **Generate search indices**:

  ./occ news:create-search-indices

Translations
------------
For translations in other languages than English, we rely on the [Transifex](https://www.transifex.com/) platform.

If you want to help with translating the app, please do not create a pull request. Instead, head over to https://www.transifex.com/projects/p/owncloud/resource/news/ and join the team of your native language.

If approved, the translation will be automatically ported to the code within 24 hours.
