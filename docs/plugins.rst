How to write plugins for the News app
=====================================
You've got this cool idea that you'd like the News app to have but the developers are pricks and don't want to implement it? Create a plugin!

General plugin infos
--------------------
A plugin is in essence a seperate app. You should first read the `intro <http://doc.owncloud.org/server/master/developer_manual/app/intro/createapp.html>`_ and `tutorial <http://doc.owncloud.org/server/master/developer_manual/app/appframework/tutorial.html>`_ and create the basic files.

In addition to the basic structure you also want to make sure that the News app is enabled. To do that open :file:`my_news_plugin/appinfo/app.php` and add the following if:

.. code-block:: php

	<?php 	
	namespace MyNewsPlugin;

	use \OCA\AppFramework\Core\API;

	if(\OCP\App::isEnabled('news') && \OCP\App::isEnabled('appframework')){

		// your code here

	}

Serverside plugin
-----------------
A serverside plugin is a plugin that uses the same infrastructure to add additional features. An example would be a plugin that makes the starred entries of a user available via an interface.

Its very easy to interface with the News app. Because all Classes are registered in the :file:`news/dependencyinjection/dicontainer.php` it takes almost no effort to use the same infrastructure.

Since you dont want to extend the app but use its resources, its advised that you dont inherit from the **DIContainer** class but rather include it in your own container in :file:`my_news_plugin/dependencyinjection/dicontainer.php`:

.. code-block:: php

	<?php 
	namespace OCA\MyNewsPlugin\DependencyInjection;

	use \OCA\AppFramework\DependencyInjection\DIContainer as BaseContainer;
	use \OCA\News\DependencyInjection\DIContainer as NewsContainer;

	class DIContainer extends BaseContainer {


		/**
		 * Define your dependencies in here
		 */
		public function __construct () {
			// tell parent container about the app name
			parent::__construct('my_news_plugin');

			$this['NewsContainer'] = $this->share(function ($c) {
				// make the newscontainer available in your app
				return new NewsContainer();
			});

			$this['YourController'] = $this->share(function ($c) {
				// use the feedbusinesslayer from the news app
				// you can use all defined classes but its recommended that you 
				// stick to the mapper and businesslayer classes since they are less
				// likely to change
				return new YourController($c['NewsContainer']['FeedBusinessLayer']);
			});
		}

		
	}

Using this method you can basically access all the functionality of the news app in your own app.

Clientside plugin
-----------------
A clientside plugin could either be a new interface for the news app or a script that enhances the current app.

Custom script
~~~~~~~~~~~~~
To add a simple script create the script in the :file:`my_news_plugin/js/script.js`, then use this inside your :file:`my_news_plugin/appinfo/app.php`:

.. code-block:: php
	
	<?php 
	namespace MyNewsPlugin;

	use \OCA\AppFramework\Core\API;

	if(\OCP\App::isEnabled('news') && \OCP\App::isEnabled('appframework')){

		$api = new API('my_news_plugin');
		$api->addScript('script.js'); // add a script from js/script.js
		$api->addStyle('style.css'); // add a stylesheet from css/styles.css

	}

Inside your script you have to make sure that the News app is active. You can do that by using:

.. code-block:: js
	
	(function ($, window, undefined) {

		var document = window.document;

		$(document).ready(function () {
			if ($('[ng-app="News"]').length > 0) {

				// your code here

			}
		});

	})(jQuery, window);


Custom user Interface
~~~~~~~~~~~~~~~~~~~~~
This is currently not yet possible to do but we're working on it ;)

These issues need to be implemented:

* `Implement RESTful urls for the web backend <https://github.com/owncloud/news/issues/166>`_
* `Move configuration into a config file instead of hard coding it in the container <https://github.com/owncloud/news/issues/167>`_
* `Transition to Twig Templates <https://github.com/owncloud/news/issues/165>`_
* `Seperate directives, filters, controllers and services into their own angularjs containers <https://github.com/owncloud/news/issues/164>`_