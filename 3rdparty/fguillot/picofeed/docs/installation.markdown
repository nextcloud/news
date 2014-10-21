Installation
============

Installation with Composer and AutoLoading
------------------------------------------

Configure your `composer.json`:

```json
{
    "require": {
        "fguillot/picofeed": "dev-master"
    }
}
```

And download the code:

```bash
php composer.phar install # or update
```

Usage example with the Composer autoloading:

```php
<?php

require 'vendor/autoload.php';

use PicoFeed\Reader;

$reader = new Reader;
$reader->download('http://linuxfr.org/news.atom');

$parser = $reader->getParser();

if ($parser !== false) {

    $feed = $parser->execute();

    if ($feed !== false) {
        echo $feed->title;
    }
}
```

Installation without AutoLoading
--------------------------------

If you don't want to use an autoloader, you can include the file `PicoFeed.php`.

Example:

```php
<?php

require 'path/to/PicoFeed.php';

use PicoFeed\Reader;

$reader = new Reader;
$reader->download('http://linuxfr.org/news.atom');

$parser = $reader->getParser();

if ($parser !== false) {

    $feed = $parser->execute();

    if ($feed !== false) {
        echo $feed->title;
    }
}
```
