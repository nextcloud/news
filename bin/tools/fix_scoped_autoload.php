#!/usr/bin/env php
<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__, 2);
$vendorDir = $projectRoot . '/vendor';
$scopedDir = $projectRoot . '/lib/Vendor';
$autoloadFilesPath = $vendorDir . '/composer/autoload_files.php';
$autoloadStaticPath = $vendorDir . '/composer/autoload_static.php';

if (!is_file($autoloadFilesPath) || !is_file($autoloadStaticPath) || !is_dir($scopedDir)) {
    exit(0);
}

$autoloadFilesContent = file_get_contents($autoloadFilesPath);
$autoloadStaticContent = file_get_contents($autoloadStaticPath);
if ($autoloadFilesContent === false || $autoloadStaticContent === false) {
    fwrite(STDERR, 'Unable to read Composer autoload files' . PHP_EOL);
    exit(1);
}

preg_match_all('/\\$vendorDir \. \'\/([^\']+)\'/', $autoloadFilesContent, $matches);
$fileEntries = $matches[1] ?? [];

$pathMap = [];
foreach ($fileEntries as $relativeVendorPath) {
    $candidate = mapVendorPathToScopedPath($relativeVendorPath, $scopedDir);
    if ($candidate === null) {
        // Fallback: some packages have their files placed at the root of lib/Vendor
        // (e.g. symfony/deprecation-contracts → function.php, ralouphie/getallheaders
        // → getallheaders.php). Check for a root-level match only — never match files
        // in subdirectories, to avoid cross-package collisions such as phpstan's
        // bootstrap.php matching a polyfill bootstrap.php deeper in the tree.
        $basename = basename($relativeVendorPath);
        if (is_file($scopedDir . '/' . $basename)) {
            $candidate = $basename;
        }
    }

    if ($candidate === null) {
        continue;
    }

    $pathMap[$relativeVendorPath] = $candidate;
}

if ($pathMap === []) {
    exit(0);
}

foreach ($pathMap as $vendorRelative => $scopedRelative) {
    $autoloadFilesContent = str_replace(
        "\$vendorDir . '/" . $vendorRelative . "'",
        "\$baseDir . '/lib/Vendor/" . $scopedRelative . "'",
        $autoloadFilesContent
    );
    $autoloadStaticContent = str_replace(
        "__DIR__ . '/..' . '/" . $vendorRelative . "'",
        "__DIR__ . '/../..' . '/lib/Vendor/" . $scopedRelative . "'",
        $autoloadStaticContent
    );
}

if (file_put_contents($autoloadFilesPath, $autoloadFilesContent) === false
    || file_put_contents($autoloadStaticPath, $autoloadStaticContent) === false) {
    fwrite(STDERR, 'Unable to update Composer autoload files' . PHP_EOL);
    exit(1);
}

function mapVendorPathToScopedPath(string $relativeVendorPath, string $scopedDir): ?string
{
    $parts = explode('/', $relativeVendorPath);
    if (count($parts) < 3) {
        return null;
    }

    $packagePath = $parts[0] . '/' . $parts[1];
    $composerPath = dirname($scopedDir, 2) . '/vendor/' . $packagePath . '/composer.json';
    $tail = implode('/', array_slice($parts, 2));
    if (is_file($composerPath)) {
        $packageInfo = json_decode(file_get_contents($composerPath), true);
        foreach ($packageInfo['autoload']['psr-4'] ?? [] as $namespace => $codeDir) {
            $codeDir = trim($codeDir, '/');
            if ($codeDir !== '' && !str_starts_with($tail, $codeDir . '/')) {
                continue;
            }

            $relativePath = $codeDir === '' ? $tail : substr($tail, strlen($codeDir) + 1);
            $namespacePath = str_replace('\\', '/', trim($namespace, '\\'));
            $candidate = $namespacePath . '/' . $relativePath;
            if (is_file($scopedDir . '/' . $candidate)) {
                return $candidate;
            }
        }
    }

    $vendor = normalizePackageSegment($parts[0]);
    $packageParts = array_map('normalizePackageSegment', explode('-', $parts[1]));
    $candidate = $vendor . '/' . implode('/', $packageParts) . '/' . $tail;

    return is_file($scopedDir . '/' . $candidate) ? $candidate : null;
}

function normalizePackageSegment(string $segment): string
{
    $words = preg_split('/[^a-zA-Z0-9]+/', $segment) ?: [];
    $words = array_filter($words, static fn (string $word): bool => $word !== '');
    return implode('', array_map(static fn (string $word): string => ucfirst(strtolower($word)), $words));
}
