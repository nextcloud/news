README
======
App is not finished yet and in rewrite


TODO
----

* Referential integrity (delete items and feeds when feed or folder with FK was deleted)
* Port coffeescript
* make export work -> generate xml with template
* make feed update work
* fix search plugin
* fix background job


How to install the news app
---------------------------
- Install ownCloud 5.0
- Clone the App Framework app into the /var/www directory::

	git clone https://github.com/owncloud/appframework.git

- Clone the News app into the /var/www directory::

	git clone https://github.com/owncloud/news.git


- Link both into ownCloud's apps folder::

	ln -s /var/www/appframework /var/www/owncloud/apps
	ln -s /var/www/news /var/www/owncloud/apps

- Activate the App Framework App first, then activate the News app in the apps menu