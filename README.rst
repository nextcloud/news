README
======

The News app is a an RSS/Atom feed aggregator. It is based on the library SimplePie.

Maintainers
-----------
* `Alessandro Cosentino <https://github.com/zimba12>`_ 
* `Bernhard Posselt <https://github.com/Raydiation>`_ 
* `Jan-Christoph Borchardt <https://github.com/jancborchardt>`_ (Design)

Bugs
----
Before reporting bugs:

* We do not support Internet Explorer and Safari (Patches accepted though, except for IE < 10)
* get the newest version of the App Framework
* get the newest version of the News app
* `check if they have already been reported <https://github.com/owncloud/news/issues?state=open>`_

If you are not able to add a feed because its XML does not validate and  (see `this issue <https://github.com/owncloud/news/issues/133>`_ for an example), 
check if:

* you are able to add the feed in other feed readers
* it runs without error through `SimplePie demo <http://www.simplepie.org/demo/>`_

In the case the second condition is not met, please file a bug on `SimplePie issue tracker <https://github.com/simplepie/simplepie>`_.


Before you install the News app
-------------------------------
Before you install the app check that the following requirements are met:

- `Magic quotes are turned off <http://php.net/manual/de/security.magicquotes.disabling.php>`_ (only needed for PHP < 5.4)
- `You use a browser that supports the FileReader API <https://developer.mozilla.org/en/docs/DOM/FileReader#Browser_compatibility>`_
- You can use a cron or webcron to call Background Jobs in ownCloud
- You have installed **php-curl** and activated it in the **php.ini**
- Install ownCloud **5.0.6** (important!)

Should you have upgraded from a prior version, disable the CSS and JavaScript caching by adding this to :file:`owncloud/config/config.php`::

    DEFINE('DEBUG', true);

You can remove the line after a page reload

Update Notices
--------------
Should you have **updated from a version prior to 0.100** you'll have to run this SQL query to trigger a schema upgrade::

    UPDATE oc_appconfig SET configvalue = '0.001' WHERE configkey = 'installed_version' AND appid = 'news'

App Store
---------

Installation
~~~~~~~~~~~~

- Go to the ownCloud apps page
- Activate the **App Framework** App first, then activate the **News** app in the apps menu
- `Set up ownCloud Background Jobs <http://doc.owncloud.org/server/5.0/admin_manual/configuration/background_jobs.html>`_ to enable feed updates. A recommended timespan for feed updates is 15-30 Minutes.

Keep up to date
~~~~~~~~~~~~~~~
Both the **News** and **App Framework** App can be updated through the ownCloud apps page. 


Git (development version)
-------------------------

Installation
~~~~~~~~~~~~

- Clone the **App Framework** app into the **/var/www** directory::

	git clone https://github.com/owncloud/appframework.git

- Clone the **News** app into the **/var/www** directory::

	git clone https://github.com/owncloud/news.git


- Link both into ownCloud's apps folder::

	ln -s /var/www/appframework /var/www/owncloud/apps
	ln -s /var/www/news /var/www/owncloud/apps

- Activate the **App Framework** App first, then activate the **News** app in the apps menu

- `Set up ownCloud Background Jobs <http://doc.owncloud.org/server/5.0/admin_manual/configuration/background_jobs.html>`_ to enable feed updates. A recommended timespan for feed updates is 15-30 Minutes.

Keep up to date
~~~~~~~~~~~~~~~

To get the newest update you can use git. To update the appframework use::

    cd /var/www/appframework
    git pull --rebase origin master


To update the News app use::

    cd /var/www/news
    git pull --rebase origin master


Keyboard shortcuts
------------------
* **Next item**: n / j / right arrow
* **Previous item**: p / k / left arrow
* **Star current item**: s / i
* **Keep current item unread**: u
* **Star item and jump to next one**: h 

Mobile Clients
--------------
Official
~~~~~~~~
* **Author**: `Bernhard Posselt <https://github.com/Raydiation>`_
* **Link**: `https://github.com/owncloud/news-mobile <https://github.com/owncloud/news-mobile>`_
* **Status**: In development
* **Platform**: Android, iOS, Windows Phone (PhoneGap), FirefoxOS
* **License**: AGPL
* **Bugtracker**: `https://github.com/owncloud/news-mobile/issues <https://github.com/owncloud/news-mobile/issues>`_

Unofficial
~~~~~~~~~~
* **Author**: `David Luhmer <https://github.com/David-Development>`_
* **Link**: `Owncloud News Reader <http://david-luhmer.de/wordpress/?p=126>`_
* **Google play Store**: `buy the App <https://play.google.com/store/apps/details?id=de.luhmer.owncloudnewsreader>`_
* **Status**: Beta
* **Platform**: Android
* **License**: AGPL
* **Bugtracker**: `https://github.com/owncloud/News-Android-App/issues <https://github.com/owncloud/News-Android-App/issues>`_

Desktop Clients
---------------

Performance Notices
-------------------
* Use MySQL or PostgreSQL for better database performance

Known Problems
--------------
* **Feeds are not updated anymore**: `this is a bug in the core backgroundjob system <https://github.com/owncloud/core/issues/3221>`_ deleting the :file:`owncloud/data/cron.lock` file gets the cron back up running

Configuration
-------------
This will be in a seperate config file in the future but for now you can tweak the folowing things. 

:file:`dependencyinjection/dicontainer.php`

* To cache feeds longer increase::
 
    $this['simplePieCacheDuration'] = 30*60;  // seconds

* To let people have more read items per feed before they are purged increase::

    $this['autoPurgeCount'] = 200;  // per feed

:file:`js/app/app.coffee`

All changes in the coffee file have to be compiled by using::

    make

in the **js/** directory

* To increase the interval when the app fetches new entries from database(!, not the webpage, thats set by the backgroundjob interval) change::

    feedUpdateInterval: 1000*60*3  # miliseconds


Building the package
--------------------
To build the app simply run::

    make

Then build the package with::

    make dist

The package lies in the **build/** directory and is ready to be uploaded to `the App-Store <http://apps.owncloud.com>`_

Running tests
-------------
All tests
~~~~~~~~~
To run them execute::

    make test

PHP Unit tests
~~~~~~~~~~~~~~
To run them execute::

    make unit-tests

Integration tests
~~~~~~~~~~~~~~~~~
To run them execute::

    make integration-tests

Acceptance tests
~~~~~~~~~~~~~~~~
.. note:: For acceptance tests, a user with the name **test** and password **test** must exist!

To change the url under which ownCloud is installed, set the environment variable $OWNCLOUD_HOST::

    export OWNCLOUD_HOST="localhost/core"

Otherwise it defaults to **localhost/owncloud**,

To run them execute::

    make acceptance-tests

JavaScript unit tests
~~~~~~~~~~~~~~~~~~~~~
To run them execute::

    make javascript-tests
