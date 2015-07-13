Feed parsing
============

Parsing a subscription
----------------------

```php
use PicoFeed\Reader\Reader;
use PicoFeed\PicoFeedException;

try {

    $reader = new Reader;

    // Return a resource
    $resource = $reader->download('http://linuxfr.org/news.atom');

    // Return the right parser instance according to the feed format
    $parser = $reader->getParser(
        $resource->getUrl(),
        $resource->getContent(),
        $resource->getEncoding()
    );

    // Return a Feed object
    $feed = $parser->execute();

    // Print the feed properties with the magic method __toString()
    echo $feed;
}
catch (PicoFeedException $e) {
    // Do Something...
}
```

- The Reader class is the entry point for feed reading
- The method `download()` fetch the remote content and return a resource, an instance of `PicoFeed\Client\Client`
- The method `getParser()` returns a Parser instance according to the feed format Atom, Rss 2.0...
- The parser itself returns a `Feed` object that contains feed and item properties

Output:

```bash
Feed::id = tag:linuxfr.org,2005:/news
Feed::title = LinuxFr.org : les dépêches
Feed::feed_url = http://linuxfr.org/news.atom
Feed::site_url = http://linuxfr.org/news
Feed::language = en-US
Feed::description =
Feed::logo =
Feed::date = Thu, 26 Feb 15 09:33:08 +0100
Feed::isRTL() = false
Feed::items = 15 items
----
Item::id = 56198c98ae852d21c369bfb5ffbc2ad13db2f3227236dde3e21ca1a9eb943faf
Item::title = Les brevets logiciels : un frein à l'innovation et la recherche (un nouvel exemple aux États-Unis)
Item::url = http://linuxfr.org/news/les-brevets-logiciels-un-frein-a-l-innovation-et-la-recherche-un-nouvel-exemple-aux-etats-unis
Item::language = en-US
Item::author = alenvers
Item::enclosure_url =
Item::enclosure_type =
Item::date = Thu, 26 Feb 15 09:33:08 +0100
Item::isRTL() = false
Item::content = 6452 bytes
....
```

Get the list of available subscriptions for a website
-----------------------------------------------------

The example below will returns all available subscriptions for the website:

```php
use PicoFeed\Reader\Reader;

try {

    $reader = new Reader;
    $resource = $reader->download('http://www.cnn.com');

    $feeds = $reader->find(
        $resource->getUrl(),
        $resource->getContent()
    );

    print_r($feeds);
}
catch (PicoFeedException $e) {
    // Do something...
}
```

Output:

```php
Array
(
    [0] => http://rss.cnn.com/rss/cnn_topstories.rss
    [1] => http://rss.cnn.com/rss/cnn_latest.rss
)
```

Feed discovery and parsing
--------------------------

This example will discover automatically the subscription and parse the feed:

```php
try {

    $reader = new Reader;
    $resource = $reader->discover('http://linuxfr.org');

    $parser = $reader->getParser(
        $resource->getUrl(),
        $resource->getContent(),
        $resource->getEncoding()
    );

    $feed = $parser->execute();
    echo $feed;
}
catch (PicoFeedException $e) {
}
```

HTTP caching
------------

PicoFeed supports HTTP caching to avoid unnecessary processing.

1. After the first download, save in your database the values of the Etag and LastModified HTTP headers
2. For the next requests, provide those values to the `download()` method and check if the feed was modified or not

Here an example:

```php
try {

    // Fetch from your database the previous values of the Etag and LastModified headers
    $etag = '...';
    $last_modified = '...';

    $reader = new Reader;

    // Provide those values to the download method
    $resource = $reader->download('http://linuxfr.org/news.atom', $last_modified, $etag);

    // Return true if the remote content has changed
    if ($resource->isModified()) {

        $parser = $reader->getParser(
            $resource->getUrl(),
            $resource->getContent(),
            $resource->getEncoding()
        );

        $feed = $parser->execute();

        // Save your feed in your database
        // ...

        // Store the Etag and the LastModified headers in your database for the next requests
        $etag = $resource->getEtag();
        $last_modified = $resource->getLastModified();

        // ...
    }
    else {

        echo 'Not modified, nothing to do!';
    }
}
catch (PicoFeedException $e) {
    // Do something...
}
```

HTTP basic auth
---------------
If a feed requires basic auth headers, you can pass them as parameters to the **download** method, e.g.:

```php
try {
    $reader = new Reader;

    $user = 'john';
    $password = 'doe';

    // Provide those values to the download method
    $resource = $reader->download('http://linuxfr.org/news.atom', '', '', $user, $password);

    // Return true if the remote content has changed
    if ($resource->isModified()) {

        $parser = $reader->getParser(
            $resource->getUrl(),
            $resource->getContent(),
            $resource->getEncoding()
        );

        $feed = $parser->execute();

        // Save your feed in your database
        // ...

    }
    else {

        echo 'Not modified, nothing to do!';
    }
}
catch (PicoFeedException $e) {
    // Do something...
}
```

Custom regex filters
--------------------
In case you want modify the content with a simple regex, you can create a rule file named after the domain of the feed's link attribute. For the feed pointing to **http://www.twogag.com/** the file is stored under **Rules/twogag.com.php**

For filtering, only the array with the key **filter** will be considered. The first level key is a preg_match regex that will match the sub url, e.g. to only match a feed whose link attribute points to **twogag.com/test**, the regex could look like **%/test.*%**. The second level array contains a list of search and replace strings, which will be passed to the preg\_replace function. The first string is the argument that should be matched, the second is the replacement.

To replace all occurences of links to smaller images for twogag, the following rule can be used:


```php
<?php
return array(
    'filter' => array(
        '%.*%' => array(
            "%http://www.twogag.com/comics-rss/([^.]+)\\.jpg%" =>
            "http://www.twogag.com/comics/$1.jpg"
        )
    )
);
```

Feed and item properties
------------------------

```php
// Feed object
$feed->getId();              // Unique feed id
$feed->getTitle();           // Feed title
$feed->getFeedUrl();         // Feed url
$feed->getSiteUrl();         // Website url
$feed->getDate();            // Feed last updated date (DateTime object)
$feed->getLanguage();        // Feed language
$feed->getDescription();     // Feed description
$feed->getLogo();            // Feed logo (can be a large image, different from icon)
$feed->getItems();           // List of item objects

// Item object
$feed->items[0]->getId();                      // Item unique id (hash)
$feed->items[0]->getTitle();                   // Item title
$feed->items[0]->getUrl();                     // Item url
$feed->items[0]->getDate();                    // Item published date (DateTime object)
$feed->items[0]->getLanguage();                // Item language
$feed->items[0]->getAuthor();                  // Item author
$feed->items[0]->getEnclosureUrl();            // Enclosure url
$feed->items[0]->getEnclosureType();           // Enclosure mime-type (audio/mp3, image/png...)
$feed->items[0]->getContent();                 // Item content (filtered or raw)
$feed->items[0]->isRTL();                      // Return true if the item language is Right-To-Left
```

Get raw XML tags/attributes or non standard tags for items
----------------------------------------------------------
The getTag function returns an array with all values of matching tags. If nothing can be found, an empty array is returned. In case of errors, the return value is false.

Get the original `guid` tag for RSS 2.0 feeds:

```php
$values = $feed->items[0]->getTag('guid');
print_r ($values);
```

Get a specific attribute value:

```php
$values = $feed->items[1]->getTag('category', 'term');
print_r ($values);
```

Get value of namespaced tag:

```php
if (array_key_exists('wfw', $feed->items[0]->namespaces)) {
    $values = $feed->items[1]->getTag('wfw:commentRss');
    print_r ($values);
}
```

Get attribute value of a namespaced tag:

```php
if (array_key_exists('media', $feed->items[0]->namespaces)) {
    $values = $feed->items[0]->getTag('media:content', 'url');
    print_r ($values);
}
```

Get the xml of the item (returns a SimpleXMLElement instance):

```php
$simplexml = $feed->items[0]->xml;
```

Get the list of namespaces:

```php
print_r($feed->items[0]->namespaces);
```

RTL language detection
----------------------

Use the method `Item::isRTL()` to test if an item is RTL or not:

```php
var_dump($item->isRTL()); // true or false
```

Known RTL languages are:

- Arabic (ar-**)
- Farsi (fa-**)
- Urdu (ur-**)
- Pashtu (ps-**)
- Syriac (syr-**)
- Divehi (dv-**)
- Hebrew (he-**)
- Yiddish (yi-**)
