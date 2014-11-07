Favicon fetcher
===============

Find and download the favicon
-----------------------------

```php

use PicoFeed\Favicon;

$favicon = new Favicon;

// The icon link is https://bits.wikimedia.org/favicon/wikipedia.ico
$icon_link = $favicon->find('https://en.wikipedia.org/');
$icon_content = $favicon->getContent();

```

PicoFeed will try first to find the favicon from the meta tags and fallback to the `favicon.ico` located in the website's root if nothing is found.

- `Favicon::find()` returns the favicon absolute url or an empty string if nothing is found.
- `Favicon::getContent()` returns the favicon file content (binary content)

When the HTML page is parsed, relative links and protocol relative links are converted to absolute url.

Check if a favicon link exists
------------------------------

```php

use PicoFeed\Favicon;

$favicon = new Favicon;

// Return true if the file exists
var_dump($favicon->exists('http://php.net/favicon.ico'));

```

Use personalized HTTP settings
------------------------------

Like other classes, the Favicon class support the Config object as constructor argument:

```php

use PicoFeed\Config;
use PicoFeed\Favicon;

$config = new Config;
$config->setClientUserAgent('My RSS Reader');

$favicon = new Favicon($config);
$favicon->find('https://github.com');

```