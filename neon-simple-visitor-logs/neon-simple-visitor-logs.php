<?php
/**
 * Plugin Name: Neon Simple Visitor Logs
 * Plugin URI: https://neon.web.id/
 * Description: Lightweight visitor logger untuk mencatat IP, ASN, negara, path, referrer, dan User-Agent dengan kontrol exclude berbasis User-Agent dan manajemen ASN.
 * Version: 1.0.0
 * Author: NeonWebId
 * Author URI: https://neon.web.id/
 * Requires at least: 6.4
 * Requires PHP: 8.2
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: neon-simple-visitor-logs
 */

use NeonWebId\SimpleVisitorLogs\VisitorLogAdminMenu\VisitorLogAdminMenu;
use NeonWebId\SimpleVisitorLogs\VisitorLogDb;
use NeonWebId\SimpleVisitorLogs\VisitorLogTracker;

if (!defined('ABSPATH')) {
    exit;
}


require_once __DIR__.'/src/autoload.php';

register_activation_hook(__FILE__, [VisitorLogDb::class, 'create']);

add_action('plugins_loaded', [VisitorLogAdminMenu::class, 'init']);
add_action('plugins_loaded', [VisitorLogTracker::class, 'init']);

