Exceptions
==========

All exceptions inherits from the standard `Exception` class.

### Library Exceptions

- `PicoFeed\PicoFeedException`: Base class exception for the library

### Client Exceptions

- `PicoFeed\Client\ClientException`: Base exception class for the Client class
- `PicoFeed\Client\InvalidCertificateException`: Invalid SSL certificate
- `PicoFeed\Client\InvalidUrlException`: Malformed URL, page not found (404), unable to establish a connection
- `PicoFeed\Client\MaxRedirectException`: Maximum of HTTP redirections reached
- `PicoFeed\Client\MaxSizeException`: The response size exceeds to maximum allowed
- `PicoFeed\Client\TimeoutException`: Connection timeout

### Parser Exceptions

- `PicoFeed\Parser\ParserException`: Base exception class for the Parser class
- `PicoFeed\Parser\MalformedXmlException`: XML Parser error

### Reader Exceptions

- `PicoFeed\Reader\ReaderException`: Base exception class for the Reader
- `PicoFeed\Reader\SubscriptionNotFoundException`: Unable to find a feed for the given website
- `PicoFeed\Reader\UnsupportedFeedFormatException`: Unable to detect the feed format
