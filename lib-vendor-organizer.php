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
    
    // Special handling for HTMLPurifier main class file
    if (strpos($projectDir, 'htmlpurifier') !== false) {
        $htmlPurifierFile = $projectDir . 'library/HTMLPurifier.php';
        $htmlPurifierComposerFile = $projectDir . 'library/HTMLPurifier.composer.php';
        
        if (file_exists($htmlPurifierFile)) {
            $destination = $targetDirectory . 'HTMLPurifier.php';
            if (file_exists($destination)) {
                unlink($destination);
            }
            if (!copy($htmlPurifierFile, $destination)) {
                printf(
                    "Failed to copy HTMLPurifier.php to %s" . PHP_EOL,
                    $destination
                );
                exit(6);
            }
            printf('Copied HTMLPurifier main class file' . PHP_EOL);
        }
        
        if (file_exists($htmlPurifierComposerFile)) {
            $destination = $targetDirectory . 'HTMLPurifier.composer.php';
            if (file_exists($destination)) {
                unlink($destination);
            }
            if (!copy($htmlPurifierComposerFile, $destination)) {
                printf(
                    "Failed to copy HTMLPurifier.composer.php to %s" . PHP_EOL,
                    $destination
                );
                exit(7);
            }
            printf('Copied HTMLPurifier composer file' . PHP_EOL);
        }
    }
    
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
    // Skip packages without supported autoload configuration
    printf("Skipping %s (no supported autoload configuration)" . PHP_EOL, $projectDir);
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
        $sourcePath = $projectDir . $codeFilePath;
        
        // Handle both files and directories
        if (is_dir($sourcePath)) {
            // For directories, move them as-is
            $targetFileName = str_replace("/", "_", rtrim($codeFilePath, '/'));
            $destination = $targetDirectory . $targetFileName;
            
            if (file_exists($destination)) {
                rmdir_recursive($destination);
            }
            if (!rename($sourcePath, $destination)) {
                printf(
                    "Failed to move directory %s to %s" . PHP_EOL,
                    $sourcePath,
                    $destination
                );
                exit(4);
            }
            printf('Transformed classpath directory: %s (from %s)' . PHP_EOL, $codeFilePath, $projectDir);
        } else {
            // For files, move them as before
            $targetFileName = str_replace("/", "_", $codeFilePath);
            $destination = $targetDirectory . $targetFileName;
            
            if (file_exists($destination)) {
                unlink($destination);
            }
            if (!rename($sourcePath, $destination)) {
                printf(
                    "Failed to move %s to %s" . PHP_EOL,
                    $sourcePath,
                    $destination
                );
                exit(4);
            }
            printf('Transformed classpath file: %s (from %s)' . PHP_EOL, $codeFilePath, $projectDir);
        }
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

// Update composer.json to include HTMLPurifier classmap
$composerJsonPath = getcwd() . '/composer.json';
if (file_exists($composerJsonPath)) {
    $composerData = json_decode(file_get_contents($composerJsonPath), true);
    
    // Remove classmap if it exists (causes conflicts with HTMLPurifier)
    if (isset($composerData['autoload']['classmap'])) {
        unset($composerData['autoload']['classmap']);
        printf('Removed classmap from composer.json (not needed)' . PHP_EOL);
    }
    
    // Add files for HTMLPurifier autoloader
    if (!isset($composerData['autoload']['files'])) {
        $composerData['autoload']['files'] = [];
    }
    
    $autoloadFile = 'lib/Vendor/HTMLPurifier.autoload.php';
    if (!in_array($autoloadFile, $composerData['autoload']['files'])) {
        $composerData['autoload']['files'][] = $autoloadFile;
        printf('Added HTMLPurifier autoloader to composer.json' . PHP_EOL);
    }
    
    // Write back to composer.json
    file_put_contents(
        $composerJsonPath,
        json_encode($composerData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL
    );
}

// Create a custom autoloader for HTMLPurifier classes
$autoloaderPath = $targetDirectory . 'HTMLPurifier.autoload.php';
$autoloaderContent = <<<'PHP'
<?php
/**
 * Custom autoloader for HTMLPurifier classes in OCA\News\Vendor namespace
 */
spl_autoload_register(function ($class) {
    $originalClass = $class;
    
    // Handle both namespaced and non-namespaced HTMLPurifier classes
    // If class starts with HTMLPurifier without namespace, add the namespace
    if (strpos($class, 'HTMLPurifier') === 0 && strpos($class, '\\') === false) {
        $class = 'OCA\\News\\Vendor\\' . $class;
    }
    
    // Only handle classes in OCA\News\Vendor namespace that start with HTMLPurifier
    if (strpos($class, 'OCA\\News\\Vendor\\HTMLPurifier') === 0) {
        // Don't reload if already loaded
        if (class_exists($class, false)) {
            return true;
        }
        
        $className = substr($class, strlen('OCA\\News\\Vendor\\'));
        
        // HTMLPurifier_ClassName -> HTMLPurifier/ClassName.php
        // But HTMLPurifier itself (no underscore) -> HTMLPurifier.php (root level)
        if ($className === 'HTMLPurifier') {
            $file = __DIR__ . '/HTMLPurifier.php';
        } else {
            // Replace all underscores to get path: HTMLPurifier_Config -> HTMLPurifier/Config.php
            $file = __DIR__ . '/' . str_replace('_', '/', $className) . '.php';
        }
        
        if (file_exists($file)) {
            include_once $file;
            
            // If we loaded a class in response to a non-namespaced request, create an alias
            if (strpos($originalClass, '\\') === false && class_exists($class, false)) {
                if (!class_exists($originalClass, false)) {
                    class_alias($class, $originalClass, false);
                }
            }
            
            return true;
        }
    }
    return false;
});
PHP;

file_put_contents($autoloaderPath, $autoloaderContent);
printf('Created HTMLPurifier autoloader' . PHP_EOL);

