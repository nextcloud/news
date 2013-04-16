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
Before reporting bugs, `please check if they already have been reported <https://github.com/owncloud/news/issues?state=open>`_.

Before you install the News app
-------------------------------
Before you install the app check that the following requirements are met:

- Your database uses utf-8
- Your webserver uses utf-8
- You use a browser that supports the FileReader API


How to install the News app
---------------------------

- Install **ownCloud 5.0.5** (not released yet contains a small css fix, use the stable5 branch or 5.0.4)

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

- Adjust the rights so that the webserver can write into the cache directory::

    sudo chown -R www-data:www-data /var/www/news/cache

How to keep up to date
----------------------
To get the newest update you can use git. To update the appframework use::

    cd /var/www/appframework
    git pull --rebase origin master


To update the News app use::

    cd /var/www/news
    git pull --rebase origin master
