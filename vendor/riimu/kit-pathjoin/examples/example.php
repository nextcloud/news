<?php

require '../src/autoload.php';
use Riimu\Kit\PathJoin\Path;

// Both of the following will output 'foo/bar' on Unix and 'foo\bar' on Windows
echo Path::normalize('foo/bar') . PHP_EOL;
echo Path::join('foo', 'bar') . PHP_EOL;

// The join method accepts multiple arguments or a single array
echo Path::join('foo', 'bar', 'baz') . PHP_EOL;   // outputs 'foo/bar/baz'
echo Path::join(['foo', 'bar', 'baz']) . PHP_EOL; // outputs 'foo/bar/baz'

// The '.' and '..' directory references will be resolved in the paths
echo Path::normalize('foo/./bar/../baz') . PHP_EOL;     // outputs 'foo/baz'
echo Path::join(['foo/./', 'bar', '../baz']) . PHP_EOL; // outputs 'foo/baz'

// Only the first path can denote an absolute path in the join method
echo Path::join('/foo', '/bar/baz') . PHP_EOL;     // outputs '/foo/bar/baz'
echo Path::join('foo', '/bar') . PHP_EOL;          // outputs 'foo/bar'
echo Path::join('foo', '../bar', 'baz') . PHP_EOL; // outputs 'bar/baz'
echo Path::join('', '/bar', 'baz') . PHP_EOL;      // outputs 'bar/baz'

// Relative paths can start with a '..', but absolute paths cannot
echo Path::join('/foo', '../../bar', 'baz') . PHP_EOL; // outputs '/bar/baz'
echo Path::join('foo', '../../bar', 'baz') . PHP_EOL;  // outputs '../bar/baz'

// Empty path will result in a '.'
echo Path::normalize('foo/..') . PHP_EOL;
echo Path::join('foo', 'bar', '../..') . PHP_EOL;

echo Path::normalize('/foo/bar') . PHP_EOL;        // outputs 'C:\foo\Bar'
echo Path::normalize('D:/foo/bar') . PHP_EOL;      // outputs 'D:\foo\Bar'
echo Path::normalize('/foo/bar', false) . PHP_EOL; // outputs '\foo\Bar'
