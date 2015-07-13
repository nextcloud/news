Favicon fetcher
===============

Find and download the favicon
-----------------------------

```php
use PicoFeed\Reader\Favicon;

$favicon = new Favicon;

// The icon link is https://bits.wikimedia.org/favicon/wikipedia.ico
$icon_link = $favicon->find('https://en.wikipedia.org/');
$icon_content = $favicon->getContent();
```

PicoFeed will try first to find the favicon from the meta tags and fallback to the `favicon.ico` located in the website's root if nothing is found.

- `Favicon::find()` returns the favicon absolute url or an empty string if nothing is found.
- `Favicon::getContent()` returns the favicon file content (binary content)

When the HTML page is parsed, relative links and protocol relative links are converted to absolute url.

Download a known favicon
-----------------------
It's possible to download a known favicon using the second optional parameter of Favicon::find(). The link to the favicon can be a relative or protocol relative url as well, but it has to be relative to the specified website.

If the requested favicon could not be found, the HTML of the website is parsed instead, with the fallback to the `favicon.ico` located in the website's root.

```php
use PicoFeed\Reader\Favicon;

$favicon = new Favicon;

$icon_link = $favicon->find('https://en.wikipedia.org/','https://bits.wikimedia.org/favicon/wikipedia.ico');
$icon_content = $favicon->getContent();
```

Get Favicon file type
---------------------

It's possible to fetch the image type, this information come from the Content-Type HTTP header:

```php
$favicon = new Favicon;
$favicon->find('http://example.net/');

echo $favicon->getType();

// Will output the content type, by example "image/png"
```

Get the Favicon as Data URI
---------------------------

You can also get the whole image as Data URI.
It's useful if you want to store the icon in your database and avoid too many HTTP requests.

```php
$favicon = new Favicon;
$favicon->find('http://example.net/');

echo $favicon->getDataUri();

// Output something like that: data:image/png;base64,iVBORw0KGgoAAAANSUh.....
```

See: http://en.wikipedia.org/wiki/Data_URI_scheme

Check if a favicon link exists
------------------------------

```php
use PicoFeed\Reader\Favicon;

$favicon = new Favicon;

// Return true if the file exists
var_dump($favicon->exists('http://php.net/favicon.ico'));
```

Use personalized HTTP settings
------------------------------

Like other classes, the Favicon class support the Config object as constructor argument:

```php
use PicoFeed\Config\Config;
use PicoFeed\Reader\Favicon;

$config = new Config;
$config->setClientUserAgent('My RSS Reader');

$favicon = new Favicon($config);
$favicon->find('https://github.com');
```
