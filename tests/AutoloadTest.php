<?php

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = dirname(__DIR__) . '/backend/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

use App\Core\Database;

try {
    // We won't actually call getInstance() because it might fail if DB is not up
    // but we can check if the class exists.
    if (class_exists('App\Core\Database')) {
        echo "PASS: Autoloader works for App\Core\Database\n";
    } else {
        echo "FAIL: Class App\Core\Database not found\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "FAIL: Exception: " . $e->getMessage() . "\n";
    exit(1);
}
