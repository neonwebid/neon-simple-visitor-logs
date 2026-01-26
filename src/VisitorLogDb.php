<?php

namespace NeonWebId\SimpleVisitorLogs;

final class VisitorLogDb
{
    /* =========================
       CREATE TABLES
       ========================= */
    public static function create(): void
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Main visitor log table
        $log_table = $wpdb->prefix . 'visitor_logs';

        $sql_logs = "CREATE TABLE $log_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            ip_address varchar(100) NOT NULL,
            ip_long bigint(20) UNSIGNED DEFAULT 0 NOT NULL,
            country varchar(100) DEFAULT 'Unknown',
            asn varchar(255) DEFAULT 'Unknown',
            asn_number bigint(20) UNSIGNED DEFAULT 0,
            path text NOT NULL,
            referrer text,
            user_agent varchar(255) NOT NULL,
            PRIMARY KEY (id),
            KEY idx_created_at (created_at),
            KEY idx_ip_address (ip_address),
            KEY idx_ip_long (ip_long),
            KEY idx_country (country),
            KEY idx_asn (asn(100)),
            KEY idx_asn_number (asn_number),
            KEY idx_user_agent (user_agent)
        ) $charset_collate;";

        dbDelta($sql_logs);
    }

    /* =========================
       TRUNCATE LOG TABLE
       ========================= */
    public static function truncate(): void
    {
        global $wpdb;
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}visitor_logs");
    }

    /* =========================
       DROP ALL TABLES
       ========================= */
    public static function drop(): void
    {
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}visitor_logs");
    }
}
