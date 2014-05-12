README
======
|travis-ci|_ |scrutinizer|_

The News app is a an RSS/Atom feed aggregator. It offers a `RESTful API <https://github.com/owncloud/news/wiki/API-1.2>`_ for app developers. The source code is `available on GitHub <https://github.com/owncloud/news>`_

For further developer and user documentation please visit `the wiki <https://github.com/owncloud/news/wiki>`_

.. |travis-ci| image:: https://travis-ci.org/owncloud/news.png
.. _travis-ci: https://travis-ci.org/owncloud/news

.. |scrutinizer| image:: https://scrutinizer-ci.com/g/owncloud/news/badges/quality-score.png?b=master
.. _scrutinizer: https://scrutinizer-ci.com/g/owncloud/news



Maintainers
-----------
* `Alessandro Cosentino <https://github.com/cosenal>`_
* `Bernhard Posselt <https://github.com/Raydiation>`_
* `Jan-Christoph Borchardt <https://github.com/jancborchardt>`_ (Design)

Minimum PHP Version
-------------------
* PHP >= 5.3.6

Minimum ownCloud Version
-------------------
* >= 6.0.3

Supported Webservers
--------------------
* Apache

Supported Databases
-------------------
* Postgresql
* Sqlite
* MySql


Bugs
----
Before reporting bugs:

* We do not support Internet Explorer and Safari (Patches accepted though, except for IE < 10)
* get the newest version of the News app
* `check if they have already been reported <https://github.com/owncloud/news/issues?state=open>`_

----------------

If you are not able to add a feed because its XML *does not validate* (see `this issue <https://github.com/owncloud/news/issues/133>`_ for an example),
check if:

* it is a valid RSS by running it through the `W3C validator <http://validator.w3.org/feed/>`_
* you are able to add the feed in other feed readers
* it runs without error through `SimplePie demo <http://www.simplepie.org/demo/>`_

In the case the third condition is not met, please file a bug on `SimplePie issue tracker <https://github.com/simplepie/simplepie>`_.


Before you install the News app
-------------------------------
Before you install the app check that the following requirements are met:

- `Magic quotes are turned off <http://php.net/manual/de/security.magicquotes.disabling.php>`_ (only needed for PHP < 5.4)
- `You use a browser that supports the FileReader API <https://developer.mozilla.org/en/docs/DOM/FileReader#Browser_compatibility>`_
- You can use a cron or webcron to call Background Jobs in ownCloud
- Your **data/** directory is owned by your webserver user and write/readable
- You have installed **php-curl** and activated it in the **php.ini**
- Install ownCloud **6.0.3+** (important!)

Should you have upgraded from a prior version, disable the CSS and JavaScript caching by adding this to :file:`owncloud/config/config.php`::

    DEFINE('DEBUG', true);

You can remove the line after a page reload


App Store
---------

Installation
~~~~~~~~~~~~

- Go to the ownCloud apps page
- Activate the **News** app in the apps menu
- `Set up ownCloud Background Jobs <http://doc.owncloud.org/server/5.0/admin_manual/configuration/background_jobs.html>`_ to enable feed updates. A recommended timespan for feed updates is 15-30 Minutes.

Keep up to date
~~~~~~~~~~~~~~~
The **News** App can be updated through the ownCloud apps page.


Git (development version)
-------------------------

Installation
~~~~~~~~~~~~

- Clone the **News** app into the **/var/www/owncloud/apps/** directory::

	git clone https://github.com/owncloud/news.git

- Activate the **News** app in the apps menu

- `Set up ownCloud Background Jobs <http://doc.owncloud.org/server/5.0/admin_manual/configuration/background_jobs.html>`_ to enable feed updates. A recommended timespan for feed updates is 15-30 Minutes.

Keep up to date
~~~~~~~~~~~~~~~
To update the News app use::

    cd /var/www/owncloud/apps/news
    git pull --rebase origin master


Keyboard shortcuts
------------------
* **Next item**: n / j / right arrow
* **Previous item**: p / k / left arrow
* **Star current item**: s / i
* **Keep current item unread**: u
* **Star item and jump to next one**: h
* **Open current item**: o
* **Toggle expand of current item in compact view**: e

Performance Notices
-------------------
* Use MySQL or PostgreSQL for better database performance
* Use the `updater script for large installations to thread the update <https://github.com/owncloud/news/wiki/Cron-1.2>`_

Frequent Problems
-----------------

How do I reset the News app
~~~~~~~~~~~~~~~~~~~~~~~~~~~
Delete the folder **owncloud/apps/news**, then connect to your database and run the following commands where **oc\_** is your table prefix (defaults to oc\_)

.. code-block:: sql

    DELETE FROM oc_appconfig WHERE appid = 'news';
    DROP TABLE oc_news_items;
    DROP TABLE oc_news_feeds;
    DROP TABLE oc_news_folders;

All feeds are not updated anymore
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
`This is a bug in the core backgroundjob system <https://github.com/owncloud/core/issues/3221>`_ deleting the :file:`owncloud/data/cron.lock` file gets the cron back up running

Another way to fix this is to run a custom `updater script <https://github.com/owncloud/news/wiki/Cron-1.2>`_

All feeds are not updated and theres no cron.lock
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
* Check if the cronjob exists with **crontab -u www-data -e** (replace www-data with your httpd user)
* Check the file permissions of the **cron.php** file and if **www-data** (or whatever your httpd user is called like) can read and execute that script
* Check if the cronjob is ever executed by placing an **error_log('updating')** in the `background job file <https://github.com/owncloud/news/blob/master/backgroundjob/task.php#L37>`_. If the cronjob runs, there should be an updating log statement in your httpd log.
* If there is no **updating** statement in your logs check if your cronjob is executed by executing a different script
* If your cron works fine but owncloud's cronjobs are never executed, file a bug in `core <https://github.com/owncloud/core/>`_
* Try the `updater script <https://github.com/owncloud/news/wiki/Cron-1.2>`_

News always redirects to files
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
This problem is related to opcode caching, `check the issue tracker for how to solve it <https://github.com/owncloud/news/issues/319>`_

Configuration
-------------
All configuration values are set inside :file:`owncloud/data/news/config/config.ini`

The configuration is in **INI** format and looks like this:

.. code-block:: ini

    autoPurgeMinimumInterval = 60
    autoPurgeCount = 200
    simplePieCacheDuration = 1800
    feedFetcherTimeout = 60
    useCronUpdates = true
    proxyHost =
    proxyPort = 8080
    proxyUser =
    proxyPassword =


* **autoPurgeMinimumInterval**: Minimum amount of seconds after deleted feeds and folders are removed from the database.
* **autoPurgeCount**: Defines the minimum amount of articles that can be unread per feed before they get deleted
* **simplePieCacheDuration**: Amount of seconds to cache feeds
* **feedFetcherTimeout**: Maximum number of seconds to wait for an RSS or Atom feed to load. If a feed takes longer than that number of seconds to update, the update will be aborted
* **useCronUpdates**: To use a custom update/cron script you need to disable the cronjob which is run by ownCloud by default by setting this to false
* **proxHost**: The adress of the proxy. Leave blank if no proxy should be used
* **proxyPort**: The proxy port, defaults to 8080
* **proxyUser**: The proxy user name for the login. Must not container colons. Leave blank if there is none. 
* **proxyPassword**: The proxy user password for the login



