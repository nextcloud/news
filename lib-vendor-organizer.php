#!/usr/bin/env php
<?php
$sourceDirectory = $argv[1];
$sourceDirectory = rtrim($sourceDirectory, "/") . "/";

if (!str_starts_with("/", $sourceDirectory)) {
    $sourceDirectory = getcwd() . "/" . $sourceDirectory;
}

$targetDirectory = $argv[2];
$targetDirectory = rtrim($targetDirectory, "/") . "/";
if (!file_exists($targetDirectory)) {
    mkdir($targetDirectory, 0777, true);
}

$stripNamespacePrefix = $argv[3] ?? "";
if ($stripNamespacePrefix) {
    printf(
        "Namespace Prefix to strip from destination dir is %s%s",
        $stripNamespacePrefix,
        PHP_EOL
    );
}

if (!file_exists($sourceDirectory) || !is_dir($sourceDirectory)) {
    print "Source directory not found";
    exit(1);
}
$organizationList = [];
foreach (scandir($sourceDirectory) as $file) {
    if (!is_dir($sourceDirectory . $file) || $file === "." || $file === "..") {
        continue;
    }
    $organizationList[] = $sourceDirectory . $file . "/";
}

$projectList = [];
foreach ($organizationList as $organizationDir) {
    foreach (scandir($organizationDir) as $file) {
        if (
            !is_dir($organizationDir . $file) ||
            $file === "." ||
            $file === ".."
        ) {
            continue;
        }
        $projectList[] = $organizationDir . $file . "/";
    }
}

foreach ($projectList as $projectDir) {
    if (!file_exists($projectDir . "composer.json")) {
        continue;
    }
    $projectInfo = json_decode(
        file_get_contents($projectDir . "composer.json"),
        true
    );
    if (isset($projectInfo["autoload"]["psr-4"])) {
        moveByPSR4(
            $projectInfo,
            $projectDir,
            $stripNamespacePrefix,
            $targetDirectory
        );
        continue;
    }
    if (isset($projectInfo["autoload"]["classmap"])) {
        moveByClassMap(
            $projectInfo,
            $projectDir,
            $stripNamespacePrefix,
            $targetDirectory
        );
        continue;
    }
    if (isset($projectInfo["autoload"]["files"])) {
        moveByFiles(
            $projectInfo,
            $projectDir,
            $stripNamespacePrefix,
            $targetDirectory
        );
        continue;
    }
    printf("No supported autoload configuration in %s" . PHP_EOL, $projectDir);
    exit(2);
}

function moveByFiles(
    array $projectInfo,
    string $projectDir,
    string $stripNamespacePrefix,
    string $targetDirectory
): void {
    foreach ($projectInfo["autoload"]["files"] as $codeFilePath) {
        $targetFileName = basename($codeFilePath);
        $destination = $targetDirectory . $targetFileName;
        if (file_exists($destination)) {
            unlink($destination);
        }
        if (!rename($projectDir . $codeFilePath, $destination)) {
            printf(
                "Failed to move %s to %s" . PHP_EOL,
                $projectDir . $codeFilePath,
                $destination
            );
            exit(5);
        }
        printf('Transformed files: %s' . PHP_EOL, $codeFilePath);
    }
}

function moveByClassMap(
    array $projectInfo,
    string $projectDir,
    string $stripNamespacePrefix,
    string $targetDirectory
): void {
    foreach ($projectInfo["autoload"]["classmap"] as $codeFilePath) {
        //FIXME: can be a directory
        $targetFileName = str_replace("/", "_", $codeFilePath);
        $destination = $targetDirectory . $targetFileName;
        if (file_exists($destination)) {
            unlink($destination);
        }
        if (!rename($projectDir . $codeFilePath, $destination)) {
            printf(
                "Failed to move %s to %s" . PHP_EOL,
                $projectDir . $codeFilePath,
                $destination
            );
            exit(4);
        }
        printf('Transformed classpath: %s (from %s)' . PHP_EOL, $codeFilePath, $projectDir);
    }
}

function moveByPSR4(
    array $projectInfo,
    string $projectDir,
    string $stripNamespacePrefix,
    string $targetDirectory
): void {
    foreach ($projectInfo["autoload"]["psr-4"] as $namespace => $codeDir) {
        if (
            $stripNamespacePrefix !== "" &&
            strpos($namespace, $stripNamespacePrefix) === 0
        ) {
            $namespace = str_replace($stripNamespacePrefix, "", $namespace);
        }
        $destination = $targetDirectory . str_replace("\\", "/", $namespace);
        if (file_exists($destination)) {
            rmdir_recursive($destination);
        }
        if(!mkdir($destination, 0777, true)) {
            printf(
                "Failed to create %s" . PHP_EOL,
                $destination
            );
            exit(5);
        }
        if (!rename($projectDir . $codeDir, $destination)) {
            printf(
                "Failed to move %s to %s" . PHP_EOL,
                $projectDir . $codeDir,
                $destination
            );
            exit(3);
        }
        printf('Transformed namespace: %s' . PHP_EOL, $namespace);
    }
}

function rmdir_recursive($dir)
{
    foreach (scandir($dir) as $file) {
        if ("." === $file || ".." === $file) {
            continue;
        }
        if (is_dir("$dir/$file")) {
            rmdir_recursive("$dir/$file");
        } else {
            unlink("$dir/$file");
        }
    }
    rmdir($dir);
}
