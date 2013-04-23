README
======

The News app is a an rss/atom feed aggregator. It is based on the library SimplePie.

Maintainers
-----------
* `Alessandro Cosentino <https://github.com/zimba12>`_ 
* `Bernhard Posselt <https://github.com/Raydiation>`_ 

Status
------
The app is in alpha status and can be tested.

See the `beta milestone in the bugtracker <https://github.com/owncloud/news/issues?milestone=3&state=open>`_ for more information on progress

Bugs
----
Before reporting bugs:

* get the newest version of the App Framework
* get the newest version of the News app
* `check if they already have been reported <https://github.com/owncloud/news/issues?state=open>`_

Before you install the News app
-------------------------------
Before you install the app check that the following requirements are met:

- Your database uses utf-8
- Your webserver uses utf-8
- You use a browser that supports the FileReader API
- You can use a cron or webcron to call Background Jobs in ownCloud
- (optional) install php-curl

How to install the News app
---------------------------

- Install ownCloud **5.0.5**

Should you have upgraded from a prior version, disable the CSS and JavaScript caching by adding this to :file:`owncloud/config/config.php`::

    DEFINE('DEBUG', true);

- Clone the App Framework app into the **/var/www** directory::

	git clone https://github.com/owncloud/appframework.git

- Clone the News app into the **/var/www** directory::

	git clone https://github.com/owncloud/news.git


- Link both into ownCloud's apps folder::

	ln -s /var/www/appframework /var/www/owncloud/apps
	ln -s /var/www/news /var/www/owncloud/apps

- Activate the App Framework App first, then activate the News app in the apps menu

- `Set up ownCloud Background Jobs <http://doc.owncloud.org/server/5.0/admin_manual/configuration/background_jobs.html>`_ to enable feed updates. A recommended timespan for feed updates is 15-30 Minutes.

How to keep up to date
----------------------
To get the newest update you can use git. To update the appframework use::

    cd /var/www/appframework
    git pull --rebase origin master


To update the News app use::

    cd /var/www/news
    git pull --rebase origin master


Keyboard shortcuts
------------------
* **Next item**: n/j/right arrow
* **Previous item**: p/k/left arrow

Performance Notices
-------------------
* It is currently discouraged to use it in large hosted installations since there is no way to restrict the backgroundjob to require a pause of X minutes. This `will be addressed <https://github.com/owncloud/news/issues/103>`_ in the `next ownCloud release <https://github.com/owncloud/core/pull/3051>`_.
* Use MySQL or PostgreSQL for better database performance

Configuration
~~~~~~~~~~~~~
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

