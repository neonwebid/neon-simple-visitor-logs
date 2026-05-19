<?php

namespace NeonWebId\SimpleVisitorLogs\VisitorLogAdminMenu\Pages;

final class Settings
{
    public function __construct()
    {
        // Hook to handle saving settings
        add_action('admin_post_svl_save_settings', [$this, 'handle_save_settings']);
    }

    public function handle_save_settings(): void
    {
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'svl_save_settings_action')) {
            wp_die('Action not authorized.');
        }

        if (!current_user_can('manage_options')) {
            wp_die('You do not have permission to perform this action.');
        }

        // Save Exclude UserAgent
        $exclude_ua = isset($_POST['svl_exclude_user_agents']) ? sanitize_textarea_field($_POST['svl_exclude_user_agents']) : '';
        update_option('svl_exclude_user_agents', $exclude_ua);

        // Save Datetime mode
        $datetime_mode = isset($_POST['svl_datetime_mode']) ? sanitize_text_field($_POST['svl_datetime_mode']) : 'local';
        if (in_array($datetime_mode, ['local', 'utc'], true)) {
            update_option('svl_datetime_mode', $datetime_mode);
        }

        wp_safe_redirect(add_query_arg(
            ['page' => 'svl-settings', 'svl_message' => 'saved'],
            admin_url('admin.php')
        ));
        exit;
    }

    public function render(): void
    {
        if (isset($_GET['svl_message']) && $_GET['svl_message'] === 'saved') {
            echo '<div class="updated notice is-dismissible"><p>Settings saved successfully.</p></div>';
        }

        $exclude_ua = get_option('svl_exclude_user_agents', '');

        $datetime_mode = get_option('svl_datetime_mode', 'local');

        $local_time = current_time('mysql');
        $utc_time = current_time('mysql', 1);

        ?>
        <div class="wrap svl-admin-wrap">
            <h1>Visitor Logs Settings</h1>
            <p class="description">Configure exclude filters and time settings for visitor logging.</p>
            <hr class="wp-header-end">

            <div class="svl-settings-card" style="background: #fff; border: 1px solid #ccd0d4; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-top: 20px; max-width: 800px;">
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="svl_save_settings">
                    <?php wp_nonce_field('svl_save_settings_action'); ?>

                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="svl_exclude_user_agents">Exclude User-Agents</label>
                                </th>
                                <td>
                                    <textarea id="svl_exclude_user_agents" name="svl_exclude_user_agents" rows="12" class="large-text code" style="font-family: monospace; font-size: 13px;"><?php echo esc_textarea($exclude_ua); ?></textarea>
                                    <p class="description">
                                        Enter strings or crawler names (one per line, case-insensitive) to exclude from logging. 
                                        If a visitor's User-Agent contains any of these values, the visit will be ignored.
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="svl_datetime_mode">Datetime Settings</label>
                                </th>
                                <td>
                                    <fieldset>
                                        <legend class="screen-reader-text"><span>Datetime Settings</span></legend>
                                        <label title="local">
                                            <input type="radio" name="svl_datetime_mode" value="local" <?php checked($datetime_mode, 'local'); ?>>
                                            <span><strong>WordPress Timezone</strong> (Local Time: <code><?php echo esc_html($local_time); ?></code>)</span>
                                        </label>
                                        <br>
                                        <label title="utc" style="margin-top: 8px; display: inline-block;">
                                            <input type="radio" name="svl_datetime_mode" value="utc" <?php checked($datetime_mode, 'utc'); ?>>
                                            <span><strong>UTC / GMT</strong> (UTC Time: <code><?php echo esc_html($utc_time); ?></code>)</span>
                                        </label>
                                        <p class="description" style="margin-top: 8px;">
                                            Choose whether log creation timestamps (`created_at`) should follow your WordPress timezone settings or UTC.
                                        </p>
                                    </fieldset>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <p class="submit" style="margin-top: 20px;">
                        <button type="submit" class="button button-primary">Save Settings</button>
                    </p>
                </form>
            </div>
        </div>
        <style>
            .svl-admin-wrap { margin-top: 20px; }
            .form-table th { width: 220px; }
        </style>
        <?php
    }
}
