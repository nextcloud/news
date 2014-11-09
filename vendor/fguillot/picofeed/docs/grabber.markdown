Web scraper
===========

The web scraper is useful for feeds that display only a summary of articles, the scraper can download and parse the full content from the original website.

How the content grabber works?
------------------------------

1. Try with rules first (XPath queries) for the domain name (see `PicoFeed\Rules\`)
2. Try to find the text content by using common attributes for class and id
3. Finally, if nothing is found, the feed content is displayed

**The best results are obtained with XPath rules file.**

How to use the content scraper?
-------------------------------

Before parsing all items, just call the method `$parser->enableContentGrabber()`:

```php
use PicoFeed\Reader\Reader;
use PicoFeed\PicoFeedException;

try {

    $reader = new Reader;

    // Return a resource
    $resource = $reader->download('http://www.egscomics.com/rss.php');

    // Return the right parser instance according to the feed format
    $parser = $reader->getParser(
        $resource->getUrl(),
        $resource->getContent(),
        $resource->getEncoding()
    );

    // Enable content grabber before parsing items
    $parser->enableContentGrabber();

    // Return a Feed object
    $feed = $parser->execute();
}
catch (PicoFeedException $e) {
    // Do Something...
}
```

When the content scraper is enabled, everything will be slower.
**For each item a new HTTP request is made** and the HTML downloaded is parsed with XML/XPath.

Configuration
-------------

### Enable content grabber for items

- Method name: `enableContentGrabber()`
- Default value: false (content grabber is disabled by default)
- Argument value: none

```php
$parser->enableContentGrabber();
```

### Ignore item urls for the content grabber

- Method name: `setGrabberIgnoreUrls()`
- Default value: empty (fetch all item urls)
- Argument value: array (list of item urls to ignore)

```php
$parser->setGrabberIgnoreUrls(['http://foo', 'http://bar']);
```

How to write a grabber rules file?
----------------------------------

Add a PHP file to the directory `PicoFeed\Rules`, the filename must be the same as the domain name:

Example with the BBC website, `www.bbc.co.uk.php`:

```php
<?php
return array(
    'test_url' => 'http://www.bbc.co.uk/news/world-middle-east-23911833',
    'body' => array(
        '//div[@class="story-body"]',
    ),
    'strip' => array(
        '//script',
        '//form',
        '//style',
        '//*[@class="story-date"]',
        '//*[@class="story-header"]',
        '//*[@class="story-related"]',
        '//*[contains(@class, "byline")]',
        '//*[contains(@class, "story-feature")]',
        '//*[@id="video-carousel-container"]',
        '//*[@id="also-related-links"]',
        '//*[contains(@class, "share") or contains(@class, "hidden") or contains(@class, "hyper")]',
    )
);
```

Actually, only `body`, `strip` and `test_url` are supported.

Don't forget to send a pull request or a ticket to share your contribution with everybody,

List of content grabber rules
-----------------------------

Rules are stored inside the directory [lib/PicoFeed/Rules](https://github.com/fguillot/picoFeed/tree/master/lib/PicoFeed/Rules)
