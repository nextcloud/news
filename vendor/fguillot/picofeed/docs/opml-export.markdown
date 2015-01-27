OPML export
===========

Example with no categories
--------------------------

```php
use PicoFeed\Serialization\Export;

$feeds = array(
    array(
        'title' => 'Site title',
        'description' => 'Optional description',
        'site_url' => 'http://petitcodeur.fr/',
        'site_feed' => 'http://petitcodeur.fr/feed.xml'
    )
);

$export = new Export($feeds);
$opml = $export->execute();

echo $opml; // XML content
```

Example with categories
-----------------------

```php
use PicoFeed\Serialization\Export;

$feeds = array(
    'my category' => array(
        array(
            'title' => 'Site title',
            'description' => 'Optional description',
            'site_url' => 'http://petitcodeur.fr/',
            'site_feed' => 'http://petitcodeur.fr/feed.xml'
        )
    )
);

$export = new Export($feeds);
$opml = $export->execute();

echo $opml; // XML content
```