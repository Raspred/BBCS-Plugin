<?php

/**
 * The plugin installer file
 *
 * @link              https://globus.studio
 * @since             1.1.0
 * @package           Botblocker
 *
 * @wordpress-plugin
 * Plugin Name:       BotBlocker
 * Plugin URI:        https://globus.studio/wordpress-toolkit/
 * Version:           1.1.0
 * Author:            GLOBUS.studio
 * Author URI:        https://globus.studio/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       botblocker
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC') || !defined('BOTBLOCKER')) {
    exit;
}
function bbcs_createTables()
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    /**
     * Creates the 'bbcs_hits' table in the database.
     */
    $table_name_hits = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'hits';
    $sql_hits = "CREATE TABLE IF NOT EXISTS $table_name_hits (
        cid TEXT NOT NULL,
        `uid` TEXT NOT NULL,
        ip TEXT NOT NULL,
        `date` INTEGER NOT NULL DEFAULT 0,
        ptr TEXT NOT NULL,
        lang TEXT NOT NULL,
        country TEXT NOT NULL,
        browser TEXT NOT NULL,/**/
        os TEXT NOT NULL,/**/
        device TEXT NOT NULL,/**/
        referer TEXT NOT NULL,
        `page` TEXT NOT NULL,
        passed INTEGER NOT NULL DEFAULT 0,
        js_w INTEGER NOT NULL,
        js_h INTEGER NOT NULL,
        js_cw INTEGER NOT NULL,
        js_ch INTEGER NOT NULL,
        js_co INTEGER NOT NULL,
        js_pi INTEGER NOT NULL,
        refhost TEXT NOT NULL,
        asnum TEXT NOT NULL,
        asname TEXT NOT NULL,
        result TEXT NOT NULL,
        http_accept TEXT NOT NULL,
        method TEXT NOT NULL,
        ym_uid TEXT NOT NULL,
        ga_uid TEXT NOT NULL,
        ip_short TEXT NOT NULL,
        hosting INTEGER NOT NULL DEFAULT 0,
        hit INTEGER NOT NULL DEFAULT 0,
        timezone TEXT NOT NULL,
        cookie TEXT NOT NULL,
        region TEXT NOT NULL,/**/
        region_name TEXT NOT NULL,/**/
        country_name TEXT NOT NULL,/**/
        proxy TEXT NOT NULL,/**/
        tor TEXT NOT NULL,/**/
        vpn TEXT NOT NULL,/**/
        carrier TEXT NOT NULL,/**/
        useragent TEXT NOT NULL default '', 
        adblock INTEGER NOT NULL,
        lat TEXT NOT NULL,/**/
        lon TEXT NOT NULL,/**/
        city TEXT NOT NULL,/**/          
        generation TEXT NOT NULL,
        generation2 TEXT NOT NULL, 
        ipv4 TEXT NOT NULL, 
        distance TEXT NOT NULL, 
        recaptcha TEXT NOT NULL, 
        wbot TEXT NOT NULL, /**/  
        fp TEXT NOT NULL, /**/  
        UNIQUE KEY cid (cid(191))
    ) $charset_collate;";

    dbDelta($sql_hits);

    /**
     * Creates the 'bbcs_se' table in the database.
     */
    $table_name_se = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'se';
    $sql_se = "CREATE TABLE IF NOT EXISTS $table_name_se (
        id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `priority` INTEGER NOT NULL DEFAULT 100,
        search TEXT NOT NULL,
        `data` TEXT NOT NULL,
        rule TEXT NOT NULL,
        comment TEXT NOT NULL,
        `disable` INTEGER NOT NULL,
        distance TEXT NOT NULL
    ) $charset_collate;";

    dbDelta($sql_se);

    /**
     * Creates the 'bbcs_ipv4rules' table in the database.
     */
    $table_name_ipv4rules = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'ipv4rules';
    $sql_ipv4rules = "CREATE TABLE IF NOT EXISTS $table_name_ipv4rules (
        id BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `priority` INTEGER NOT NULL DEFAULT 100,
        search TEXT NOT NULL,
        ip1 INTEGER NOT NULL,
        ip2 INTEGER NOT NULL,
        rule TEXT NOT NULL,
        comment TEXT NOT NULL,
        expires BIGINT(20) NOT NULL DEFAULT " . BOTBLOCKER_EXP_INF . ",
        `disable` INTEGER NOT NULL DEFAULT 0,
        `readonly` INTEGER NOT NULL DEFAULT 0,
        UNIQUE KEY search (search(191))
    ) $charset_collate;";

    dbDelta($sql_ipv4rules);

    /**
     * Creates the 'bbcs_ipv6rules' table in the database.
     */
    $table_name_ipv6rules = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'ipv6rules';
    $sql_ipv6rules = "CREATE TABLE IF NOT EXISTS $table_name_ipv6rules (
        id BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `priority` INTEGER NOT NULL DEFAULT 100,
        search TEXT NOT NULL,
        ip1 TEXT NOT NULL,
        ip2 TEXT NOT NULL,
        rule TEXT NOT NULL,
        comment TEXT NOT NULL,
        expires BIGINT(20) NOT NULL DEFAULT " . BOTBLOCKER_EXP_INF . ",
        `disable` INTEGER NOT NULL DEFAULT 0,
        `readonly` INTEGER NOT NULL DEFAULT 0,
        UNIQUE KEY search (search(191))
    ) $charset_collate;";

    dbDelta($sql_ipv6rules);

    /**
     * Creates the 'bbcs_path' table in the database.
     */
    $table_name_path = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'path';
    $sql_path = "CREATE TABLE IF NOT EXISTS $table_name_path (
        id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `priority` INT(11) NOT NULL DEFAULT 100,
        search TEXT NOT NULL,
        rule TEXT NOT NULL,
        comment TEXT NOT NULL,
        `disable` INT(1) NOT NULL DEFAULT 0
    ) $charset_collate;";

    dbDelta($sql_path);

    /**
     * Creates the 'bbcs_rules' table in the database.
     */
    $table_name_rules = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'rules';
    $sql_rules = "CREATE TABLE IF NOT EXISTS $table_name_rules (
        id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
        search VARCHAR(255) UNIQUE NOT NULL,
        `priority` INTEGER NOT NULL DEFAULT 1,
        `type` TEXT NOT NULL,
        `data` TEXT NOT NULL,
        expires BIGINT(20) NOT NULL DEFAULT " . BOTBLOCKER_EXP_INF . ",
        `disable` INTEGER NOT NULL DEFAULT 0,
        rule TEXT NOT NULL,
        comment TEXT NOT NULL
    ) $charset_collate;";

    dbDelta($sql_rules);

    /**
     * Creates the 'bbcs_settings' table in the database.
     */
    $table_name_settings = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'settings';
    $sql_settings = "CREATE TABLE IF NOT EXISTS $table_name_settings (
        id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `key` VARCHAR(255) NOT NULL UNIQUE,
        `value` TEXT NOT NULL
    ) $charset_collate;";

    dbDelta($sql_settings);

    /**
     * Creates the 'bbcs_proxy' table in the database.
     */
    $table_name_proxy = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'proxy';
    $sql_proxy = "CREATE TABLE IF NOT EXISTS $table_name_proxy (
        id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
        k TEXT NOT NULL default '', 
        v TEXT NOT NULL default ''
        ) $charset_collate;";

    dbDelta($sql_proxy);

    /**
     * Creates the 'bbcs_ptrcache' table in the database.
     */
    $table_name_ptrcache = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'ptrcache';
    $sql_ptrcache = "CREATE TABLE IF NOT EXISTS $table_name_ptrcache (
        ip TEXT(191) NOT NULL default '', 
        ptr TEXT(256) NOT NULL default '', 
        `date` INTEGER NOT NULL default '0', 
        etime TEXT,
        PRIMARY KEY (ip(191))
    ) $charset_collate;";

    dbDelta($sql_ptrcache);
}

function bbcs_addServerIPs()
{
    global $wpdb;

    // Функция для проверки и добавления IPv4 адреса
    function addIPv4Rule($ip, $comment)
    {
        global $wpdb;
        if ($wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}bbcs_ipv4rules WHERE search = %s", $ip)) == 0) {
            $wpdb->query($wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}bbcs_ipv4rules (priority, search, ip1, ip2, rule, comment, expires, readonly) VALUES (%d, %s, %d, %d, %s, %s, %d, %d)",
                10,
                $ip,
                bbcs_ipToNumeric($ip),
                bbcs_ipToNumeric($ip),
                'allow',
                $comment,
                BOTBLOCKER_EXP_INF,
                1
            ));
        }
    }

    // Функция для проверки и добавления IPv6 адреса
    function addIPv6Rule($ip, $comment)
    {
        global $wpdb;
        $expandedIP = bbcs_expandIPv6($ip);
        if ($wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}bbcs_ipv6rules WHERE search = %s", $expandedIP)) == 0) {
            $wpdb->query($wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}bbcs_ipv6rules (priority, search, ip1, ip2, rule, comment, expires, readonly) VALUES (%d, %s, %s, %s, %s, %s, %d, %d)",
                10,
                $expandedIP,
                bbcs_ipToNumeric($expandedIP),
                bbcs_ipToNumeric($expandedIP),
                'allow',
                $comment,
                BOTBLOCKER_EXP_INF,
                1
            ));
        }
    }

    // Добавление localhost IPv4
    addIPv4Rule('127.0.0.1', 'Local IP');

    // Добавление SERVER_ADDR IPv4
    if (filter_var($_SERVER['SERVER_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        addIPv4Rule($_SERVER['SERVER_ADDR'], 'Local IP from SERVER_ADDR');
    }

    // Получение и добавление внешнего IPv4 сервера
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, BOTBLOCKER_API_GS_URL . 'ip?v=4');
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_USERAGENT, BOTBLOCKER_USER_AGENT);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_FTP_SSL, CURLFTPSSL_TRY);
    $serverIPv4 = @trim(strip_tags(curl_exec($ch)));
    curl_close($ch);
    if (filter_var($serverIPv4, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        addIPv4Rule($serverIPv4, 'Server IPv4');
    }

    // Добавление localhost IPv6
    addIPv6Rule('::1', 'Local IP ::1');

    // Добавление SERVER_ADDR IPv6
    if (filter_var($_SERVER['SERVER_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        addIPv6Rule($_SERVER['SERVER_ADDR'], 'Local IP from SERVER_ADDR');
    }

    // Получение и добавление внешнего IPv6 сервера
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, BOTBLOCKER_API_GS_URL . 'ip?v=6');
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_USERAGENT, BOTBLOCKER_USER_AGENT);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_FTP_SSL, CURLFTPSSL_TRY);
    $serverIPv6 = @trim(strip_tags(curl_exec($ch)));
    curl_close($ch);
    if (filter_var($serverIPv6, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        addIPv6Rule($serverIPv6, 'Server IPv6');
    }
}

function bbcs_dbIndexCreate()
{
    global $wpdb;
    $index_ipv4rules = "SHOW INDEX FROM {$wpdb->prefix}bbcs_ipv4rules WHERE Key_name='ipv4range_disabled_index';";
    if (!$wpdb->get_results($index_ipv4rules)) {
        $wpdb->query("CREATE INDEX ipv4range_disabled_index ON {$wpdb->prefix}bbcs_ipv4rules (disable, ip1, ip2);");
    }

    $index_ipv6rules = "SHOW INDEX FROM {$wpdb->prefix}bbcs_ipv6rules WHERE Key_name='ipv6range_disabled_index';";
    if (!$wpdb->get_results($index_ipv6rules)) {
        $wpdb->query("CREATE INDEX ipv6range_disabled_index ON {$wpdb->prefix}bbcs_ipv6rules (disable, ip1(191), ip2(191));");
    }

    $index_rules_priority = "SHOW INDEX FROM {$wpdb->prefix}bbcs_rules WHERE Key_name='i_priority';";
    if (!$wpdb->get_results($index_rules_priority)) {
        $wpdb->query("CREATE INDEX i_priority ON {$wpdb->prefix}bbcs_rules (priority);");
    }

    $index_rules_search = "SHOW INDEX FROM {$wpdb->prefix}bbcs_rules WHERE Key_name='i_search';";
    if (!$wpdb->get_results($index_rules_search)) {
        $wpdb->query("CREATE INDEX i_search ON {$wpdb->prefix}bbcs_rules (search(191));");
    }

    $index_hits_ip = "SHOW INDEX FROM {$wpdb->prefix}bbcs_hits WHERE Key_name='i_ip';";
    if (!$wpdb->get_results($index_hits_ip)) {
        $wpdb->query("CREATE INDEX i_ip ON {$wpdb->prefix}bbcs_hits (ip(191));");
    }

    $index_hits_passed = "SHOW INDEX FROM {$wpdb->prefix}bbcs_hits WHERE Key_name='i_passed';";
    if (!$wpdb->get_results($index_hits_passed)) {
        $wpdb->query("CREATE INDEX i_passed ON {$wpdb->prefix}bbcs_hits (passed);");
    }

    $index_hits_date = "SHOW INDEX FROM {$wpdb->prefix}bbcs_hits WHERE Key_name='i_date';";
    if (!$wpdb->get_results($index_hits_date)) {
        $wpdb->query("CREATE INDEX i_date ON {$wpdb->prefix}bbcs_hits (date);");
    }
}

function bbcs_insertInitialData($salt_bb = '')
{
    bbcs_insertDefaultRules();
    bbcs_insertDefaultSearchEngines();
    bbcs_insertDefaultPaths();
    bbcs_insertDefaultSettings($salt_bb);
}

function bbcs_insertDefaultRules()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'bbcs_rules';
    $default_rules = [
        ['priority' => 10, 'type' => 'useragent', 'data' => '', 'search' => 'useragent=', 'rule' => 'block', 'comment' => 'Empty User-Agent'],
        ['priority' => 10, 'type' => 'lang', 'data' => '', 'search' => 'lang=', 'rule' => 'block', 'comment' => 'Empty Language'],
        ['priority' => 10, 'type' => 'asname', 'data' => 'Biterika', 'search' => 'asname=Biterika', 'rule' => 'block', 'comment' => 'Spam ASN'],
        ['priority' => 11, 'type' => 'referer', 'data' => '', 'search' => 'referer=', 'rule' => 'dark', 'comment' => 'Empty Referer']
    ];

    foreach ($default_rules as $rule) {
        if ($wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE search = %s", $rule['search'])) == 0) {
            $wpdb->query($wpdb->prepare(
                "INSERT INTO $table_name (priority, type, data, search, rule, comment) VALUES (%d, %s, %s, %s, %s, %s)",
                $rule['priority'],
                $rule['type'],
                $rule['data'],
                $rule['search'],
                $rule['rule'],
                $rule['comment']
            ));
        }
    }
}

function bbcs_insertDefaultSearchEngines()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'bbcs_se';
    $default_search_engines = [
        ['priority' => 10, 'search' => 'Googlebot', 'data' => '.googlebot.com', 'rule' => 'allow', 'comment' => 'GoogleBot main indexer', 'disable' => 0],
        ['priority' => 10, 'search' => 'yandex.com', 'data' => '.yandex.ru .yandex.net .yandex.com', 'rule' => 'allow', 'comment' => 'All Yandex bots', 'disable' => 0],
        ['priority' => 10, 'search' => 'Mail.RU_Bot', 'data' => '.mail.ru .smailru.net', 'rule' => 'allow', 'comment' => 'All Bots Mail.RU Indexers', 'disable' => 0],
        ['priority' => 10, 'search' => 'bingbot', 'data' => 'search.msn.com', 'rule' => 'allow', 'comment' => 'Bing.com indexer', 'disable' => 0],
        ['priority' => 10, 'search' => 'msnbot', 'data' => 'search.msn.com', 'rule' => 'allow', 'comment' => 'Additional Indexer Bing.com', 'disable' => 0],
        ['priority' => 10, 'search' => 'Google-Site-Verification', 'data' => '.googlebot.com .google.com', 'rule' => 'allow', 'comment' => 'Check for Google Search Console', 'disable' => 0],
        ['priority' => 10, 'search' => 'Chrome-Lighthouse', 'data' => '.google.com', 'rule' => 'allow', 'comment' => 'PageSpeed Insights: https://pagespeed.web.dev/', 'disable' => 1],
        ['priority' => 10, 'search' => 'Google-InspectionTool', 'data' => '.googlebot.com', 'rule' => 'allow', 'comment' => 'Search Console', 'disable' => 1],
        ['priority' => 10, 'search' => 'Mediapartners', 'data' => '.googlebot.com .google.com', 'rule' => 'allow', 'comment' => 'AdSense bot', 'disable' => 1]
    ];

    foreach ($default_search_engines as $se) {
        if ($wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE search = %s", $se['search'])) == 0) {
            $wpdb->query($wpdb->prepare(
                "INSERT INTO $table_name (priority, search, data, rule, comment, disable) VALUES (%d, %s, %s, %s, %s, %d)",
                $se['priority'],
                $se['search'],
                $se['data'],
                $se['rule'],
                $se['comment'],
                $se['disable']
            ));
        }
    }
}

function bbcs_insertDefaultPaths()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'bbcs_path';
    $default_paths = [
        ['priority' => 1,  'search' => '/wp-cron.php', 'rule' => 'allow', 'comment' => 'WordPress cron jobs', 'disable' => 0],
        ['priority' => 1,  'search' => '/wp-admin/admin-ajax.php', 'rule' => 'allow', 'comment' => 'WordPress AJAX', 'disable' => 0]
    ];

    foreach ($default_paths as $path) {
        if ($wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}bbcs_path WHERE search = %s", $path['search'])) == 0) {
            $wpdb->query($wpdb->prepare(
                "INSERT INTO $table_name (priority, search, rule, comment, disable) VALUES (%d, %s, %s, %s, %d)",
                $path['priority'],
                $path['search'],
                $path['rule'],
                $path['comment'],
                $path['disable']
            ));
        }
    }
}

function bbcs_insertExtendedPaths()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'bbcs_path';
    $default_paths = [
        ['priority' => 1,  'search' => '/favicon.ico', 'rule' => 'allow', 'comment' => 'WordPress Favicon', 'disable' => 0],
        ['priority' => 10, 'search' => '/wp-admin/post.php', 'rule' => 'allow', 'comment' => 'WordPress post editing', 'disable' => 0],
        ['priority' => 1,  'search' => '/?wc-ajax=', 'rule' => 'allow', 'comment' => 'WooCommerce AJAX', 'disable' => 0],
        ['priority' => 1,  'search' => '/wp-admin/load-scripts.php', 'rule' => 'allow', 'comment' => 'WordPress script loading', 'disable' => 0],
        ['priority' => 1,  'search' => '/wp-admin/load-styles.php', 'rule' => 'allow', 'comment' => 'WordPress style loading', 'disable' => 0],
        ['priority' => 10, 'search' => '/wp-admin/admin-post.php', 'rule' => 'allow', 'comment' => 'WordPress admin posts', 'disable' => 0],
        ['priority' => 1,  'search' => '/wp-admin/async-upload.php', 'rule' => 'allow', 'comment' => 'WordPress async upload', 'disable' => 0],
        ['priority' => 1,  'search' => '/wp-admin/update-core.php', 'rule' => 'allow', 'comment' => 'WordPress core updates', 'disable' => 0],
        ['priority' => 10, 'search' => '/wp-admin/customize.php', 'rule' => 'allow', 'comment' => 'WordPress customizer', 'disable' => 0],
        ['priority' => 10, 'search' => '/wp-admin/admin.php', 'rule' => 'allow', 'comment' => 'WordPress admin', 'disable' => 0],
        ['priority' => 10, 'search' => '/wp-admin/options.php', 'rule' => 'allow', 'comment' => 'WordPress options', 'disable' => 0],
        ['priority' => 10, 'search' => '/wp-admin/edit-comments.php', 'rule' => 'allow', 'comment' => 'WordPress comment editing', 'disable' => 0],
        ['priority' => 10, 'search' => '/wp-admin/edit.php', 'rule' => 'allow', 'comment' => 'WordPress post/page editing', 'disable' => 0],
        ['priority' => 10, 'search' => '/wp-admin/users.php', 'rule' => 'allow', 'comment' => 'WordPress user management', 'disable' => 0],
        ['priority' => 10, 'search' => '/wp-admin/profile.php', 'rule' => 'allow', 'comment' => 'WordPress user profile', 'disable' => 0],
        ['priority' => 10, 'search' => '/wp-admin/media-new.php', 'rule' => 'allow', 'comment' => 'WordPress media upload', 'disable' => 0],
        ['priority' => 10, 'search' => '/wp-admin/upload.php', 'rule' => 'allow', 'comment' => 'WordPress media library', 'disable' => 0],
        ['priority' => 10, 'search' => '/wp-admin/post-new.php', 'rule' => 'allow', 'comment' => 'WordPress new post/page', 'disable' => 0],
        ['priority' => 10, 'search' => '/wp-admin/edit-tags.php', 'rule' => 'allow', 'comment' => 'WordPress taxonomy management', 'disable' => 0],
        ['priority' => 10, 'search' => '/wp-comments-post.php', 'rule' => 'allow', 'comment' => 'WordPress comment posting', 'disable' => 0],
        ['priority' => 10, 'search' => '/wp-includes/js/tinymce/', 'rule' => 'allow', 'comment' => 'WordPress TinyMCE editor', 'disable' => 0],
        ['priority' => 1,  'search' => '/wp-json/', 'rule' => 'allow', 'comment' => 'WordPress REST API', 'disable' => 0],
        ['priority' => 1,  'search' => '/xmlrpc.php', 'rule' => 'allow', 'comment' => 'WordPress XML-RPC', 'disable' => 0]
    ];

    foreach ($default_paths as $path) {
        if ($wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}bbcs_path WHERE search = %s", $path['search'])) == 0) {
            $wpdb->query($wpdb->prepare(
                "INSERT INTO $table_name (priority, search, rule, comment, disable) VALUES (%d, %s, %s, %s, %d)",
                $path['priority'],
                $path['search'],
                $path['rule'],
                $path['comment'],
                $path['disable']
            ));
        }
    }
}

function bbcs_insertDefaultSettings($salt_bb)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'bbcs_settings';

    $license_email = bbcs_getLicenseEmail();
    $license_pass = md5(md5($license_email) . $salt_bb);
    $license_key = bbcs_generateLicenseKey(BOTBLOCKER_SHORT_NAME, $license_email);
    $license_secret = bbcs_generateLicenseKey(BOTBLOCKER_SHORT_NAME, $license_pass);

    $initSettings = [
        'disable' => 0,
        'bbcs_captcha_img_pack' => 1,
        'bbcs_captcha_mode' => 2,
        'bbcs_captcha_wait' => 10,
        'bbcs_captcha_disable' => 0,
        'license_email' => $license_email,
        'license_pass' => $license_pass,
        'license' => 'free',
        'license_key' => $license_key,
        'license_secret' => $license_secret
    ];

    $defaultSettings = [

        'block_empty_ua' => 1,
        'block_empty_lang' => 1,
        'block_nojs_users' => 1,
        'block_proxy_users' => 1,
        'block_vpn_users' => 1,
        'block_tor_users' => 1,
        'block_IPv6_users' => 1,
        'block_adblocker_users' => 1,

        'get_browser_type' => 1,
        'get_os_type' => 1,
        'get_device_type' => 1,

        'admin_report_period' => 5,
        'admin_store_period' => 7,
        'admin_gmt_offset' => 0,

        'check' => 0,
        'unresponsive' => 1,
        'cookie' => BOTBLOCKER_SHORT_NAME,
        'salt' => $salt_bb,
        'hits_per_user' => 500,
        'time_ban' => '200',
        'time_ban_2' => '400',
        'utm_referrer' => 1,
        'utm_noindex' => 1,
        'check_get_ref' => 1,
        'ptrcache_time' => 10,
        'botblocker_log_tests' => 1,
        'botblocker_log_local' => 1,
        'botblocker_log_allow' => 1,
        'botblocker_log_fake' => 1,
        'botblocker_log_goodip' => 1,
        'botblocker_log_block' => 1,
        'header_test_code' => 200,
        'header_error_code' => 400,
        'noarchive' => 0,
        'last_rule' => '',
        'samesite' => 'Lax', // Lax, Strict, None
        'iframe_stop' => 0, // 1 - block, 0 - no check
        'hosting_block' => 0, // 1 - block, 0 - no check
        'block_fake_ref' => 1 // 1 - block, 0 - do not check
    ];

    $defaultIntegration = [
        'recaptcha_check' => 1,
        'recaptcha_key2' => '6LdNE9IZAAAAANZhNB70M9rdJFhUeZP9WIEuPjwL',
        'recaptcha_secret2' => '6LdNE9IZAAAAACkkzzx-WZ66rkP8WC3QaV7bTPB3',
        'recaptcha_key3' => '6LdzJvcpAAAAAOHUj2rnfmpa_ecqiWNW0jDIOtLl',
        'recaptcha_secret3' => '6LdzJvcpAAAAAFH7vb9wnXeTasTSge1krB6GUJLm',
        'memcached_counter' => 1,
        'memcached_host' => '127.0.0.1',
        'memcached_port' => 11211,
        'memcached_prefix' => BOTBLOCKER_PREFIX,

        'bbcs_api_url' => BOTBLOCKER_API_URL,
        'bbcs_api_gs_url' => BOTBLOCKER_API_GS_URL,
        /*
        Reason: Deprecated
        'bbcs_api_key' => '',
        'bbcs_api_secret' => '',        
        'bbcs_api_gs_key' => '',
        'bbcs_api_gs_secret' => '',*/

        'redis_host' => '',
        'redis_port' => 6379,
        'redis_prefix' => BOTBLOCKER_PREFIX,
        'redis_db' => 0,
        'redis_password' => '',
        'redis_counter' => 0
    ];

    $all_settings = array_merge($initSettings, $defaultSettings, $defaultIntegration);

    bbcs_saveSettingsToFile($all_settings);
    bbcs_saveSettingsToDatabase($table_name, $all_settings);
}

function bbcs_getLicenseEmail()
{
    $user = wp_get_current_user();
    if (is_user_logged_in()) {
        $license_email = bbcs_get_email($user->ID);
        if (empty($license_email)) {
            $license_email = '{email}';
        }
    } else {
        $license_email = '{email}';
    }
    return $license_email;
}

function bbcs_saveSettingsToFile($settings)
{
    $settingsFile = BOTBLOCKER_DIR . 'data/settings.php';
    $settingsContent = "<?php\nreturn " . var_export($settings, true) . ";\n";
    file_put_contents($settingsFile, $settingsContent);
}

function bbcs_saveSettingsToDatabase($table_name, $settings)
{
    global $wpdb;
    foreach ($settings as $setting_key => $setting_value) {
        if ($wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE `key` = %s", $setting_key)) == 0) {
            $wpdb->insert(
                $table_name,
                ['key' => $setting_key, 'value' => is_array($setting_value) ? json_encode($setting_value) : $setting_value],
                ['%s', '%s']
            );
        } else {
            $wpdb->update(
                $table_name,
                ['value' => is_array($setting_value) ? json_encode($setting_value) : $setting_value],
                ['key' => $setting_key],
                ['%s'],
                ['%s']
            );
        }
    }
}



/**
 * Insert code into wp-config.php
 */
function bbcs_insertCodeToWpConfig()
{
    $wp_config_file = ABSPATH . 'wp-config.php';

    if (!file_exists($wp_config_file)) {
        return false;
    }

    $config_contents = file_get_contents($wp_config_file);
    $marker_start = '/* BBCS Start */';
    $marker_end = '/* BBCS End */';

    if (strpos($config_contents, $marker_start) !== false && strpos($config_contents, $marker_end) !== false) {
        return false;
    }

    $insert_position = strpos($config_contents, "/* That's all, stop editing! Happy publishing. */");

    if ($insert_position === false) {
        return false;
    }

    $plugin_relative_path = str_replace(ABSPATH, '', BOTBLOCKER_DIR) . 'botblocker-early.php';
    $code_to_insert = $marker_start . "\n" . <<<EOD
    if (file_exists(ABSPATH . '$plugin_relative_path')) {
        require_once ABSPATH . '$plugin_relative_path';
        \$botBlocker = new BotBlockerEarly();
        \$botBlocker->run();
    }
    EOD;
    $code_to_insert .= "\n" . $marker_end;

    $before_insert = substr($config_contents, 0, $insert_position);
    $after_insert = substr($config_contents, $insert_position);

    $new_config_contents = $before_insert . PHP_EOL . $code_to_insert . PHP_EOL . $after_insert;

    file_put_contents($wp_config_file, $new_config_contents);

    return true;
}


/**
 * Remove code from wp-config.php
 */
function bbcs_removeCodeFromWpConfig()
{
    $wp_config_file = ABSPATH . 'wp-config.php';

    if (!file_exists($wp_config_file)) {
        return false;
    }

    $config_contents = file_get_contents($wp_config_file);
    $marker_start = '/* BBCS Start */';
    $marker_end = '/* BBCS End */';

    $start_position = strpos($config_contents, $marker_start);
    $end_position = strpos($config_contents, $marker_end);

    if ($start_position === false || $end_position === false) {
        return false;
    }

    $end_position += strlen($marker_end);

    $new_config_contents = substr($config_contents, 0, $start_position) . substr($config_contents, $end_position);

    file_put_contents($wp_config_file, $new_config_contents);

    return true;
}

// TODO - Double method Helpers bbcs_initSalt()
function bbcs_createSaltFile($bbcs_start_files = false)
{
    $saltFilePath = BOTBLOCKER_DIR . 'data/salt.php';

    if (!file_exists($saltFilePath) || $bbcs_start_files == true) {
        $host_key = md5($_SERVER['HTTP_HOST']);
        $salt_bb = bin2hex(random_bytes(12));
        $salt_ps = bin2hex(random_bytes(12));
        $salt_pz = time();

        $fileContent = "<?php\n";
        $fileContent .= '$this->BBCS' . "['host_key'] = '$host_key';\n";
        $fileContent .= '$this->BBCS' . "['salt_bb'] = '$salt_bb';\n";
        $fileContent .= '$this->BBCS' . "['salt_ps'] = '$salt_ps';\n";
        $fileContent .= '$this->BBCS' . "['salt_pz'] = '$salt_pz';\n";

        file_put_contents($saltFilePath, $fileContent);

        return $salt_bb;
    }
}


function bbcs_installMuPlugin()
{
    /*    
    $mu_plugin_dir = WPMU_PLUGIN_DIR;
    $mu_plugin_file = $mu_plugin_dir . '/botblocker-mu.php';
*/
    $botblocker_path = str_replace(ABSPATH, '', BOTBLOCKER_DIR) . 'botblocker-mu.php';
    $mu_plugin_content = <<<EOD
<?php
/*
Plugin Name: CyberSecure BotBlocker
Description: BotBlocker by CyberSecure is a powerful WordPress plugin designed to safeguard your website from unwanted bots and malicious activities.
Aythor: GLOBUS.studio
Version: 1.0.0
*/

if (file_exists(ABSPATH . '{$botblocker_path}')) {
    require_once ABSPATH . '{$botblocker_path}';

    \$botBlocker = new BotBlockerMu();
    \$botBlocker->run();
}
EOD;

    $mu_plugins_dir = WP_CONTENT_DIR . '/mu-plugins';

    if (!file_exists($mu_plugins_dir)) {
        mkdir($mu_plugins_dir);
    }

    $mu_plugin_file = $mu_plugins_dir . '/cybersecure-botblocker.php';

    if (file_put_contents($mu_plugin_file, $mu_plugin_content) !== false) {
    } else {
    }
}

function bbcs_uninstallMuPlugin()
{
    $mu_plugin_file = WP_CONTENT_DIR . '/mu-plugins/cybersecure-botblocker.php';
    if (file_exists($mu_plugin_file)) {
        unlink($mu_plugin_file);
    }
}

function bbcs_createRuleFiles($bbcs_start_files = false)
{
    $se_data = '<?php
// If this file is called directly, abort.
if (!defined(\'ABSPATH\') || !defined(\'WPINC\') || !defined(\'BOTBLOCKER\')) {
    exit;
}

return [
    \'bbcs_rule\' => [
        \'Googlebot\' => \'allow\',
        \'yandex.com\' => \'allow\',
        \'Mail.RU_Bot\' => \'allow\',
        \'bingbot\' => \'allow\',
        \'msnbot\' => \'allow\',
        \'Google-Site-Verification\' => \'allow\',
    ],
    
    \'bbcs_se\' => [
        \'Googlebot\' => [\'.googlebot.com\'],
        \'yandex.com\' => [\'.yandex.ru\', \'.yandex.net\', \'.yandex.com\'],
        \'Mail.RU_Bot\' => [\'.mail.ru\', \'.smailru.net\'],
        \'bingbot\' => [\'search.msn.com\'],
        \'msnbot\' => [\'search.msn.com\'],
        \'Google-Site-Verification\' => [\'.googlebot.com\', \'.google.com\'],
    ]
];';

    $wp_path = '<?php
// If this file is called directly, abort.
if (!defined(\'ABSPATH\') || !defined(\'WPINC\') || !defined(\'BOTBLOCKER\')) {
    exit;
}

return [
    \'bbcs_path\' => [
        \'wp-cron.php\' => \'allow\',
        \'wp-admin/admin-ajax.php\' => \'allow\',
        \'wp-admin/post.php\' => \'allow\',
        \'?wc-ajax=\' => \'allow\'
    ],
];';

    $proxy_data = '<?php' . "\n";
    $rules_data = '<?php' . "\n";
    $ip_data = '<?php // 127.0.0.1' . "\n";

    $search_engines_file = BOTBLOCKER_DIR . 'data/search_engines.php';
    $paths_file = BOTBLOCKER_DIR . 'data/paths.php';
    $proxy_file = BOTBLOCKER_DIR . 'data/proxy.php';
    $rules_file = BOTBLOCKER_DIR . 'data/rules.php';
    $ip_file = BOTBLOCKER_DIR . 'data/ip.php';

    if (!file_exists($search_engines_file) || $bbcs_start_files == true) {
        file_put_contents($search_engines_file, $se_data, LOCK_EX);
    }
    if (!file_exists($paths_file) || $bbcs_start_files == true) {
        file_put_contents($paths_file, $wp_path, LOCK_EX);
    }
    if (!file_exists($proxy_file) || $bbcs_start_files == true) {
        file_put_contents($proxy_file, $proxy_data, LOCK_EX);
    }
    if (!file_exists($rules_file) || $bbcs_start_files == true) {
        file_put_contents($rules_file, $rules_data, LOCK_EX);
    }
    if (!file_exists($ip_file) || $bbcs_start_files == true) {
        file_put_contents($ip_file, $ip_data, LOCK_EX);
    }
}


function bbcs_deleteRuleFiles()
{
    $files = [
        BOTBLOCKER_DIR . 'data/search_engines.php',
        BOTBLOCKER_DIR . 'data/paths.php',
        BOTBLOCKER_DIR . 'data/proxy.php',
        BOTBLOCKER_DIR . 'data/rules.php',
        BOTBLOCKER_DIR . 'data/ip.php',
    ];
    foreach ($files as $file) {
        if (file_exists($file)) {
            unlink($file);
        }
    }
}


function bbcs_tablesExist()
{
    global $wpdb;

    $tables = [
        $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'hits',
        $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'se',
        $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'ipv4rules',
        $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'ipv6rules',
        $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'path',
        $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'rules',
        $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'settings',
        $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'ptrcache',
        $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'proxy'
    ];

    foreach ($tables as $table) {
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {

            return false;
        }
    }
    return true;
}


function bbcs_check_install()
{
    $bbcs_start_files = false;

    if (!bbcs_tablesExist()) {
        $bbcs_start_files = true;
        $salt_bb = bbcs_createSaltFile($bbcs_start_files);
        bbcs_createTables();
        bbcs_addServerIPs();
        bbcs_dbIndexCreate();
        bbcs_insertInitialData($salt_bb);
        bbcs_createRuleFiles($bbcs_start_files);
    }
}
