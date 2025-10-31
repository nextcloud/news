<?php

declare(strict_types=1);

use Isolated\Symfony\Component\Finder\Finder;

$composerJsonRaw = file_get_contents(__DIR__ . '/composer.json');
$composerJson = \json_decode($composerJsonRaw, true);
$dependencies = [];
$finderConfig = [];

$dependenciesListRaw = shell_exec('composer show --format=json --tree --no-dev');
if($dependenciesListRaw === null || $dependenciesListRaw === false) {
    error_log('Cannot determine dependencies');
    exit(1);
}
$dependenciesList = \json_decode($dependenciesListRaw, true);
if(!is_array($dependenciesList) || !isset($dependenciesList['installed'])) {
    error_log('Invalid composer show output');
    exit(2);
}

addDependencies($dependenciesList['installed'], $dependencies);

function isIgnoreable(array $depInfo, array $dependencies): bool {
    return ($depInfo['name'] === 'php'
        || $depInfo['name'] === 'bamarni/composer-bin-plugin'
        || str_starts_with($depInfo['name'], 'ext-')
        || isset($dependencies[$depInfo['name']])
    );
}

function addDependencies(array $requires, array &$dependencies): void {
    foreach($requires as $depInfo) {
        if (isIgnoreable($depInfo, $dependencies)) {
            continue;
        }
        if (!isset($dependencies[$depInfo['name']])) {
            $dependencies[$depInfo['name']] = 1;
        }
        if (isset($depInfo['requires'])) {
            addDependencies($depInfo['requires'], $dependencies);
        }
    }
}

foreach (array_keys($dependencies) as $name) {
    fwrite(STDERR, 'Adding ' . $name . PHP_EOL);
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
