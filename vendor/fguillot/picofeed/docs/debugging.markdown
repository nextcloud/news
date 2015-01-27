Debugging
=========

Logging
-------

PicoFeed can log **in memory** the execution flow, if a feed doesn't work correctly it's easy to see what is wrong.

### Enable/disable logging

The logging is **disabled by default** to avoid unnecessary memory usage.

Enable logging:

```php
use PicoFeed\Logging\Logger;

Logger::enable();

// or change the flag value

Logger::$enable = true;
```

### Reading messages

```php
use PicoFeed\Logging\Logger;

// All messages are stored inside an Array
print_r(Logger::getMessages());
```

You will got an output like that:

```php
Array
(
    [0] => Fetch URL: http://petitcodeur.fr/feed.xml
    [1] => Etag:
    [2] => Last-Modified:
    [3] => cURL total time: 0.711378
    [4] => cURL dns lookup time: 0.001064
    [5] => cURL connect time: 0.100733
    [6] => cURL speed download: 74825
    [7] => HTTP status code: 200
    [8] => HTTP headers: Set-Cookie => start=R2701971637; path=/; expires=Sat, 06-Jul-2013 05:16:33 GMT
    [9] => HTTP headers: Date => Sat, 06 Jul 2013 03:55:52 GMT
    [10] => HTTP headers: Content-Type => application/xml
    [11] => HTTP headers: Content-Length => 53229
    [12] => HTTP headers: Connection => close
    [13] => HTTP headers: Server => Apache
    [14] => HTTP headers: Last-Modified => Tue, 02 Jul 2013 03:26:02 GMT
    [15] => HTTP headers: ETag => "393e79c-cfed-4e07ee78b2680"
    [16] => HTTP headers: Accept-Ranges => bytes
    ....
)
```

### Remove messages

All messages are stored in memory, if you need to clear them just call the method `Logger::deleteMessages()`:

```php
Logger::deleteMessages();
```

Command line utility
====================

PicoFeed provides a basic command line tool to debug feeds quickly.
The tool is located in the root directory project.

### Usage

```bash
$ ./picofeed
Usage:
./picofeed feed <feed-url>                   # Parse a feed a dump the ouput on stdout
./picofeed debug <feed-url>                  # Display all logging messages for a feed
./picofeed item <feed-url> <item-id>         # Fetch only one item
./picofeed nofilter <feed-url> <item-id>     # Fetch an item but with no content filtering
```

### Example

```bash
$ ./picofeed debug https://linuxfr.org/
Exception thrown ===> "Invalid SSL certificate"
Array
(
    [0] => [2014-11-08 14:04:14] PicoFeed\Client\Curl Fetch URL: https://linuxfr.org/
    [1] => [2014-11-08 14:04:14] PicoFeed\Client\Curl Etag provided:
    [2] => [2014-11-08 14:04:14] PicoFeed\Client\Curl Last-Modified provided:
    [3] => [2014-11-08 14:04:16] PicoFeed\Client\Curl cURL total time: 1.850634
    [4] => [2014-11-08 14:04:16] PicoFeed\Client\Curl cURL dns lookup time: 0.00093
    [5] => [2014-11-08 14:04:16] PicoFeed\Client\Curl cURL connect time: 0.115213
    [6] => [2014-11-08 14:04:16] PicoFeed\Client\Curl cURL speed download: 0
    [7] => [2014-11-08 14:04:16] PicoFeed\Client\Curl cURL effective url: https://linuxfr.org/
    [8] => [2014-11-08 14:04:16] PicoFeed\Client\Curl cURL error: SSL certificate problem: Invalid certificate chain
)
```
