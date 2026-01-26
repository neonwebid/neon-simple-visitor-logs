<?php

defined('ABSPATH') or die;

spl_autoload_register(function ($class) {

    // Namespace prefix plugin
    $prefix = 'NeonWebId\\SimpleVisitorLogs\\';

    // Base directory untuk namespace ini
    $base_dir = __DIR__ .'/';

    // Jika class bukan dari namespace kita, skip
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    // Ambil relative class name
    $relative_class = substr($class, strlen($prefix));

    // Convert namespace ke path file
    $file = $base_dir.str_replace('\\', '/', $relative_class).'.php';

    // Load file jika ada
    if (file_exists($file)) {
        require $file;
    }
});