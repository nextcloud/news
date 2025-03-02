<?php

declare(strict_types=1);

use Isolated\Symfony\Component\Finder\Finder;

$composerJsonRaw = file_get_contents(__DIR__ . '/composer.json');
$composerJson = \json_decode($composerJsonRaw, true);
$finderConfig = [];
foreach ($composerJson['require'] as $name => $version) {
    if ($name === 'php' || $name === 'bamarni/composer-bin-plugin' || str_starts_with($name, 'ext-')) {
        continue;
    }
    $finderConfig[] = Finder::create()
        ->path($name)
        ->files()
        ->exclude(["test", "composer", "bin"])
        ->notName("autoload.php")
        ->in("vendor/");
}


return [
    "prefix" => "OCA\\News\\Vendor",
    'exclude-namespaces' => [ 'Psr\\Log\\' ],
    "finders" => $finderConfig,
];
