<?php

namespace NeonWebId\SimpleVisitorLogs\VisitorLogAdminMenu;

use NeonWebId\SimpleVisitorLogs\VisitorLogAdminMenu\Pages\ASNManager;
use NeonWebId\SimpleVisitorLogs\VisitorLogAdminMenu\Pages\ExcludeUserAgents;
use NeonWebId\SimpleVisitorLogs\VisitorLogAdminMenu\Pages\VisitorLogs;

final class VisitorLogAdminMenu
{
    private VisitorLogs $visitorLogs;
    private ASNManager $asnManager;

    public function __construct()
    {
        $this->visitorLogs       = new VisitorLogs();
        $this->asnManager        = new ASNManager();
        add_action('admin_menu', [$this, 'register']);
    }

    public static function init(): void
    {
        new self();
    }

    public function register(): void
    {
        // Parent menu: Visitor Logs
        add_menu_page(
            'Visitor Logs',
            'Visitor Logs',
            'manage_options',
            'svl-visitor-logs',
            [$this->visitorLogs, 'render'],
            'dashicons-visibility',
            58
        );

        // Submenu: Visitor Logs (default)
        add_submenu_page(
            'svl-visitor-logs',
            'Visitor Logs',
            'Visitor Logs',
            'manage_options',
            'svl-visitor-logs',
            [$this->visitorLogs, 'render']
        );

        // Submenu: ASN Manager
        add_submenu_page(
            'svl-visitor-logs',
            'ASN Manager',
            'ASN Manager',
            'manage_options',
            'svl-asn-manager',
            [$this->asnManager, 'render']
        );

    }
}
