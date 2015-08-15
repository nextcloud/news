<?php

// Autoloader for loading the library when composer is not available
spl_autoload_register(function ($class) {
    if (strncmp($class, $ns = 'Riimu\\Kit\\PathJoin\\', strlen($ns)) === 0) {
        $file = substr_replace($class, '\\', 0, strlen($ns)) . '.php';
        $path = __DIR__ . str_replace('\\', DIRECTORY_SEPARATOR, $file);
        if (file_exists($path)) {
            require $path;
        }
    }
});
