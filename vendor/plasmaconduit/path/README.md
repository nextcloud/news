Path
====

[![Build Status](https://travis-ci.org/JosephMoniz/php-path.png?branch=master)](undefined)

Simple and care free file path concatenation and simplification.

```php
<?php
use PlasmaConduit\Path;

Path::join("wat", "lol");              // -> wat/lol
Path::join("/a", "///b");              // -> /a/b
Path::join("/a", "b", "c", "..", "d"); // -> /a/b/d

Path::normalize("/a/b/c/../d");       // -> /a/b/d
Path::normalize("/a/b/c/../../d");    // -> /a/d
Path::normalize("/b/wat//");          // -> /b/wat/
Path::normalize("/b///wat/");         // -> /b/wat/
Path::normalize("");                  // -> .
Path::normalize("/");                 // -> /
```