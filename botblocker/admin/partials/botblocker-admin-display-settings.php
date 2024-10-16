<?php
// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC') || !defined('BOTBLOCKER')) {
    exit;
}

global $BBCS;

include('botblocker-section-header.php');

global $wpdb;
$table_name = $wpdb->prefix . 'bbcs_settings';

function load_settings()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'bbcs_settings';
    $results = $wpdb->get_results("SELECT `key`, `value` FROM $table_name", ARRAY_A);
    $settings = [];
    foreach ($results as $row) {
        $key = $row['key'];
        $value = $row['value'];
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $settings[$key] = $decoded;
        } else {
            $settings[$key] = $value;
        }
    }
    return $settings;
}

$settings = load_settings();

if (isset($_POST['save_settings'])) {
    $checkbox_fields = [
        'block_empty_ua',
        'block_empty_lang',
        'block_nojs_users',
        'block_proxy_users',
        'block_vpn_users',
        'block_tor_users',
        'block_IPv6_users',
        'block_adblocker_users',
        'get_browser_type',
        'get_os_type',
        'get_device_type',
        'check',
        'unresponsive',
        'bbcs_captcha_disable',
        'utm_referrer',
        'utm_noindex',
        'check_get_ref',
        'botblocker_log_tests',
        'botblocker_log_local',
        'botblocker_log_allow',
        'botblocker_log_fake',
        'botblocker_log_goodip',
        'botblocker_log_block',
        'noarchive',
        'iframe_stop',
        'hosting_block',
        'block_fake_ref'
    ];

    foreach ($checkbox_fields as $field) {
        $value = isset($_POST[$field]) ? '1' : '0';
        $wpdb->replace(
            $table_name,
            ['key' => $field, 'value' => $value],
            ['%s', '%s']
        );
    }

    foreach ($_POST as $key => $value) {
        if ($key !== 'save_settings' && !in_array($key, $checkbox_fields)) {
            if (is_array($value)) {
                $prepared_value = json_encode($value);
            } else {
                $prepared_value = $value;
            }
            $wpdb->replace(
                $table_name,
                ['key' => $key, 'value' => $prepared_value],
                ['%s', '%s']
            );
        }
    }
    bbcs_generateSettingsFileFromDb();
    $settings = load_settings();
}
?>

<section role="main" class="content-body">
    <form method="post" action="">
        <div class="row">
            <div class="col-md-10">
                <section class="card">
                    <header class="card-header">

                        <div class="card-actions">
                            <button type="submit" name="save_settings" value="Save Settings" class="bbcs-icon-button">
                                <i class="bbcs-card-action fa-regular fa-xl fa-floppy-disk"></i>
                            </button>
                        </div>

                        <h2 class="card-title">Settings</h2>
                    </header>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <h3 class="bbcs_settings_h3">Simple bots</h3>

                                <div class="bbcs_checkbox_input mb-2">
                                    <div class="bbcs_label_checkbox_box">
                                        <input type="checkbox" name="block_empty_ua" class="bbcs_checkbox_input_input" value="1"
                                            <?php checked(1, isset($settings['block_empty_ua']) ? $settings['block_empty_ua'] : 0); ?>>
                                        <span class="bbcs_label_input_checkbox">Block users with empty Useragent</span>
                                    </div>
                                    <i class="fa-regular fa-circle-question"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        data-bs-original-title="Block users with empty Useragent">
                                    </i>
                                </div>
                                <div class="bbcs_checkbox_input mb-2">
                                    <div class="bbcs_label_checkbox_box">
                                        <input type="checkbox" name="block_empty_lang" class="bbcs_checkbox_input_input" value="1"
                                            <?php checked(1, isset($settings['block_empty_lang']) ? $settings['block_empty_lang'] : 0); ?>>
                                        <span class="bbcs_label_input_checkbox">Block users with empty language</span>
                                    </div>
                                    <i class="fa-regular fa-circle-question"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        data-bs-original-title="Block users with empty language">
                                    </i>
                                </div>
                                <div class="bbcs_checkbox_input mb-2">
                                    <div class="bbcs_label_checkbox_box">
                                        <input type="checkbox" name="block_nojs_users" class="bbcs_checkbox_input_input" value="1"
                                            <?php checked(1, isset($settings['block_nojs_users']) ? $settings['block_nojs_users'] : 0); ?>>
                                        <span class="bbcs_label_input_checkbox">Block users without Javascript</span>
                                    </div>
                                    <i class="fa-regular fa-circle-question"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        data-bs-original-title="Block users without Javascript">
                                    </i>
                                </div>

                            </div>
                            <div class="col-md-3">
                                <h3 class="bbcs_settings_h3"> Connect types</h3>

                                <div class="bbcs_checkbox_input mb-2">
                                    <div class="bbcs_label_checkbox_box">
                                        <input type="checkbox" name="block_proxy_users" class="bbcs_checkbox_input_input" value="1"
                                            <?php checked(1, isset($settings['block_proxy_users']) ? $settings['block_proxy_users'] : 0); ?>>
                                        <span class="bbcs_label_input_checkbox">Block users with proxy</span>
                                    </div>
                                    <i class="fa-regular fa-circle-question"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        data-bs-original-title="Block users with proxy">
                                    </i>
                                </div>
                                <div class="bbcs_checkbox_input mb-2">
                                    <div class="bbcs_label_checkbox_box">
                                        <input type="checkbox" name="block_vpn_users" class="bbcs_checkbox_input_input" value="1"
                                            <?php checked(1, isset($settings['block_vpn_users']) ? $settings['block_vpn_users'] : 0); ?>>
                                        <span class="bbcs_label_input_checkbox">Block users with VPN</span>
                                    </div>
                                    <i class="fa-regular fa-circle-question"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        data-bs-original-title="Block users with VPN">
                                    </i>
                                </div>
                                <div class="bbcs_checkbox_input mb-2">
                                    <div class="bbcs_label_checkbox_box">
                                        <input type="checkbox" name="block_tor_users" class="bbcs_checkbox_input_input" value="1"
                                            <?php checked(1, isset($settings['block_tor_users']) ? $settings['block_tor_users'] : 0); ?>>
                                        <span class="bbcs_label_input_checkbox">Block users with TOR</span>
                                    </div>
                                    <i class="fa-regular fa-circle-question"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        data-bs-original-title="Block users with TOR">
                                    </i>
                                </div>
                                <div class="bbcs_checkbox_input mb-2">
                                    <div class="bbcs_label_checkbox_box">
                                        <input type="checkbox" name="block_IPv6_users" class="bbcs_checkbox_input_input" value="1"
                                            <?php checked(1, isset($settings['block_IPv6_users']) ? $settings['block_IPv6_users'] : 0); ?>>
                                        <span class="bbcs_label_input_checkbox">Block users with IPv6</span>
                                    </div>
                                    <i class="fa-regular fa-circle-question"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        data-bs-original-title="Block users with IPv6">
                                    </i>
                                </div>


                            </div>
                            <div class="col-md-3">
                                <h3 class="bbcs_settings_h3">Browser plugins</h3>

                                <div class="bbcs_checkbox_input mb-2">
                                    <div class="bbcs_label_checkbox_box">
                                        <input type="checkbox" name="block_adblocker_users" class="bbcs_checkbox_input_input" value="1"
                                            <?php checked(1, isset($settings['block_adblocker_users']) ? $settings['block_adblocker_users'] : 0); ?>>
                                        <span class="bbcs_label_input_checkbox">Block users with Adblock plugins</span>
                                    </div>
                                    <i class="fa-regular fa-circle-question"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        data-bs-original-title="Block users with Adblock plugins">
                                    </i>
                                </div>

                            </div>
                            <div class="col-md-3">
                                <h3 class="bbcs_settings_h3">Extra</h3>

                                <div class="bbcs_checkbox_input mb-2">
                                    <div class="bbcs_label_checkbox_box">
                                        <input type="checkbox" name="get_browser_type" class="bbcs_checkbox_input_input" value="1"
                                            <?php checked(1, isset($settings['get_browser_type']) ? $settings['get_browser_type'] : 0); ?>>
                                        <span class="bbcs_label_input_checkbox">Get browser type</span>
                                    </div>
                                    <i class="fa-regular fa-circle-question"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        data-bs-original-title="Determine and store the user's browser type">
                                    </i>
                                </div>

                                <div class="bbcs_checkbox_input mb-2">
                                    <div class="bbcs_label_checkbox_box">
                                        <input type="checkbox" name="get_os_type" class="bbcs_checkbox_input_input" value="1"
                                            <?php checked(1, isset($settings['get_os_type']) ? $settings['get_os_type'] : 0); ?>>
                                        <span class="bbcs_label_input_checkbox">Get OS type</span>
                                    </div>
                                    <i class="fa-regular fa-circle-question"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        data-bs-original-title="Determine and store the user's operating system type">
                                    </i>
                                </div>

                                <div class="bbcs_checkbox_input mb-2">
                                    <div class="bbcs_label_checkbox_box">
                                        <input type="checkbox" name="get_device_type" class="bbcs_checkbox_input_input" value="1"
                                            <?php checked(1, isset($settings['get_device_type']) ? $settings['get_device_type'] : 0); ?>>
                                        <span class="bbcs_label_input_checkbox">Get device type</span>
                                    </div>
                                    <i class="fa-regular fa-circle-question"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        data-bs-original-title="Determine and store the user's device type (desktop, mobile, tablet, etc.)">
                                    </i>
                                </div>

                                <div class="bbcs_text_input mb-2">
                                    <div class="bbcs_label_input_box">
                                        <span class="bbcs_label_input">Report Period:</span>
                                        <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Number of days to display statistics"></i>
                                    </div>
                                    <div class="bbcs_text_input_inner">
                                        <select class="bbcs_select_input_input" name="admin_report_period">
                                            <?php
                                            $periods = array(3, 5, 7, 10, 14, 30);
                                            foreach ($periods as $days) {
                                                $selected = (isset($settings['admin_report_period']) && $settings['admin_report_period'] == $days) ? 'selected' : '';
                                                echo "<option value=\"$days\" $selected>$days days</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="bbcs_text_input mb-2">
                                    <div class="bbcs_label_input_box">
                                        <span class="bbcs_label_input">Store Period:</span>
                                        <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Number of days to store detailed statistics"></i>
                                    </div>
                                    <div class="bbcs_text_input_inner">
                                        <select class="bbcs_select_input_input" name="admin_store_period">
                                            <?php
                                            $periods = array(3, 5, 7, 10, 14, 30);
                                            foreach ($periods as $days) {
                                                $selected = (isset($settings['admin_store_period']) && $settings['admin_store_period'] == $days) ? 'selected' : '';
                                                echo "<option value=\"$days\" $selected>$days days</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="bbcs_text_input mb-2">
                                    <div class="bbcs_label_input_box">
                                        <span class="bbcs_label_input">GMT Offset for reports:</span>
                                        <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Select the GMT offset for correct timezone in reports"></i>
                                    </div>
                                    <div class="bbcs_text_input_inner">
                                        <select class="bbcs_select_input_input" name="admin_gmt_offset">
                                            <?php
                                            $gmt_offsets = array(
                                                -12,
                                                -11,
                                                -10,
                                                -9.5,
                                                -9,
                                                -8,
                                                -7,
                                                -6,
                                                -5,
                                                -4,
                                                -3.5,
                                                -3,
                                                -2,
                                                -1,
                                                0,
                                                1,
                                                2,
                                                3,
                                                3.5,
                                                4,
                                                4.5,
                                                5,
                                                5.5,
                                                5.75,
                                                6,
                                                6.5,
                                                7,
                                                8,
                                                8.75,
                                                9,
                                                9.5,
                                                10,
                                                10.5,
                                                11,
                                                12,
                                                13,
                                                14
                                            );

                                            foreach ($gmt_offsets as $offset) {
                                                $selected = (isset($settings['admin_gmt_offset']) && $settings['admin_gmt_offset'] == $offset) ? 'selected' : '';
                                                $label = ($offset == 0) ? 'GMT' : (($offset > 0) ? "GMT+$offset" : "GMT$offset");
                                                echo "<option value=\"$offset\" $selected>$label</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </section>






                <section class="card">
                    <header class="card-header">
                        <div class="card-actions">
                            <button type="submit" name="save_settings" value="Save Settings" class="bbcs-icon-button">
                                <i class="bbcs-card-action fa-regular fa-xl fa-floppy-disk"></i>
                            </button>
                        </div>
                        <h2 class="card-title">Advanced Settings</h2>
                    </header>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <h3 class="bbcs_settings_h3">General</h3>

                                <div class="bbcs_checkbox_input mb-2">
                                    <div class="bbcs_label_checkbox_box">
                                        <input type="checkbox" name="check" class="bbcs_checkbox_input_input" value="1" <?php checked(1, isset($settings['check']) ? $settings['check'] : 0); ?>>
                                        <span class="bbcs_label_input_checkbox">Enable BotBlocker cloud check. PRO option</span>
                                    </div>
                                    <i class="fa-regular fa-circle-question"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        data-bs-original-title="Use the full power of BotBlocker to detect malicious bots">
                                    </i>
                                </div>

                                <div class="bbcs_checkbox_input mb-2">
                                    <div class="bbcs_label_checkbox_box">
                                        <input type="checkbox" name="unresponsive" value="1" <?php checked(1, isset($settings['unresponsive']) ? $settings['unresponsive'] : 1); ?>>
                                        <span class="bbcs_label_input_checkbox">Block unresponsive with cloud clients (if PRO is active)</span>
                                    </div>
                                    <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top"
                                        data-bs-original-title="Enable blocking of unresponsive clients using the BotBlocker cloud (PRO feature)">
                                    </i>
                                </div>
                                <div class="bbcs_text_input mb-2">
                                    <div class="bbcs_label_input_box">
                                        <span class="bbcs_label_input">Salt</span>
                                        <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Enter a unique salt value for added security"></i>
                                    </div>
                                    <div class="bbcs_text_input_inner">
                                        <input type="text" class="bbcs_text_input_input" name="salt" value="<?php echo isset($settings['salt']) ? $settings['salt'] : ''; ?>">
                                    </div>
                                </div>
                                <div class="bbcs_text_input mb-2">
                                    <div class="bbcs_label_input_box">
                                        <span class="bbcs_label_input">Hits per user</span>
                                        <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="The number of visits allowed for a verified user before a full re-verification"></i>
                                    </div>
                                    <div class="bbcs_text_input_inner">
                                        <input type="number" class="bbcs_text_input_input" name="hits_per_user" value="<?php echo isset($settings['hits_per_user']) ? $settings['hits_per_user'] : 500; ?>">
                                    </div>
                                </div>
                                <div class="bbcs_text_input mb-2">
                                    <div class="bbcs_label_input_box">
                                        <span class="bbcs_label_input">Ban time (seconds):</span>
                                        <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="The ban time in seconds"></i>
                                    </div>
                                    <div class="bbcs_text_input_inner">
                                        <input type="number" class="bbcs_text_input_input" name="time_ban" value="<?php echo isset($settings['time_ban']) ? $settings['time_ban'] : 200; ?>">
                                    </div>
                                </div>
                                <div class="bbcs_text_input mb-2">
                                    <div class="bbcs_label_input_box">
                                        <span class="bbcs_label_input">Secondary ban time (seconds):</span>
                                        <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="The secondary ban time in seconds"></i>
                                    </div>
                                    <div class="bbcs_text_input_inner">
                                        <input type="number" class="bbcs_text_input_input" name="time_ban_2" value="<?php echo isset($settings['time_ban_2']) ? $settings['time_ban_2'] : 400; ?>">
                                    </div>
                                </div>
                            </div> <!-- col-md-3 -->

                            <div class="col-md-3">
                                <h3 class="bbcs_settings_h3">Cookies Settings</h3>
                                <div class="bbcs_text_input mb-2">
                                    <div class="bbcs_label_input_box">
                                        <span class="bbcs_label_input">Cookie name for BotBlocker</span>
                                        <i class="fa-regular fa-circle-question"
                                            data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            data-bs-original-title="Set a unique name for the protection plugin cookie. 
                                        If you change the cookie name, all previously set cookies will be reset">
                                        </i>
                                    </div>
                                    <div class="bbcs_text_input_inner">
                                        <input type="text" class="bbcs_text_input_input" name="cookie" value="<?php echo isset($settings['cookie']) ? $settings['cookie'] : 'BotBlocker'; ?>">
                                    </div>
                                </div>
                                <div class="bbcs_text_input mb-2">
                                    <div class="bbcs_label_input_box">
                                        <span class="bbcs_label_input">Cookies SameSite:</span>
                                        <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="The SameSite attribute"></i>
                                    </div>
                                    <div class="bbcs_text_input_inner">
                                        <select class="bbcs_select_input_input" name="samesite">
                                            <option value="Lax" <?php selected('Lax', isset($settings['samesite']) ? $settings['samesite'] : 'Lax'); ?>>Lax</option>
                                            <option value="Strict" <?php selected('Strict', isset($settings['samesite']) ? $settings['samesite'] : 'Lax'); ?>>Strict</option>
                                            <option value="None" <?php selected('None', isset($settings['samesite']) ? $settings['samesite'] : 'Lax'); ?>>None</option>
                                        </select>
                                    </div>
                                </div>

                                <h3 class="bbcs_settings_h3">Traffic and Referrer Settings</h3>
                                <div class="bbcs_checkbox_input mb-2">
                                    <div class="bbcs_label_checkbox_box">
                                        <input type="checkbox" name="utm_referrer" value="1" <?php checked(1, isset($settings['utm_referrer']) ? $settings['utm_referrer'] : 1); ?>>
                                        <span class="bbcs_label_input_checkbox">Check UTM referrer</span>
                                    </div>
                                    <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Enable checking of UTM referrer">
                                    </i>
                                </div>
                                <div class="bbcs_checkbox_input mb-2">
                                    <div class="bbcs_label_checkbox_box">
                                        <input type="checkbox" name="utm_noindex" value="1" <?php checked(1, isset($settings['utm_noindex']) ? $settings['utm_noindex'] : 1); ?>>
                                        <span class="bbcs_label_input_checkbox">No-index UTM pages</span>
                                    </div>
                                    <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Enable no-indexing of UTM pages">
                                    </i>
                                </div>
                                <div class="bbcs_checkbox_input mb-2">
                                    <div class="bbcs_label_checkbox_box">
                                        <input type="checkbox" name="check_get_ref" value="1" <?php checked(1, isset($settings['check_get_ref']) ? $settings['check_get_ref'] : 1); ?>>
                                        <span class="bbcs_label_input_checkbox">Check GET referrer</span>
                                    </div>
                                    <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Enable checking of GET referrer">
                                    </i>
                                </div>
                                <div class="bbcs_number_input mb-2">
                                    <div class="bbcs_label_input_box">
                                        <span class="bbcs_label_input">PTR cache time (minutes):</span>
                                        <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="The PTR cache time in minutes"></i>
                                    </div>
                                    <div class="bbcs_number_input_inner">
                                        <input type="number" class="bbcs_number_input_input" name="ptrcache_time" value="<?php echo isset($settings['ptrcache_time']) ? $settings['ptrcache_time'] : 10; ?>">
                                    </div>
                                </div>
                            </div> <!-- col-md-3 -->

                            <div class="col-md-3">
                                <h3 class="bbcs_settings_h3">BotBlocker Captcha (BBCS)</h3>

                                <div class="bbcs_checkbox_input mb-2">
                                    <div class="bbcs_label_checkbox_box">
                                        <input type="checkbox" name="bbcs_captcha_disable" value="1" <?php checked(1, isset($settings['bbcs_captcha_disable']) ? $settings['bbcs_captcha_disable'] : 0); ?>>
                                        <span class="bbcs_label_input_checkbox">BBCS captcha disable. Unique captcha by BotBlocker</span>
                                    </div>
                                    <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Enable blocking of unresponsive clients using the BotBlocker cloud (PRO feature)">
                                    </i>
                                </div>
                                <div class="bbcs_text_input mb-2">
                                    <div class="bbcs_label_input_box">
                                        <span class="bbcs_label_input">BBCS captcha mode:</span>
                                        <i class="fa-regular fa-circle-question"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            data-bs-original-title="BBCS captcha mode"></i>
                                    </div>
                                    <div class="bbcs_text_input_inner">
                                        <select class="bbcs_select_input_input" name="bbcs_captcha_mode">
                                            <option value="0" <?php selected('0', isset($settings['bbcs_captcha_mode']) ? $settings['bbcs_captcha_mode'] : '2'); ?>>Button - "I am not robot"</option>
                                            <option value="1" <?php selected('1', isset($settings['bbcs_captcha_mode']) ? $settings['bbcs_captcha_mode'] : '2'); ?>>Color buttons</option>
                                            <option value="2" <?php selected('2', isset($settings['bbcs_captcha_mode']) ? $settings['bbcs_captcha_mode'] : '2'); ?>>BotBlocker Captcha</option>
                                            <option value="3" <?php selected('3', isset($settings['bbcs_captcha_mode']) ? $settings['bbcs_captcha_mode'] : '2'); ?>>ReCAPTCHA v2 and "I am not robot"</option>
                                            <option value="4" <?php selected('4', isset($settings['bbcs_captcha_mode']) ? $settings['bbcs_captcha_mode'] : '2'); ?>>ReCAPTCHA v2</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="bbcs_text_input mb-2">
                                    <div class="bbcs_label_input_box">
                                        <span class="bbcs_label_input">BBCS Captcha Image pack:</span>
                                        <i class="fa-regular fa-circle-question"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            data-bs-original-title="The SameSite attribute"></i>
                                    </div>
                                    <div class="bbcs_text_input_inner">
                                        <select class="bbcs_select_input_input" name="bbcs_captcha_img_pack">
                                            <option value="1" <?php selected('1', isset($settings['bbcs_captcha_img_pack']) ? $settings['bbcs_captcha_img_pack'] : '1'); ?>>Eagle</option>
                                            <option value="2" <?php selected('2', isset($settings['bbcs_captcha_img_pack']) ? $settings['bbcs_captcha_img_pack'] : '1'); ?>>Horse</option>
                                            <option value="3" <?php selected('3', isset($settings['bbcs_captcha_img_pack']) ? $settings['bbcs_captcha_img_pack'] : '1'); ?>>Racoon</option>
                                            <option value="4" <?php selected('4', isset($settings['bbcs_captcha_img_pack']) ? $settings['bbcs_captcha_img_pack'] : '1'); ?>>Dog</option>
                                            <option value="5" <?php selected('5', isset($settings['bbcs_captcha_img_pack']) ? $settings['bbcs_captcha_img_pack'] : '1'); ?>>Cat</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="bbcs_number_input mb-2">
                                    <div class="bbcs_label_input_box">
                                        <span class="bbcs_label_input">BBCS captcha wait:</span>
                                        <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top"
                                            data-bs-original-title="BBCS captcha wait"></i>
                                    </div>
                                    <div class="bbcs_number_input_inner">
                                        <input type="number" class="bbcs_number_input_input" name="bbcs_captcha_wait" value="<?php echo isset($settings['bbcs_captcha_wait']) ? $settings['bbcs_captcha_wait'] : 15; ?>">
                                    </div>
                                </div>





                                <h3 class="bbcs_settings_h3">Logging Settings</h3>
                                <div class="bbcs_checkbox_input mb-2">
                                    <div class="bbcs_label_checkbox_box">
                                        <input type="checkbox" name="botblocker_log_tests" value="1" <?php checked(1, isset($settings['botblocker_log_tests']) ? $settings['botblocker_log_tests'] : 1); ?>>
                                        <span class="bbcs_label_input_checkbox">Log test visitors</span>
                                    </div>
                                    <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Enable logging of test visitors">
                                    </i>
                                </div>
                                <div class="bbcs_checkbox_input mb-2">
                                    <div class="bbcs_label_checkbox_box">
                                        <input type="checkbox" name="botblocker_log_local" value="1" <?php checked(1, isset($settings['botblocker_log_local']) ? $settings['botblocker_log_local'] : 1); ?>>
                                        <span class="bbcs_label_input_checkbox">Log local visitors</span>
                                    </div>
                                    <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Enable logging of local visitors">
                                    </i>
                                </div>
                                <div class="bbcs_checkbox_input mb-2">
                                    <div class="bbcs_label_checkbox_box">
                                        <input type="checkbox" name="botblocker_log_allow" value="1" <?php checked(1, isset($settings['botblocker_log_allow']) ? $settings['botblocker_log_allow'] : 1); ?>>
                                        <span class="bbcs_label_input_checkbox">Log allowed visitors</span>
                                    </div>
                                    <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Enable logging of allowed visitors">
                                    </i>
                                </div>
                                <div class="bbcs_checkbox_input mb-2">
                                    <div class="bbcs_label_checkbox_box">
                                        <input type="checkbox" name="botblocker_log_fake" value="1" <?php checked(1, isset($settings['botblocker_log_fake']) ? $settings['botblocker_log_fake'] : 1); ?>>
                                        <span class="bbcs_label_input_checkbox">Log fake bots</span>
                                    </div>
                                    <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Enable logging of fake bots">
                                    </i>
                                </div>
                                <div class="bbcs_checkbox_input mb-2">
                                    <div class="bbcs_label_checkbox_box">
                                        <input type="checkbox" name="botblocker_log_goodip" value="1" <?php checked(1, isset($settings['botblocker_log_goodip']) ? $settings['botblocker_log_goodip'] : 1); ?>>
                                        <span class="bbcs_label_input_checkbox">Log good IPs</span>
                                    </div>
                                    <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Enable logging of good IPs">
                                    </i>
                                </div>
                                <div class="bbcs_checkbox_input mb-2">
                                    <div class="bbcs_label_checkbox_box">
                                        <input type="checkbox" name="botblocker_log_block" value="1" <?php checked(1, isset($settings['botblocker_log_block']) ? $settings['botblocker_log_block'] : 1); ?>>
                                        <span class="bbcs_label_input_checkbox">Log blocked visitors</span>
                                    </div>
                                    <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Enable logging of blocked visitors">
                                    </i>
                                </div>
                            </div> <!-- col-md-3 -->

                            <div class="col-md-3">
                                <h3 class="bbcs_settings_h3">Error and Access Settings</h3>

                                <div class="bbcs_text_input mb-2">
                                    <div class="bbcs_label_input_box">
                                        <span class="bbcs_label_input">Test header code:</span>
                                        <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="The test header code"></i>
                                    </div>
                                    <div class="bbcs_text_input_inner">
                                        <select class="bbcs_select_input_input" name="header_test_code">
                                            <?php
                                            foreach ($BBCS['error_headers'] as $code => $description) {
                                                $selected = (isset($settings['header_test_code']) && $settings['header_test_code'] == $code) ? 'selected' : '';
                                                echo "<option value=\"$code\" $selected>$description</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="bbcs_text_input mb-2">
                                    <div class="bbcs_label_input_box">
                                        <span class="bbcs_label_input">Error header code:</span>
                                        <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="The error header code"></i>
                                    </div>
                                    <div class="bbcs_text_input_inner">
                                        <select class="bbcs_select_input_input" name="header_error_code">
                                            <?php
                                            foreach ($BBCS['error_headers'] as $code => $description) {
                                                $selected = (isset($settings['header_error_code']) && $settings['header_error_code'] == $code) ? 'selected' : '';
                                                echo "<option value=\"$code\" $selected>$description</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="bbcs_checkbox_input mb-2">
                                    <div class="bbcs_label_checkbox_box">
                                        <input type="checkbox" name="noarchive" value="1" <?php checked(1, isset($settings['noarchive']) ? $settings['noarchive'] : 0); ?>>
                                        <span class="bbcs_label_input_checkbox">No-archive for blocked pages</span>
                                    </div>
                                    <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Enable no-archive for blocked pages">
                                    </i>
                                </div>
                                <div class="bbcs_text_input mb-2">
                                    <div class="bbcs_label_input_box">
                                        <span class="bbcs_label_input">Last rule:</span>
                                        <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="The last rule"></i>
                                    </div>
                                    <div class="bbcs_text_input_inner">
                                        <input type="text" class="bbcs_text_input_input" name="last_rule" value="<?php echo isset($settings['last_rule']) ? $settings['last_rule'] : ''; ?>">
                                    </div>
                                </div>
                                <div class="bbcs_checkbox_input mb-2">
                                    <div class="bbcs_label_checkbox_box">
                                        <input type="checkbox" name="iframe_stop" value="1" <?php checked(1, isset($settings['iframe_stop']) ? $settings['iframe_stop'] : 0); ?>>
                                        <span class="bbcs_label_input_checkbox">Stop iframe loading</span>
                                    </div>
                                    <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Enable stop iframe loading">
                                    </i>
                                </div>
                                <div class="bbcs_checkbox_input mb-2">
                                    <div class="bbcs_label_checkbox_box">
                                        <input type="checkbox" name="hosting_block" value="1" <?php checked(1, isset($settings['hosting_block']) ? $settings['hosting_block'] : 0); ?>>
                                        <span class="bbcs_label_input_checkbox">Block hosting IPs</span>
                                    </div>
                                    <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Enable block hosting IPs">
                                    </i>
                                </div>
                                <div class="bbcs_checkbox_input mb-2">
                                    <div class="bbcs_label_checkbox_box">
                                        <input type="checkbox" name="block_fake_ref" value="1" <?php checked(1, isset($settings['block_fake_ref']) ? $settings['block_fake_ref'] : 1); ?>>
                                        <span class="bbcs_label_input_checkbox">Block fake referrers</span>
                                    </div>
                                    <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Enable block fake referrers">
                                    </i>
                                </div>
                            </div> <!-- col-md-3 -->
                        </div> <!-- row -->
                        <div class="row">
                            <!-- <input type="submit" name="save_settings" value="Save Settings" class="btn btn-primary">-->
                        </div> <!-- row -->
                    </div> <!-- card-body -->
                </section> <!-- card -->
            </div> <!-- col-md-10 -->

            <div class="col-md-2">
                <?php include('botblocker-section-right-sidebar.php'); ?>
            </div>
        </div>
    </form>
</section>