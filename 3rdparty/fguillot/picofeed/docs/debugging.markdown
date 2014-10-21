Debugging
=========

Get log messages
----------------

PicoFeed log in memory the execution flow, if a feed doesn't work correctly it's easy to see what is wrong.

```php
print_r(PicoFeed\Logging::getMessages());
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

Remove messages
---------------

All messages are stored in memory, if you need to clear them just call the method `Logging::deleteMessages()`:

```php
PicoFeed\Logging::deleteMessages();
```
