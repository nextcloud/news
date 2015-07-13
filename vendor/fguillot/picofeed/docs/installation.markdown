Installation
============

Versions
--------

- Development version: master
- Stable version: use the last tag

Installation with Composer
--------------------------

```bash
composer require fguillot/picofeed @stable
```

And download the code:

```bash
composer install # or update
```

Usage example with the Composer autoloader:

```php
<?php

require 'vendor/autoload.php';

use PicoFeed\Reader\Reader;

try {

    $reader = new Reader;
    $resource = $reader->download('http://linuxfr.org/news.atom');

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
