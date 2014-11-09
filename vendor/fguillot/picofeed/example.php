<?php

require 'vendor/autoload.php';

use PicoFeed\Reader\Reader;
use PicoFeed\PicoFeedException;

try {

    // Fetch from your database the previous values of the Etag and LastModified headers
    $etag = '...';
    $last_modified = '...';

    $reader = new Reader;

    // Provide those values to the download method
    $resource = $reader->download('http://linuxfr.org/news.atom', $last_modified, $etag);

    if ($resource->isModified()) {

        $parser = $reader->getParser(
            $resource->getUrl(),
            $resource->getContent(),
            $resource->getEncoding()
        );

        $feed = $parser->execute();

        // Save your feed in your database
        // ...

        // Store the Etag and the LastModified headers in your database
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
