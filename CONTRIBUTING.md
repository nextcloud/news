# Contributing
Read this when you want to:

* file an issue (bug or feature request)
* help translate the News file to your language
* start programming and change the way the News app works

## Issues
This section is split into two section:

* Everything that has to do with bugs
* How to request features

### Before Reporting Bugs

* We do not support Internet Explorer and Safari (Patches accepted though, except for IE < 10)
* We do **not support the server-side encryption app** (use client side encryption instead)
* Get the newest version of the News app
* Clear your PHP opcode cache if you use any by restarting your webserver. This affects any version of PHP >=5.5
* [Check if they have already been reported](https://github.com/owncloud/news/issues?state=open)
* [Check if your problem is covered in the FAQ section](https://github.com/owncloud/news#faq) or [Updating notices](https://github.com/owncloud/news#updating-notices)

If you are not able to add a feed because its XML *does not validate* (see [this issue](https://github.com/owncloud/news/issues/133) for an example),
check if:

* It is a valid RSS by running it through the [W3C validator](http://validator.w3.org/feed/)
* You are able to add the feed in other feed readers


### When reporting bugs

* Enable debug mode in your **config/config.php**:
 * ownCloud >=8.2: Add the **debug** attribute to config array (if not already present) and set it to **true**:
 ```php
 <?php
 $CONFIG = array(
    // other options
    // ...
    'debug' => true,
 );
 ```
 * ownCloud < 8.2: Add **DEFINE('DEBUG', true);** at the end of the file
 ```php
 <?php
 $CONFIG = array(
    // options
 );
 DEFINE('DEBUG', true);
 ```

* Reproduce the Problem
* Check **data/owncloud.log**
* Check your [error on the client side](http://ggnome.com/wiki/Using_The_Browser_Error_Console) if it's a client-side issue

Please provide the following details so that your problem can be fixed:

* **data/owncloud.log** (important!)
* ownCloud version
* News version
* Browser and version
* PHP version
* Distribution

### When requesting features

Please always provide the usecase in addition solution, e.g.:

* "If I read feed that has comics, the newest first ordering does not work well because I have to read from the bottom up"

is much more helpful than just writing:

* "Please add reverse ordering".


## Development

In general it's a good idea to **first create an issue where you explain why, what and how** you want to make a change **before writing any code**.

That way we can talk about the problem first and discuss the implementation (and of course help you with your code)

### Contact Us!

We usually hang out on **irc.freenode.net** in the **#owncloud-news** and **#owncloud-dev** chat room. Just ping [BernhardPosselt](https://github.com/BernhardPosselt/) or [cosenal](https://github.com/cosenal) or write us a mail directly. Mail addresses are listed on our GitHub profiles.

You can also send a mail to the [owncloud-devel mailing list](https://mailman.owncloud.org/mailman/listinfo/devel).

### Coding Style Guidelines

* Use 4 spaces for indention. Why spaces? Because it looks the same on every machine and on the web where you can't normally control the tab width.
* Place the open curly braces on the same line as the parameter block, e.g.:
  ```php
  if (condition) {
      // code
  } else {
      // code
  }
  ```

* Place a space before and after the parameter block for if, else, for, foreach, function
* Everything should be in pascalCase except classes which should be in CamelCase
* For linting JavaScript, a [jshint file](https://github.com/owncloud/news/blob/master/js/.jshintrc) is used that is run before compiling the JavaScript

### Project Structure
The project is structured in the following way:

* **admin/**: Admin related parts which hook up the News app in the admin area. The HTML is in **templates/admin.php** folder, the JavaScript is in **js/admin/Admin.js**, CSS in **css/admin.css**. Both CSS and JavaScript don't need to be recompiled unlike everything else. The controller that hooks up the template is located in **controller/admincontroller.php**. **admin/admin.php** is just there to wire up the controllers on the admin page which does not support the App Framework.

* **appinfo/**: Contains metadata related things, like names, versions, database structure, routes and the container that tells ownCloud how the app is assembled

* **bin/**: Git hooks and the custom Python updater

* **build/**: The folder where complete archives are saved when running make appstore

* **config/**: The code that reads the news config.ini file which is located in the data directory and can also be edited in the admin interface

* **controller/**: The stuff that reacts when a request comes in to a certain URL. URLs are defined in **appinfo/routes.php** and link to controllers.

* **cron/**: The code that is run when the ownCloud cron is being called.

* **css/**: All the CSS used in the project. Except the admin.css file everything needs to be minified first using Grunt, see the **js/README.md** file for more information

* **db/**: SQL queries and data objects. The database schema is stored in **appinfo/database.xml**

* **explore/**: Code that allows you to hook up custom explore pages and JSON configuration files what is displayed on the default explore page

* **fetcher/**: The code part that receives the feed url and uses picoFeed to fetch the content. Then things are mapped to Feed and Item objects that can be stored in the database.

* **hooks/**: Code to react to changes in ownCloud, e.g. what to do when a user is deleted

* **http/**: Custom response classes, e.g. to download a textfile

* **img/**: Pictures for thumbnails and icons

* **js/**: All the JavaScript files, libs and tests. Needs to be compiled using Grunt first, instructions are in the **js/README.md** file. The app is built using [Angular](https://angularjs.org/)

* **l10n/**: Automatically generated translation files. Don't edit them directly, instead go to Transifex which is a web interface that handles our translations, e.g. the German translation page is located here: [https://www.transifex.com/projects/p/owncloud/translate/#de/news/36802042](https://www.transifex.com/projects/p/owncloud/translate/#de/news/36802042). For other languages just replace the language code in the url.

* **plugin/**:

* **service/**: The most important part. Contains the app logic and validation, like what happens when you add or update a feed.

* **templates/**: All the HTML that is used in the app

* **upgrade/**: Migrations and hooks that are run when upgrading the app to a newer version

* **utility/**: Stuff that did not fit anywhere, mostly factories that are needed to deal with 3rdparty libraries like picoFeed, but also OPML exporters and updater classes

* **vendor/**: 3rdparty libraries that are managed using composer.
