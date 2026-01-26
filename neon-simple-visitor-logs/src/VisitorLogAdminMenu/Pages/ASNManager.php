<?php

namespace NeonWebId\SimpleVisitorLogs\VisitorLogAdminMenu\Pages;

final class ASNManager
{
    public function render(): void
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'visitor_logs';

        // Query to get statistical summary per ASN
        // Grouping by ASN name and ASN number
        $query = "
            SELECT 
                asn, 
                asn_number, 
                COUNT(*) as hits, 
                MAX(created_at) as last_hit 
            FROM $table_name 
            WHERE asn != 'Unknown'
            GROUP BY asn, asn_number 
            ORDER BY hits DESC
        ";

        $results = $wpdb->get_results($query);

        echo '<div class="wrap svl-admin-wrap">';
        echo '<h1 class="wp-heading-inline">ASN Manager</h1>';
        echo '<p class="description">Traffic analysis based on Organization (ISP/Hosting) and AS Number.</p>';
        echo '<hr class="wp-header-end">';

        ?>
        <div class="svl-table-responsive" style="margin-top: 20px;">
            <table class="wp-list-table widefat fixed striped table-view-list">
                <thead>
                <tr>
                    <th>Organization</th>
                    <th style="width: 120px;">AS Number</th>
                    <th style="width: 100px;">Hits</th>
                    <th style="width: 180px;">Last Hit</th>
                    <th style="width: 100px;">Action</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($results): foreach ($results as $row): ?>
                    <tr>
                        <td class="svl-col-org">
                            <strong><?php echo esc_html($row->asn ?: 'Unknown'); ?></strong>
                        </td>
                        <td>
                            <span class="svl-asn-badge">AS<?php echo esc_html($row->asn_number ?: '???'); ?></span>
                        </td>
                        <td>
                            <span class="svl-hits-count"><?php echo number_format($row->hits); ?></span>
                        </td>
                        <td>
                            <div class="svl-last-hit">
                                <strong><?php echo date('H:i:s', strtotime($row->last_hit)); ?></strong><br>
                                <small><?php echo date('Y-m-d', strtotime($row->last_hit)); ?></small>
                            </div>
                        </td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=svl-visitor-logs&f_asn=' . urlencode($row->asn)); ?>"
                               class="button button-small">
                                View Logs
                            </a>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr>
                        <td colspan="5">No ASN data recorded yet.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <style>
            .svl-admin-wrap { margin-top: 20px; }
            .svl-table-responsive { background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; box-shadow: 0 1px 1px rgba(0,0,0,.04); }

            .svl-col-org strong { color: #1d2327; font-size: 14px; }

            .svl-asn-badge {
                background: #f0f0f1;
                border: 1px solid #dcdcde;
                padding: 2px 8px;
                border-radius: 4px;
                font-family: monospace;
                font-size: 12px;
                color: #2271b1;
            }

            .svl-hits-count {
                font-weight: 700;
                color: #d63638;
                font-size: 14px;
            }

            .svl-last-hit strong { color: #50575e; }
            .svl-last-hit small { color: #8c8f94; }

            .wp-list-table th { padding: 12px 10px; font-weight: 700; }
            .wp-list-table td { vertical-align: middle; padding: 10px; }

            tr:hover .svl-asn-badge { border-color: #2271b1; background-color: #f6f7f7; }
        </style>
        <?php

        echo '</div>';
    }
}