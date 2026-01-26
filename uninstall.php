<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

use NeonWebId\SimpleVisitorLogs\VisitorLogDb;

// pastikan autoload tersedia
require_once __DIR__ . '/src/autoload.php';

VisitorLogDb::drop();
