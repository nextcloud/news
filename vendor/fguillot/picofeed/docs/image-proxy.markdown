Image Proxy
===========

To prevent mixed content warnings on SSL pages served from your RSS reader you might want to use an assets proxy.

Images url will be rewritten to be downloaded through the proxy.

Example:

```html
<img src="http://example.org/image.png"/>
```

Can be rewritten like that:

```html
<img src="http://myproxy.example.org/?url=http%3A%2F%2Fexample.org%2Fimage.png"/>
```

Currently this feature is only compatible with images.

There is several open source SSL image proxy available like [Camo](https://github.com/atmos/camo).
You can also write your own proxy.

Usage
-----

There two different ways to use this feature, define a proxy url or a callback.

### Define a proxy url

A proxy url must be defined with a placeholder `%s`.
The placeholder will be replaced by the image source urlencoded.

```php
$config = new Config;
$config->setFilterImageProxyUrl('http://myproxy.example.org/?url=%s');
```

Will rewrite the image source like that:

```html
<img src="http://myproxy.example.org/?url=http%3A%2F%2Fexample.org%2Fimage.png"/>
```

### Define a callback

Your callback will be called each time an image url need to be rewritten.
The first argument is the original image url and your function must returns the new image url.

Here an example if your proxy need a shared secret key:

```php
$config = new Config;

$config->setFilterImageProxyCallback(function ($image_url) {
    $key = hash_hmac('sha1', $image_url, 'secret');
    return 'https://mypublicproxy/'.$key.'/'.urlencode($image_url);
});
```

Will generate an image url like that:

```html
<img src="https://mypublicproxy/4924964043f3119b3cf2b07b1922d491bcc20092/http%3A%2F%2Ffoo%2Fimage.png"/>
```
