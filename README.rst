README
======

The News app is a an rss/atom feed aggregator. It is based on the library SimplePie.

Status
------
App is not finished yet and in rewrite.

When the first kinda working version is available it will be mentioned in this readme.

See the milestones in the Bugtracker for more information on progress


How to install the news app
---------------------------
- Install ownCloud 5.0
- Clone the App Framework app into the **/var/www** directory::

	git clone https://github.com/owncloud/appframework.git

- Clone the News app into the **/var/www** directory::

	git clone https://github.com/owncloud/news.git


- Link both into ownCloud's apps folder::

	ln -s /var/www/appframework /var/www/owncloud/apps
	ln -s /var/www/news /var/www/owncloud/apps

- Activate the App Framework App first, then activate the News app in the apps menu
