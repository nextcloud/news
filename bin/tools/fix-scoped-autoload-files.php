#!/usr/bin/env php
<?php

$autoloadFile = __DIR__ . '/../../vendor/composer/autoload_files.php';

if (!file_exists($autoloadFile)) {
    exit(0);
}

$autoload = file_get_contents($autoloadFile);
if ($autoload === false) {
    fwrite(STDERR, "Failed to read autoload files list\n");
    exit(1);
}

$updatedAutoload = preg_replace_callback(
    '#\$vendorDir \. \'/symfony/polyfill-([a-z0-9-]+)/bootstrap\.php\'#',
    static function (array $matches): string {
        $suffix = $matches[1];
        $segments = array_map(
            static fn (string $segment): string => ucfirst($segment),
            explode('-', $suffix)
        );
        $scopedPath = __DIR__ . '/../../lib/Vendor/Symfony/Polyfill/' . implode('/', $segments) . '/bootstrap.php';

        if (!file_exists($scopedPath)) {
            return $matches[0];
        }

        return "\$baseDir . '/lib/Vendor/Symfony/Polyfill/" . implode('/', $segments) . "/bootstrap.php'";
    },
    $autoload
);

if (!is_string($updatedAutoload) || $updatedAutoload === $autoload) {
    exit(0);
}

if (file_put_contents($autoloadFile, $updatedAutoload) === false) {
    fwrite(STDERR, "Failed to write updated autoload files list\n");
    exit(1);
}
