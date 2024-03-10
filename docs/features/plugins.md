# Plugins

Plugins were created to keep the app maintainable while still making it possible to easily implement additional functionality.

There are essentially three different use cases for plugins:

* Creating or extending server-side functionality, e.g. creating additional REST API endpoints
* Offering article actions such as share via Twitter or E-Mail
* Dropping in additional CSS or JavaScript

## The Basics

Whatever plugin you want to create, you first need to create a basic structure. A plugin is basically  just an app, so you can take advantage of the full [Nextcloud app API](https://docs.nextcloud.org/server/latest/developer_manual/app/index.html). [Take a look at the developer docs](https://docs.nextcloud.com/server/latest/developer_manual/app_development/index.html) or [dig into the tutorial](https://docs.nextcloud.com/server/latest/developer_manual/app_development/tutorial.html).

However, if you just want to start slow, the full process is described below.

First create a skeleton app using the [web interface](https://apps.nextcloud.com/developer/apps/generate)

The application name affects the name and namespace of your plugin and only one app can exist using the same name. Choose wisely. This will become the directory name in the Nextcloud `apps/` directory

* **newsplugin/**
  * **appinfo/**
    * **app.php**
    * **info.xml**

**Note**: You must license your app under the [AGPL 3 or later](https://www.gnu.org/licenses/agpl-3.0.en.html) to comply with the News app's license. Don't forget to add the license as plain text file if you want to distribute your app!

Then we want to make sure that our code is only run if the News app is enabled. To do that put the following PHP code into the **newsplugin/lib/AppInfo/Application.php** file:

```php
<?php
declare(strict_types=1);

namespace OCA\NewsBookmarkPlugin\AppInfo;

use OCP\AppFramework\App;
use OCP\Util;
use OCP\App as Test;

class Application extends App {
 public const APP_ID = 'newsbookmarkplugin';

 public function __construct() {
  parent::__construct(self::APP_ID);


        // your code here
 }
}

```

## Server-Side Plugin

A Server-Side plugin is a plugin that uses the same infrastructure as the News app for its own purposes. An example would be a plugin that makes the starred entries of a user available via an interface or a bookmark app that also shows starred articles as bookmarks.

It's very easy to interface with the News app. Because all Classes are registered in the **news/app/application.php** it takes almost no effort to use the same infrastructure.

**Note**: Keep in mind that these classes are essentially private which means they might break if the News app changes. There is no real public API so use at your own risk ;)

Since you don't want to extend the app but use its resources, its advised that you don't inherit from the **Application** class but rather include it in your own container in **newsplugin/appinfo/application.php**:

```php
<?php
namespace OCA\NewsPlugin\AppInfo;

use OCP\AppFramework\App;
use OCA\News\AppInfo\Application as News;

class Application extends App {

    public function __construct (array $urlParams=[]) {
        parent::__construct('newsplugin', $urlParams);

        $container = $this->getContainer();

        $container->registerService('NewsContainer', function($c) {
            $app = new News();
            return $app->getContainer();
        });

        $container->registerService(OCA\News\Service\FeedService::class, function($c) {
            // use the feed service from the news app, you can use all
            // defined classes but its recommended that you stick to the
            // mapper and service classes since they are less likely to change
            return $c->query('NewsContainer')->query(OCA\News\Service\FeedService::class);
        });
    }

}
```

Using automatic container assembly you can then use it from your code by simply adding the type to your constructors.

# TODO: Update the following

If your plugin integrates with another Nextcloud app, make sure to also require it be installed. If you depend on the Bookmarks app for instance use:

```php
<?php
namespace OCA\MyNewsPlugin\AppInfo;
use OCP\App;

if (App::isEnabled('news') && App::isEnabled('bookmarks')) {
    // your code here
}
```

Now you are ready to enable the app. Head over to the apps section and choose the **Not enabled** section. Your app should be listed under the name **Example News Plugin** (or whatever name you set in the **info.xml**).

With the basics set up, you can now choose how to progress further. In our case we just want to add some additional CSS and JavaScript, so we are going to create a client-side plugin.

## Client-Side Plugin

A client-side plugin is adding additional JavaScript and/or CSS to the News app. Remember the **app.php**? Open it and place the following contents inside:

```php
<?php
namespace OCA\MyNewsPlugin\AppInfo;
use OCP\App;

if (App::isEnabled('news') && class_exists('OCA\News\Plugin\Client\Plugin')) {
    \OCA\News\Plugin\Client\Plugin::registerScript('newsplugin', 'script');
    \OCA\News\Plugin\Client\Plugin::registerStyle('newsplugin', 'style');
}
```

This will tell the News app to load the following files after its own JavaScript and CSS files have been included:

* **newsplugin/js/script.js**
* **newspluing/css/style.css**

### Adding Basic JavaScript Functionality

You can basically add any JavaScript you want. If you want to add a new article action, this is a bit more complicated because it's hard to hook into Angular from the outside. Therefore, the News app provides an API which makes creating additional article actions a breeze.

A basic article action looks like this:

```js
News.addArticleAction(function($actionsElement, article) {
    // your code here
});
```

The **addArticleAction** method expects a function with the following parameters:

* **$actionsElement**: The DOM element wrapped in jQuery where your plugin should be appended to
* **article**: The current article's data (readonly!). The article object has the following properties:
  * **id**: the article ID in the News database
  * **url**: the article URL it points to
  * **title**: the article title
  * **author**: the article author
  * **pubDate**: the article published date, a Unix timestamp
  * **body**: the HTML content
  * **enclosureMime**: if an enclosure is added, this is the mime type
  * **enclosureLink**: this is the source of the enclosure
  * **mediaThumbnail**: if there is a media attached, this is its thumbnail
  * **mediaDescription**: if there is a media attached, this is its description
  * **feedId**: the feed ID it belongs to
  * **unread**: if the article is unread (bool)
  * **starred**: if the article is starred (bool)
  * **lastModified**: the last modified date

With that in mind, let's add the Twitter button. Open the JavaScript file at **newsplugin/js/script.js** and add the following contents:

```js
News.addArticleAction(function($actionsElement, article) {
    var $li = $('<li>')
        .addClass('article-plugin-twitter');
    var $button = $('<button>')
        .attr('title', t('newsplugin', 'Share on Twitter'));
    var text = 'Read this: ' + article.url;
    var url = 'https://twitter.com/intent/tweet?text=' + encodeURIComponent(text);

    $button.click(function (event) {
        window.open(url);
        window.opener = null; // prevent twitter being from able to access the DOM
        event.stopPropagation();  // prevent expanding in compact mode
    });

    $li.append($button);
    $actionsElement.append($li);
});
```

Great! Now the only thing left is to add some styles.

### Adding Styles

Now let's add some styles to our app. We want to style the button to look like a Twitter icon, so simply download an icon (e.g. [from Wikipedia](https://commons.wikimedia.org/wiki/File:Twitter_icon.png)) and place it at **newsplugin/img/twitter.png**.

Then open the **newspluing/css/style.css** file and add the following CSS:

```css
.article-plugin-twitter button {
    background-image: url('../img/twitter.png');
}
```

Reload the News app and click the three dots menu, sit back and enjoy :)
