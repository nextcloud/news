Installation
============

Versions
--------

- Development version: branch master
- Available versions:
    - v0.1.0 (stable)
    - v0.0.2
    - v0.0.1

Installation with Composer
--------------------------

Configure your `composer.json`:

```json
{
    "require": {
        "fguillot/picofeed": "0.1.0"
    }
}
```

Or simply:

```bash
composer require fguillot/picofeed:0.1.0
```

And download the code:

```bash
composer install # or update
```

Usage example with the Composer autoloading:

```php
<?php

require 'vendor/autoload.php';

use PicoFeed\Reader\Reader;

try {

    $reader = new Reader;
    $resource = $reader->download('https://linuxfr.org/news.atom');

    $parser = $reader->getParser(
        $resource->getUrl(),
        $resource->getContent(),
        $resource->getEncoding()
    );

    $feed = $parser->execute();

    echo $feed;
}
catch (Exception $e) {
    // Do something...
}
```
