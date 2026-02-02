<?php

namespace NeonWebId\SimpleVisitorLogs\VisitorLogAdminMenu\Pages;

final class IPManager
{
    /**
     * Render halaman IP Manager.
     */
    public function render(): void
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'visitor_logs';

        // Query untuk mendapatkan ringkasan statistik per IP Address
        $query = "
            SELECT 
                ip_address, 
                country,
                asn,
                COUNT(*) as hits, 
                MAX(created_at) as last_hit 
            FROM $table_name 
            GROUP BY ip_address, country, asn
            ORDER BY hits DESC
            LIMIT 100
        ";

        $results = $wpdb->get_results($query);

        ?>
        <div class="wrap svl-admin-wrap">
        <h1 class="wp-heading-inline">IP Manager</h1>
        <p class="description">Analisis trafik berdasarkan Alamat IP pengunjung, lokasi negara, dan organisasi penyedia layanan.</p>
        <hr class="wp-header-end">
        <div class="svl-table-responsive" style="margin-top: 20px;">
            <table class="wp-list-table widefat fixed striped table-view-list">
                <thead>
                <tr>
                    <th style="width: 180px;">IP Address</th>
                    <th style="width: 150px;">Negara</th>
                    <th>Organisasi / ASN</th>
                    <th style="width: 100px;">Hits</th>
                    <th style="width: 180px;">Kunjungan Terakhir</th>
                    <th style="width: 100px;">Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($results)): foreach ($results as $row): ?>
                    <tr>
                        <td class="svl-col-ip">
                            <strong><?php echo esc_html($row->ip_address); ?></strong>
                        </td>
                        <td>
                            <span class="svl-country-label">
                                <?php echo esc_html($row->country); ?>
                            </span>
                        </td>
                        <td class="svl-col-asn">
                            <small><?php echo esc_html($row->asn); ?></small>
                        </td>
                        <td>
                            <span class="svl-hits-count"><?php echo number_format($row->hits); ?></span>
                        </td>
                        <td class="svl-last-hit">
                            <strong><?php echo date_i18n(get_option('date_format'), strtotime($row->last_hit)); ?></strong><br>
                            <small><?php echo date_i18n(get_option('time_format'), strtotime($row->last_hit)); ?></small>
                        </td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=svl-visitor-logs&ip_address=' . urlencode($row->ip_address)); ?>"
                               class="button button-small">
                                Lihat Log
                            </a>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr>
                        <td colspan="6">Belum ada data IP yang tercatat.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <style>
            .svl-admin-wrap { margin-top: 20px; }
            .svl-table-responsive { background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; box-shadow: 0 1px 1px rgba(0,0,0,.04); }

            .svl-col-ip strong { color: #2271b1; font-family: monospace; font-size: 14px; }

            .svl-country-label {
                display: inline-block;
                padding: 2px 6px;
                background: #f6f7f7;
                border: 1px solid #dcdcde;
                border-radius: 3px;
                font-size: 12px;
            }

            .svl-col-asn small { color: #646970; line-height: 1.2; display: block; }

            .svl-hits-count {
                font-weight: 700;
                color: #d63638;
                font-size: 14px;
            }

            .svl-last-hit strong { color: #50575e; }
            .svl-last-hit small { color: #8c8f94; }

            .wp-list-table th { padding: 12px 10px; font-weight: 600; }
            .wp-list-table td { padding: 10px; vertical-align: middle; }
        </style>
        </div>
        <?php
    }
}
