<?php

namespace Riimu\Kit\PathJoin;

/**
 * Cross-platform library for normalizing and joining file system paths.
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2014, Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class Path
{
    /**
     * Normalizes the provided file system path.
     *
     * Normalizing file system paths means that all forward and backward
     * slashes in the path will be replaced with the system directory separator
     * and multiple directory separators will be condensed into one.
     * Additionally, all `.` and `..` directory references will be resolved in
     * the returned path.
     *
     * Note that if the normalized path is not an absolute path, the resulting
     * path may begin with `..` directory references if it is not possible to
     * resolve them simply by using string handling. You should also note that
     * if the resulting path would result in an empty string, this method will
     * return `.` instead.
     *
     * If the `$prependDrive` option is enabled, the normalized path will be
     * prepended with the drive name on Windows platforms using the current
     * working directory, if the path is an absolute path that does not include
     * a drive name.
     *
     * @param string $path File system path to normalize
     * @param bool $prependDrive True to prepend drive name to absolute paths
     * @return string The normalizes file system path
     */
    public static function normalize($path, $prependDrive = true)
    {
        $path = self::join((string) $path);

        if ($path[0] === DIRECTORY_SEPARATOR && $prependDrive) {
            return strstr(getcwd(), DIRECTORY_SEPARATOR, true) . $path;
        }

        return $path;
    }

    /**
     * Joins the provided file systems paths together and normalizes the result.
     *
     * The paths can be provided either as multiple arguments to this method
     * or as an array. The paths will be joined using the system directory
     * separator and the result will be normalized similar to the normalization
     * method (the drive letter will not be prepended however).
     *
     * Note that unless the first path in the list is an absolute path, the
     * entire resulting path will be treated as a relative path.
     *
     * @param string[]|string $paths File system paths to join
     * @return string The joined file system paths
     */
    public static function join($paths)
    {
        $paths = array_map('strval', is_array($paths) ? $paths : func_get_args());
        $parts = self::getParts($paths);

        $absolute = self::isAbsolute($paths[0]);
        $root = $absolute ? array_shift($parts) . DIRECTORY_SEPARATOR : '';
        $parts = self::resolve($parts, $absolute);

        return self::buildPath($root, $parts);
    }

    /**
     * Builds the final path from the root and path parts.
     * @param string $root Root path
     * @param string[] $parts The path parts
     * @return string The final built path
     */
    private static function buildPath($root, array $parts)
    {
        if ($parts === []) {
            return $root === '' ? '.' : $root;
        }

        return $root . implode(DIRECTORY_SEPARATOR, $parts);
    }

    /**
     * Merges the paths and returns the individual parts.
     * @param string[] $paths Array of paths
     * @return string[] Parts in the paths merged into a single array
     */
    private static function getParts(array $paths)
    {
        if ($paths === []) {
            throw new \InvalidArgumentException('You must provide at least one path');
        }

        return array_map('trim', explode('/', str_replace('\\', '/', implode('/', $paths))));
    }

    /**
     * Tells if the path is an absolute path.
     * @param string $path The file system path to test
     * @return bool True if the path is an absolute path, false if not
     */
    private static function isAbsolute($path)
    {
        $path = trim($path);

        if ($path === '') {
            return false;
        }

        $length = strcspn($path, '/\\');

        return $length === 0 || $path[$length - 1] === ':';
    }

    /**
     * Resolves parent directory references and removes redundant entries.
     * @param string[] $parts List of parts in the the path
     * @param bool $absolute Whether the path is an absolute path or not
     * @return string[] Resolved list of parts in the path
     */
    private static function resolve(array $parts, $absolute)
    {
        $resolved = [];

        foreach ($parts as $path) {
            if ($path === '..') {
                self::resolveParent($resolved, $absolute);
            } elseif (self::isValidPath($path)) {
                $resolved[] = $path;
            }
        }

        return $resolved;
    }

    /**
     * Tells if the part of the path is valid and not empty.
     * @param string $path Part of the path to check for redundancy
     * @return bool True if the path is valid and not empty, false if not
     * @throws \InvalidArgumentException If the path contains invalid characters
     */
    private static function isValidPath($path)
    {
        if (strpos($path, ':') !== false) {
            throw new \InvalidArgumentException('Invalid path character ":"');
        }

        return $path !== '' && $path !== '.';
    }

    /**
     * Resolves the relative parent directory for the path.
     * @param string[] $parts Path parts to modify
     * @param bool $absolute True if dealing with absolute path, false if not
     * @return string|null The removed parent or null if nothing was removed
     */
    private static function resolveParent(& $parts, $absolute)
    {
        if ($absolute || !in_array(end($parts), ['..', false], true)) {
            return array_pop($parts);
        }

        $parts[] = '..';
    }
}
