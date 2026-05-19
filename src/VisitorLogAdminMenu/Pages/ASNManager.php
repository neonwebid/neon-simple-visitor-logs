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
                MAX(hosting) as hosting,
                MAX(mobile) as mobile,
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
        <div class="svl-table-scroll-wrapper" style="margin-top: 20px;">
            <table class="wp-list-table widefat fixed striped table-view-list">
                <thead>
                <tr>
                    <th>Organization</th>
                    <th style="width: 120px;">Type</th>
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
                            <?php 
                            $type = 'ISP';
                            if (!empty($row->hosting)) {
                                $type = 'Hosting';
                            } elseif (!empty($row->mobile)) {
                                $type = 'Selular';
                            }
                            ?>
                            <span class="svl-type-badge svl-type-<?php echo esc_attr(strtolower($type)); ?>">
                                <?php echo esc_html($type); ?>
                            </span>
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
                        <td colspan="6">No ASN data recorded yet.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <style>
            .svl-admin-wrap { margin-top: 20px; max-width: 100%; overflow-x: hidden; }
            .svl-table-scroll-wrapper {
                background: #fff;
                border: 1px solid #ccd0d4;
                border-radius: 4px;
                overflow-x: auto;
                box-shadow: 0 1px 1px rgba(0,0,0,.04);
            }
            .wp-list-table { border: none !important; border-collapse: collapse; min-width: 800px; }

            .svl-col-org strong { color: #1d2327; font-size: 14px; }

            .svl-type-badge {
                display: inline-block;
                padding: 2px 8px;
                border-radius: 12px;
                font-size: 11px;
                font-weight: 600;
                text-transform: uppercase;
                text-align: center;
            }
            .svl-type-hosting {
                background: #f3e8ff;
                color: #6b21a8;
                border: 1px solid #e9d5ff;
            }
            .svl-type-isp {
                background: #dbeafe;
                color: #1e40af;
                border: 1px solid #bfdbfe;
            }
            .svl-type-selular {
                background: #fef3c7;
                color: #92400e;
                border: 1px solid #fde68a;
            }

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