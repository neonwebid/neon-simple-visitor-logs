<?php

namespace NeonWebId\SimpleVisitorLogs;

final class VisitorLogTracker
{
    public static function init(): void
    {
        add_action('template_redirect', [self::class, 'track']);
    }

    public static function track(): void
    {
        // ===== HARD SKIP (WP CONTEXT) =====
        if (is_admin() || wp_doing_ajax() || wp_doing_cron()) {
            return;
        }
        if (current_user_can('manage_options')) {
            return;
        }

        // ===== REQUEST DATA =====
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        $ua   = $_SERVER['HTTP_USER_AGENT'] ?? '';

        // ===== IP DETECTION (CF SAFE) =====
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
        }

        if ($ip === '') {
            return;
        }

        // ===== STATIC ASSET SKIP =====
        if (self::isStaticAsset($path)) {
            return;
        }

        // ===== UA EXCLUDE =====
        if (self::isExcludedUserAgent($ua)) {
            return;
        }

        // ===== IP LONG (IPv4 ONLY) =====
        $long    = ip2long($ip);
        $ip_long = ($long !== false) ? sprintf('%u', $long) : 0;

        global $wpdb;
        $table = $wpdb->prefix.'visitor_logs';

        // ===== CACHE ASN / COUNTRY =====
        $cached = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT country, asn, asn_number, isp, org, hosting, proxy, mobile
                 FROM $table
                 WHERE ip_address = %s AND asn_number > 0
                 ORDER BY id DESC LIMIT 1",
                $ip
            )
        );

        $country = $cached->country ?? 'Unknown';
        $asn     = $cached->asn ?? 'Unknown';
        $asn_num = $cached->asn_number ?? 0;
        $isp     = $cached->isp ?? 'Unknown';
        $org     = $cached->org ?? 'Unknown';
        $hosting = isset($cached->hosting) ? (int)$cached->hosting : 0;
        $proxy   = isset($cached->proxy) ? (int)$cached->proxy : 0;
        $mobile  = isset($cached->mobile) ? (int)$cached->mobile : 0;

        // ===== LOOKUP ONLY IF NEEDED =====
        if (!$cached) {
            $res = wp_remote_get(
                "http://ip-api.com/json/{$ip}?fields=status,country,isp,org,as,hosting,proxy,mobile",
                ['timeout' => 3]
            );

            if (!is_wp_error($res)) {
                $body = json_decode(wp_remote_retrieve_body($res), true);
                if (($body['status'] ?? '') === 'success') {
                    $country = $body['country'] ?? 'Unknown';
                    $asn     = $body['as'] ?? 'Unknown';
                    if (preg_match('/^AS(\d+)/i', $asn, $m)) {
                        $asn_num = (int) $m[1];
                    }
                    $isp     = $body['isp'] ?? 'Unknown';
                    $org     = $body['org'] ?? 'Unknown';
                    $hosting = isset($body['hosting']) ? ($body['hosting'] ? 1 : 0) : 0;
                    $proxy   = isset($body['proxy']) ? ($body['proxy'] ? 1 : 0) : 0;
                    $mobile  = isset($body['mobile']) ? ($body['mobile'] ? 1 : 0) : 0;
                }
            }
        }

        // ===== DATETIME SETTING =====
        $datetime_mode = get_option('svl_datetime_mode', 'local');
        $created_at    = ($datetime_mode === 'utc') ? current_time('mysql', 1) : current_time('mysql');

        // ===== INSERT LOG =====
        $wpdb->insert($table, [
            'ip_address' => $ip,
            'ip_long'    => $ip_long,
            'country'    => $country,
            'asn'        => $asn,
            'asn_number' => $asn_num,
            'isp'        => $isp,
            'org'        => $org,
            'hosting'    => $hosting,
            'proxy'      => $proxy,
            'mobile'     => $mobile,
            'path'       => $path,
            'referrer'   => $_SERVER['HTTP_REFERER'] ?? '',
            'user_agent' => $ua ?: 'None',
            'created_at' => $created_at,
        ]);
    }

    /* =========================
       HELPERS
       ========================= */

    private static function isStaticAsset(string $path): bool
    {
        return preg_match(
                '#\.(ico|png|jpg|jpeg|gif|svg|css|js|woff2?|ttf|map)$#i',
                $path
            ) === 1;
    }

    private static function isExcludedUserAgent(string $ua): bool
    {
        if ($ua === '') {
            return false;
        }

        $ua = strtolower($ua);

        // KHUSUS:
        $specials = [
            'mediapartners-google',
            'chrome privacy preserving prefetch proxy'
        ];

        if (in_array($ua, $specials, true)) {
            return true; // EXCLUDE
        }

        $bots = [
            // Search engines (stabil, predictable)
            'googlebot',
            'google-inspectiontool',
            'bingbot',
            'yandexbot',
            'applebot',
            'baiduspider',
            'duckduckbot',
            'exabot',
            'seznambot',
            'sogou',

            // SEO tools (legit, high volume, low security value)
            'semrushbot',
            'ahrefsbot',
            'serankingbacklinksbot',
            'mj12bot',
            'dotbot',
            'rogerbot',
            'linkdexbot',
            'siteauditor',
            'screamingfrog',
            'barkrowler',

            // Social / feed preview (noise tinggi, tidak bernilai forensik)
            'facebookexternalhit',
            'facebot',
            'twitterbot',
            'linkedinbot',
            'pinterest',
            'slackbot',
            'telegrambot',
            'whatsapp',
            'discordbot',
            'feedburner',

            // AI crawlers (identitas jelas, bukan exploit tools)
            'gptbot',
            'openai',
            'anthropic',
            'claudebot',
            'perplexitybot',
            'bytespider',
            'cohere',
            'meta-webindexer',

            // Ads / brand verification (otomatis & repetitif)
            'awariobot',
            'dataforseobot',
            'ias-or',
            'integralads',
            'admantx',
            'doubleverify',
            'moatbot',
            'pixalate',

            // Commercial crawler yang known & konsisten
            'geedoshopproductfinder',
        ];

        $bots = apply_filters('neon_simple_visitor_logs_bots', $bots);
        
        foreach ($bots as $bot) {
            $bot = strtolower($bot);
            if (str_contains($ua, $bot)) {
                return true; // EXCLUDE
            }
        }

        // Additional Excludes from Settings
        $custom_exclude = get_option('svl_exclude_user_agents', '');
        if ($custom_exclude !== '') {
            $custom_bots = array_filter(array_map('trim', explode("\n", $custom_exclude)));
            foreach ($custom_bots as $bot) {
                $bot = strtolower($bot);
                if ($bot !== '' && str_contains($ua, $bot)) {
                    return true; // EXCLUDE
                }
            }
        }

        return false; // LOG
    }

}
