<?php

namespace NeonWebId\SimpleVisitorLogs\VisitorLogAdminMenu\Pages;

use JetBrains\PhpStorm\NoReturn;use NeonWebId\SimpleVisitorLogs\VisitorLogDb;

final class VisitorLogs
{
    /**
     * Constructor untuk mendaftarkan hook.
     */
    public function __construct()
    {
        // Hook untuk menangani proses hapus log
        add_action('admin_post_svl_clear_logs', [$this, 'handle_clear_logs']);

        // Hook untuk AJAX Select2 (Hanya jika user login admin)
        add_action('wp_ajax_svl_get_ips', [$this, 'ajax_get_ips']);
        add_action('wp_ajax_svl_get_countries', [$this, 'ajax_get_countries']);
        add_action('wp_ajax_svl_get_asns', [$this, 'ajax_get_asns']);
    }

    /**
     * AJAX Handler untuk mendapatkan daftar IP unik
     */
    public function ajax_get_ips(): void
    {
        global $wpdb;
        $search = isset($_GET['q']) ? sanitize_text_field($_GET['q']) : '';
        $table = $wpdb->prefix . 'visitor_logs';

        $query = "SELECT DISTINCT ip_address FROM $table WHERE ip_address LIKE %s LIMIT 20";
        $results = $wpdb->get_col($wpdb->prepare($query, '%' . $wpdb->esc_like($search) . '%'));

        $data = array_map(function($ip) {
            return ['id' => $ip, 'text' => $ip];
        }, $results);

        wp_send_json($data);
    }

    /**
     * AJAX Handler untuk mendapatkan daftar Negara unik
     */
    public function ajax_get_countries(): void
    {
        global $wpdb;
        $search = isset($_GET['q']) ? sanitize_text_field($_GET['q']) : '';
        $table = $wpdb->prefix . 'visitor_logs';

        $query = "SELECT DISTINCT country FROM $table WHERE country LIKE %s LIMIT 20";
        $results = $wpdb->get_col($wpdb->prepare($query, '%' . $wpdb->esc_like($search) . '%'));

        $data = array_map(function($c) {
            return ['id' => $c, 'text' => $c];
        }, $results);

        wp_send_json($data);
    }

    /**
     * AJAX Handler untuk mendapatkan daftar ASN unik
     */
    public function ajax_get_asns(): void
    {
        global $wpdb;
        $search = isset($_GET['q']) ? sanitize_text_field($_GET['q']) : '';
        $table = $wpdb->prefix . 'visitor_logs';

        $query = "SELECT DISTINCT asn FROM $table WHERE asn LIKE %s LIMIT 20";
        $results = $wpdb->get_col($wpdb->prepare($query, '%' . $wpdb->esc_like($search) . '%'));

        $data = array_map(function($a) {
            return ['id' => $a, 'text' => $a];
        }, $results);

        wp_send_json($data);
    }

    /**
     * Method untuk menangani proses hapus semua log melalui admin_post.
     */
    #[NoReturn]
    public function handle_clear_logs(): void
    {
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'svl_clear_logs_action')) {
            wp_die('Action not authorized.');
        }

        if (!current_user_can('manage_options')) {
            wp_die('You do not have permission to perform this action.');
        }

        VisitorLogDb::truncate();

        wp_safe_redirect(add_query_arg(
            ['page' => 'svl-visitor-logs', 'svl_message' => 'cleared'],
            admin_url('admin.php')
        ));
        exit;
    }

    public function render(): void
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'visitor_logs';

        if (isset($_GET['svl_message']) && $_GET['svl_message'] === 'cleared') {
            echo '<div class="updated notice is-dismissible"><p>All visitor logs have been cleared successfully.</p></div>';
        }

        $per_page = 30;
        $page     = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $offset   = ($page - 1) * $per_page;

        $search    = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $f_ip      = isset($_GET['f_ip']) ? sanitize_text_field($_GET['f_ip']) : '';
        $f_country = isset($_GET['f_country']) ? sanitize_text_field($_GET['f_country']) : '';
        $f_asn     = isset($_GET['f_asn']) ? sanitize_text_field($_GET['f_asn']) : '';

        $orderby  = isset($_GET['orderby']) ? sanitize_sql_orderby($_GET['orderby']) : 'created_at';
        $order    = (isset($_GET['order']) && strtoupper($_GET['order']) === 'ASC') ? 'ASC' : 'DESC';

        $allowed_sort = [
            'time'    => 'created_at',
            'ip'      => 'ip_long',
            'country' => 'country',
            'asn'     => 'asn'
        ];
        $sort_column = $allowed_sort[$orderby] ?? 'created_at';

        $where = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $where[] = "(user_agent LIKE %s OR ip_address LIKE %s OR path LIKE %s)";
            $term = '%' . $wpdb->esc_like($search) . '%';
            array_push($params, $term, $term, $term);
        }
        if (!empty($f_ip)) {
            $where[] = "ip_address = %s";
            $params[] = $f_ip;
        }
        if (!empty($f_country)) {
            $where[] = "country = %s";
            $params[] = $f_country;
        }
        if (!empty($f_asn)) {
            $where[] = "asn = %s";
            $params[] = $f_asn;
        }

        $where_sql = count($params) > 0 ? $wpdb->prepare(implode(" AND ", $where), ...$params) : implode(" AND ", $where);

        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE $where_sql");
        $results     = $wpdb->get_results("SELECT * FROM $table_name WHERE $where_sql ORDER BY $sort_column $order LIMIT $offset, $per_page");
        $total_pages = ceil($total_items / $per_page);

        $this->enqueue_assets();
        ?>
        <div class="wrap svl-admin-wrap">
        <div class="svl-header-flex">
        <h1 class="wp-heading-inline">Visitor Logs</h1>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('Are you sure you want to delete all logs? This action cannot be undone.');">
            <input type="hidden" name="action" value="svl_clear_logs">
            <?php wp_nonce_field('svl_clear_logs_action'); ?>
            <button type="submit" class="button button-link-delete">Clear All Logs</button>
        </form>
        </div>
        <hr class="wp-header-end">

        <div class="svl-filter-container">
            <form method="get" action="">
                <input type="hidden" name="page" value="<?php echo esc_attr($_GET['page']); ?>">
                <?php if(isset($_GET['orderby'])): ?><input type="hidden" name="orderby" value="<?php echo esc_attr($_GET['orderby']); ?>"><?php endif; ?>
                <?php if(isset($_GET['order'])): ?><input type="hidden" name="order" value="<?php echo esc_attr($_GET['order']); ?>"><?php endif; ?>

                <div class="svl-filter-grid">
                    <div class="svl-filter-item">
                        <label>Search</label>
                        <input type="text" name="s" value="<?php echo esc_attr($search); ?>" placeholder="IP, Path, or UA...">
                    </div>

                    <div class="svl-filter-item">
                        <label>Filter IP</label>
                        <select name="f_ip" class="svl-select2-ajax" data-action="svl_get_ips">
                            <?php if($f_ip) {echo '<option value="'.esc_attr($f_ip).'" selected>'.esc_html($f_ip).'</option>';} ?>
                        </select>
                    </div>

                    <div class="svl-filter-item">
                        <label>Filter Country</label>
                        <select name="f_country" class="svl-select2-ajax" data-action="svl_get_countries">
                            <?php if($f_country) {echo '<option value="'.esc_attr($f_country).'" selected>'.esc_html($f_country).'</option>';} ?>
                        </select>
                    </div>

                    <div class="svl-filter-item">
                        <label>Filter ASN</label>
                        <select name="f_asn" class="svl-select2-ajax" data-action="svl_get_asns">
                            <?php if($f_asn) {echo '<option value="'.esc_attr($f_asn).'" selected>'.esc_html($f_asn).'</option>';} ?>
                        </select>
                    </div>

                    <div class="svl-filter-actions">
                        <button type="submit" class="button button-primary">Apply</button>
                        <a href="admin.php?page=<?php echo esc_attr($_GET['page']); ?>" class="button">Reset</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="svl-table-scroll-wrapper">
            <table class="wp-list-table widefat fixed striped table-view-list">
                <thead>
                <tr>
                    <th id="time" class="manage-column sortable <?php echo ($orderby === 'time') ? strtolower($order) : 'desc'; ?>" style="width: 110px;">
                        <a href="<?php echo $this->get_sort_url('time', $orderby, $order); ?>">
                            <span>Time</span><span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th id="ip" class="manage-column sortable <?php echo ($orderby === 'ip') ? strtolower($order) : 'desc'; ?>" style="width: 130px;">
                        <a href="<?php echo $this->get_sort_url('ip', $orderby, $order); ?>">
                            <span>IP Address</span><span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th id="country" class="manage-column sortable <?php echo ($orderby === 'country') ? strtolower($order) : 'desc'; ?>" style="width: 100px;">
                        <a href="<?php echo $this->get_sort_url('country', $orderby, $order); ?>">
                            <span>Country</span><span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th id="asn" class="manage-column sortable <?php echo ($orderby === 'asn') ? strtolower($order) : 'desc'; ?>" style="width: 180px;">
                        <a href="<?php echo $this->get_sort_url('asn', $orderby, $order); ?>">
                            <span>Organization (ASN)</span><span class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th style="min-width: 250px;">Request Info</th>
                    <th style="min-width: 250px;">User Agent</th>
                </tr>
                </thead>
                <tbody id="svl-log-body">
                <?php if ($results): foreach ($results as $log): ?>
                    <tr id="log-row-<?php echo $log->id; ?>">
                        <td class="svl-col-time">
                            <div class="svl-time-box">
                                <span class="time-main"><?php echo date('H:i:s', strtotime($log->created_at)); ?></span>
                                <span class="time-sub"><?php echo date('Y-m-d', strtotime($log->created_at)); ?></span>
                            </div>
                        </td>
                        <td>
                            <code class="svl-ip-code"><?php echo esc_html($log->ip_address); ?></code>
                        </td>
                        <td>
                            <span class="svl-badge-country">
                                <?php echo esc_html($log->country); ?>
                            </span>
                        </td>
                        <td class="svl-col-asn">
                            <span class="svl-asn-text"><?php echo esc_html($log->asn); ?></span>
                        </td>
                        <td class="svl-col-request">
                            <div class="svl-path"><strong>GET</strong> <code><?php echo esc_html($log->path); ?></code></div>
                            <?php if($log->referrer): ?>
                                <div class="svl-ref"><strong>Ref:</strong> <span><?php echo esc_html($log->referrer); ?></span></div>
                            <?php endif; ?>
                        </td>
                        <td class="svl-col-ua">
                            <div class="svl-ua-container">
                                <span class="svl-ua-text"><?php echo esc_html($log->user_agent); ?></span>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="6" style="text-align:center">No visitor logs found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <span class="displaying-num"><?php echo number_format($total_items); ?> items</span>
                <?php
                echo paginate_links([
                    'base'      => add_query_arg('paged', '%#%'),
                    'format'    => '',
                    'prev_text' => __('&laquo;'),
                    'next_text' => __('&raquo;'),
                    'total'     => $total_pages,
                    'current'   => $page,
                ]);
                ?>
            </div>
        </div>
        <?php
        echo '</div>';

        $this->print_scripts();
    }

    private function get_sort_url(string $key, string $current_orderby, string $current_order): string
    {
        $order = 'ASC';
        if ($current_orderby === $key && $current_order === 'ASC') {
            $order = 'DESC';
        }
        return add_query_arg(['orderby' => $key, 'order' => $order]);
    }

    private function enqueue_assets(): void
    {
        echo '<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />';
        echo '<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>';

        ?>
        <style>
            .svl-admin-wrap { margin-top: 20px; max-width: 100%; overflow-x: hidden; }
            .svl-header-flex { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-bottom: 10px; }

            .svl-filter-container { background: #fff; border: 1px solid #ccd0d4; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
            .svl-filter-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: end; }
            .svl-filter-item label { display: block; font-weight: 600; margin-bottom: 6px; font-size: 13px; color: #3c434a; }
            .svl-filter-item input, .svl-filter-item .select2-container { width: 100% !important; }
            .svl-filter-actions { display: flex; gap: 8px; }

            .svl-table-scroll-wrapper {
                background: #fff;
                border: 1px solid #ccd0d4;
                border-radius: 8px;
                overflow-x: auto;
                box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            }
            .wp-list-table { border: none !important; border-collapse: collapse; min-width: 1000px; }
            .wp-list-table thead th { background: #f6f7f7; border-bottom: 1px solid #ccd0d4; padding: 12px 10px; }
            .wp-list-table td { padding: 12px 10px; vertical-align: top; border-bottom: 1px solid #f0f0f1; font-size: 13px; line-height: 1.5; }

            .svl-time-box { display: flex; flex-direction: column; }
            .time-main { font-weight: bold; color: #1d2327; }
            .time-sub { font-size: 11px; color: #646970; }

            .svl-ip-code { background: #f0f6fb; color: #0073aa; padding: 2px 6px; border-radius: 4px; font-family: 'Consolas', monospace; font-size: 12px; border: 1px solid #d9eaf7; }
            .svl-badge-country { background: #e7e7ed; color: #3c434a; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
            .svl-asn-text { color: #50575e; font-size: 12px; }

            .svl-path code { background: #f6f7f7; color: #d63638; padding: 2px 4px; border-radius: 3px; font-size: 12px; }
            .svl-ref { font-size: 11px; color: #646970; margin-top: 6px; padding-top: 4px; border-top: 1px dashed #dcdcde; word-break: break-all; }
            .svl-ua-text { display: block; font-size: 11px; color: #646970; line-height: 1.4; white-space: normal; word-break: break-all; max-width: 400px; }

            .select2-container--default .select2-selection--single { height: 32px; border: 1px solid #8c8f94; border-radius: 4px; }
            .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 30px; padding-left: 10px; color: #2c3338; }
            .select2-container--default .select2-selection--single .select2-selection__arrow { height: 30px; }

            @media screen and (max-width: 782px) {
                .svl-filter-grid { grid-template-columns: 1fr; }
                .svl-filter-actions { justify-content: flex-end; }
                .svl-header-flex { flex-direction: column; align-items: flex-start; }
            }
        </style>
        <?php
    }

    private function print_scripts(): void
    {
        ?>
        <script>
            jQuery(document).ready(function($) {
                $('.svl-select2-ajax').each(function() {
                    var $el = $(this);
                    $el.select2({
                        ajax: {
                            url: ajaxurl,
                            dataType: 'json',
                            delay: 250,
                            data: function (params) {
                                return { q: params.term, action: $el.data('action') };
                            },
                            processResults: function (data) { return { results: data }; },
                            cache: true
                        },
                        placeholder: 'All',
                        allowClear: true,
                        minimumInputLength: 0
                    });
                });
            });
        </script>
        <?php
    }
}