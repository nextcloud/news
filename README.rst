README
======

Status
------
App is not finished yet and in rewrite.

When the first kinda working version is available it will be mentioned in this readme.


Roadmap
-------
All estimations include unittests

* Referential integrity (delete items and feeds when feed or folder with FK was deleted) ~ **3h**
* Port coffeescript ~ **35h**
* make export work -> generate xml with template ~ **3h**
* make feed update work ~ **5-10h**
* fix search plugin ~ **2h**
* fix background job ~ **2h**


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