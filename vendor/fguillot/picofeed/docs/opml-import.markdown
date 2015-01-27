Import OPML file
================

Importing a list of subscriptions is pretty straightforward:

```php
use PicoFeed\Serialization\Import;

$opml = file_get_contents('mySubscriptions.opml');
$import = new Import($opml);
$entries = $import->execute();

if ($entries !== false) {
    print_r($entries);
}

```

The method `execute()` return `false` if there is a parsing error.
