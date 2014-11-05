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
    $resource = $reader->download('https://linuxfr.org/news.atom');

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
Feed::url = http://linuxfr.org/news
Feed::date = 1415138079
Feed::language = en-US
Feed::description =
Feed::logo =
Feed::items = 15 items
----
Item::id = 38d8f48284fb03940cbb3aff9101089b81e44efb1281641bdd7c3e7e4bf3b0cd
Item::title = openSUSE 13.2 : nouvelle version du caméléon disponible !
Item::url = http://linuxfr.org/news/opensuse-13-2-nouvelle-version-du-cameleon-disponible
Item::date = 1415122640
Item::language = en-US
Item::author = Syvolc
Item::enclosure_url =
Item::enclosure_type =
Item::content = 18307 bytes
----
Item::id = d0ebddc90bfc3f109f9be00a3bb0b4a770af7a647cdc88454fe15d79168e0dea
Item::title = Fuzix OS, parce que les petites choses sont belles
Item::url = http://linuxfr.org/news/fuzix-os-parce-que-les-petites-choses-sont-belles
Item::date = 1415112167
Item::language = en-US
Item::author = Thomas DEBESSE
Item::enclosure_url =
Item::enclosure_type =
Item::content = 6104 bytes
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

TODO


Feed and item properties
------------------------

```php
// Feed object
$feed->getId();              // Unique feed id
$feed->getTitle();           // Feed title
$feed->getUrl();             // Website url
$feed->getDate();            // Feed last updated date
$feed->getLanguage();        // Feed language
$feed->getDescription();     // Feed description
$feed->getLogo();            // Feed logo (can be a large image, different from icon)
$feed->getItems();           // List of item objects

// Item object
$feed->items[0]->getId();                      // Item unique id (hash)
$feed->items[0]->getTitle();                   // Item title
$feed->items[0]->getUrl();                     // Item url
$feed->items[0]->getDate();                    // Item published date (timestamp)
$feed->items[0]->getLanguage();                // Item language
$feed->items[0]->getAuthor();                  // Item author
$feed->items[0]->getEnclosureUrl();            // Enclosure url
$feed->items[0]->getEnclosureType();           // Enclosure mime-type (audio/mp3, image/png...)
$feed->items[0]->getContent();                 // Item content (filtered or raw)
```
