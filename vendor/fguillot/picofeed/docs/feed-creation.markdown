Feed creation
=============

PicoFeed can also generate Atom and RSS feeds.

Generate RSS 2.0 feed
----------------------

```php
use PicoFeed\Syndication\Rss20;

$writer = new Rss20();
$writer->title = 'My site';
$writer->site_url = 'http://boo/';
$writer->feed_url = 'http://boo/feed.atom';
$writer->author = array(
    'name' => 'Me',
    'url' => 'http://me',
    'email' => 'me@here'
);

$writer->items[] = array(
    'title' => 'My article 1',
    'updated' => strtotime('-2 days'),
    'url' => 'http://foo/bar',
    'summary' => 'Super summary',
    'content' => '<p>content</p>'
);

$writer->items[] = array(
    'title' => 'My article 2',
    'updated' => strtotime('-1 day'),
    'url' => 'http://foo/bar2',
    'summary' => 'Super summary 2',
    'content' => '<p>content 2 &nbsp; &copy; 2015</p>',
    'author' => array(
        'name' => 'Me too',
    )
);

$writer->items[] = array(
    'title' => 'My article 3',
    'url' => 'http://foo/bar3'
);

echo $writer->execute();
```

Generate Atom feed
------------------

```php
use PicoFeed\Syndication\Atom;

$writer = new Atom();
$writer->title = 'My site';
$writer->site_url = 'http://boo/';
$writer->feed_url = 'http://boo/feed.atom';
$writer->author = array(
    'name' => 'Me',
    'url' => 'http://me',
    'email' => 'me@here'
);

$writer->items[] = array(
    'title' => 'My article 1',
    'updated' => strtotime('-2 days'),
    'url' => 'http://foo/bar',
    'summary' => 'Super summary',
    'content' => '<p>content</p>'
);

echo $writer->execute();
```
