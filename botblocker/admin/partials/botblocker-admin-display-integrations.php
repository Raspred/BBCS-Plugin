<?php
// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC') || !defined('BOTBLOCKER')) {
    exit;
}


include('botblocker-section-header.php');

global $wpdb;
$table_name = $wpdb->prefix . 'bbcs_settings';

// Загрузка настроек
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

// Обработка сохранения настроек
if (isset($_POST['save_settings'])) {
    $checkbox_fields = [
        'recaptcha_check',
        'memcached_counter'
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
                        <h2 class="card-title">Integration</h2>
                    </header>
                    <div class="card-body">
                        <form method="post" action="">
                            <div class="row">
                                <div class="col-md-3">
                                    <h3 class="bbcs_settings_h3">reCAPTCHA v2</h3>
                                    <div class="bbcs_text_input mb-2">
                                        <div class="bbcs_label_input_box">
                                            <span class="bbcs_label_input">reCAPTCHA v2 Site Key:</span>
                                            <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="The reCAPTCHA v2 Site Key"></i>
                                        </div>
                                        <div class="bbcs_text_input_inner">
                                            <input type="text" class="bbcs_text_input_input" name="recaptcha_key2" value="<?php echo isset($settings['recaptcha_key2']) ? $settings['recaptcha_key2'] : '6LdNE9IZAAAAANZhNB70M9rdJFhUeZP9WIEuPjwL'; ?>">
                                        </div>
                                    </div>
                                    <div class="bbcs_text_input mb-2">
                                        <div class="bbcs_label_input_box">
                                            <span class="bbcs_label_input">reCAPTCHA v2 Secret Key:</span>
                                            <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="The reCAPTCHA v2 Secret Key"></i>
                                        </div>
                                        <div class="bbcs_text_input_inner">
                                            <input type="text" class="bbcs_text_input_input" name="recaptcha_secret2" value="<?php echo isset($settings['recaptcha_secret2']) ? $settings['recaptcha_secret2'] : '6LdNE9IZAAAAACkkzzx-WZ66rkP8WC3QaV7bTPB3'; ?>">
                                        </div>
                                    </div>

                                    <h3 class="bbcs_settings_h3">reCAPTCHA v3</h3>

                                    <div class="bbcs_checkbox_input mb-2">
                                        <div class="bbcs_label_checkbox_box">
                                            <input type="checkbox" name="re_check" value="1" <?php checked(1, isset($settings['recaptcha_check']) ? $settings['recaptcha_check'] : 1); ?>>
                                            <span class="bbcs_label_input_checkbox">Enable reCAPTCHA v3 checking</span>
                                        </div>
                                        <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Enable reCAPTCHA v3 checking">
                                        </i>
                                    </div>

                                    <div class="bbcs_text_input mb-2">
                                        <div class="bbcs_label_input_box">
                                            <span class="bbcs_label_input">reCAPTCHA v3 Site Key:</span>
                                            <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="The reCAPTCHA v3 Site Key"></i>
                                        </div>
                                        <div class="bbcs_text_input_inner">
                                            <input type="text" class="bbcs_text_input_input" name="recaptcha_key3" value="<?php echo isset($settings['recaptcha_key3']) ? $settings['recaptcha_key3'] : '6LdzJvcpAAAAAOHUj2rnfmpa_ecqiWNW0jDIOtLl'; ?>">
                                        </div>
                                    </div>



                                    <div class="bbcs_text_input mb-2">
                                        <div class="bbcs_label_input_box">
                                            <span class="bbcs_label_input">reCAPTCHA v3 Secret Key:</span>
                                            <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="The reCAPTCHA v3 Secret Key"></i>
                                        </div>
                                        <div class="bbcs_text_input_inner">
                                            <input type="text" class="bbcs_text_input_input" name="recaptcha_secret3" value="<?php echo isset($settings['recaptcha_secret3']) ? $settings['recaptcha_secret3'] : '6LdzJvcpAAAAAFH7vb9wnXeTasTSge1krB6GUJLm'; ?>">
                                        </div>
                                    </div>


                                    <br>
                                    <!--<input type="submit" name="save_settings" value="Save Settings" class="btn btn-primary">-->

                                </div>

                                <div class="col-md-3">
                                    <h3 class="bbcs_settings_h3">Memcached</h3>

                                    <div class="bbcs_checkbox_input mb-2">
                                        <div class="bbcs_label_checkbox_box">
                                            <input type="checkbox" name="memcached_counter" value="1" <?php checked(1, isset($settings['memcached_counter']) ? $settings['memcached_counter'] : 1); ?>>
                                            <span class="bbcs_label_input_checkbox">Enable memcached counters</span>
                                        </div>
                                        <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Enable memcached counters">
                                        </i>
                                    </div>


                                    <div class="bbcs_text_input mb-2">
                                        <div class="bbcs_label_input_box">
                                            <span class="bbcs_label_input">Memcached host:</span>
                                            <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="The Memcached host"></i>
                                        </div>
                                        <div class="bbcs_text_input_inner">
                                            <input type="text" class="bbcs_text_input_input" name="memcached_host" value="<?php echo isset($settings['memcached_host']) ? $settings['memcached_host'] : '127.0.0.1'; ?>">
                                        </div>
                                    </div>



                                    <div class="bbcs_text_input mb-2">
                                        <div class="bbcs_label_input_box">
                                            <span class="bbcs_label_input">Memcached port:</span>
                                            <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="The Memcached port"></i>
                                        </div>
                                        <div class="bbcs_text_input_inner">
                                            <input type="number" class="bbcs_number_input_input" name="memcached_port" value="<?php echo isset($settings['memcached_port']) ? $settings['memcached_port'] : 11211; ?>">
                                        </div>
                                    </div>



                                    <div class="bbcs_text_input mb-2">
                                        <div class="bbcs_label_input_box">
                                            <span class="bbcs_label_input">Memcached prefix:</span>
                                            <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="The Memcached prefix"></i>
                                        </div>
                                        <div class="bbcs_text_input_inner">
                                            <input type="text" class="bbcs_text_input_input" name="memcached_prefix" value="<?php echo isset($settings['memcached_prefix']) ? $settings['memcached_prefix'] : BOTBLOCKER_PREFIX; ?>">
                                        </div>
                                    </div>


                                </div>

                                <div class="col-md-3">
                                    <h3 class="bbcs_settings_h3">Redis</h3>

                                    <div class="bbcs_text_input mb-2">
                                        <div class="bbcs_label_input_box">
                                            <span class="bbcs_label_input">redis_host:</span>
                                            <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top"
                                                data-bs-original-title="redis_host"></i>
                                        </div>
                                        <div class="bbcs_text_input_inner">
                                            <input type="text" class="bbcs_text_input_input" name="redis_host" value="<?php echo isset($settings['redis_host']) ? $settings['redis_host'] : ''; ?>">
                                        </div>
                                    </div>


                                    <div class="bbcs_text_input mb-2">
                                        <div class="bbcs_label_input_box">
                                            <span class="bbcs_label_input">redis_prefix:</span>
                                            <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top"
                                                data-bs-original-title="redis_prefix"></i>
                                        </div>
                                        <div class="bbcs_text_input_inner">
                                            <input type="text" class="bbcs_text_input_input" name="redis_prefix" value="<?php echo isset($settings['redis_prefix']) ? $settings['redis_prefix'] : ''; ?>">
                                        </div>
                                    </div>


                                    <div class="bbcs_text_input mb-2">
                                        <div class="bbcs_label_input_box">
                                            <span class="bbcs_label_input">redis_password:</span>
                                            <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top"
                                                data-bs-original-title="redis_password"></i>
                                        </div>
                                        <div class="bbcs_text_input_inner">
                                            <input type="text" class="bbcs_text_input_input" name="redis_password" value="<?php echo isset($settings['redis_password']) ? $settings['redis_password'] : ''; ?>">
                                        </div>
                                    </div>


                                    <div class="bbcs_text_input mb-2">
                                        <div class="bbcs_label_input_box">
                                            <span class="bbcs_label_input">redis_port:</span>
                                            <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top"
                                                data-bs-original-title="redis_port"></i>
                                        </div>
                                        <div class="bbcs_text_input_inner">
                                            <input type="number" class="bbcs_number_input_input" name="redis_port" value="<?php echo isset($settings['redis_port']) ? $settings['redis_port'] : 0; ?>">
                                        </div>
                                    </div>


                                    <div class="bbcs_text_input mb-2">
                                        <div class="bbcs_label_input_box">
                                            <span class="bbcs_label_input">redis_db:</span>
                                            <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top"
                                                data-bs-original-title="redis_db"></i>
                                        </div>
                                        <div class="bbcs_text_input_inner">
                                            <input type="number" class="bbcs_number_input_input" name="redis_db" value="<?php echo isset($settings['redis_db']) ? $settings['redis_db'] : 0; ?>">
                                        </div>
                                    </div>



                                </div>

                                <div class="col-md-3">
                                    <h3 class="bbcs_settings_h3">BotBlocker API</h3>

                                    <div class="bbcs_text_input mb-2">
                                        <div class="bbcs_label_input_box">
                                            <span class="bbcs_label_input">BotBlocker API URL:</span>
                                            <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top"
                                                data-bs-original-title="BotBlocker API URL"></i>
                                        </div>
                                        <div class="bbcs_text_input_inner">
                                            <input type="text" class="bbcs_text_input_input" name="bbcs_api_url" value="<?php echo isset($settings['bbcs_api_url']) ? $settings['bbcs_api_url'] : ''; ?>">
                                        </div>
                                    </div>

                                    <!-- Reason: deprecated

                                    <div class="bbcs_text_input mb-2">
                                        <div class="bbcs_label_input_box">
                                            <span class="bbcs_label_input">BotBlocker API Key:</span>
                                            <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top"
                                                data-bs-original-title="BotBlocker API Key"></i>
                                        </div>
                                        <div class="bbcs_text_input_inner">
                                            <input type="text" class="bbcs_text_input_input" name="bbcs_api_key" value="<?php echo isset($settings['bbcs_api_key']) ? $settings['bbcs_api_key'] : ''; ?>">
                                        </div>
                                    </div>

                                    <div class="bbcs_text_input mb-2">
                                        <div class="bbcs_label_input_box">
                                            <span class="bbcs_label_input">BotBlocker API Secret:</span>
                                            <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top"
                                                data-bs-original-title="BotBlocker API Secret"></i>
                                        </div>
                                        <div class="bbcs_text_input_inner">
                                            <input type="text" class="bbcs_text_input_input" name="bbcs_api_secret" value="<?php echo isset($settings['bbcs_api_secret']) ? $settings['bbcs_api_secret'] : ''; ?>">
                                        </div>
                                    </div>
                                    -->

                                    <div class="bbcs_text_input mb-2">
                                        <div class="bbcs_label_input_box">
                                            <span class="bbcs_label_input">GLOBUS.studio API URL:</span>
                                            <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top"
                                                data-bs-original-title="GLOBUS.studio API URL"></i>
                                        </div>
                                        <div class="bbcs_text_input_inner">
                                            <input type="text" class="bbcs_text_input_input" name="bbcs_api_gs_url" value="<?php echo isset($settings['bbcs_api_gs_url']) ? $settings['bbcs_api_gs_url'] : ''; ?>">
                                        </div>
                                    </div>

                                    <!-- Reason: deprecated

                                    <div class="bbcs_text_input mb-2">
                                        <div class="bbcs_label_input_box">
                                            <span class="bbcs_label_input">GLOBUS.studio API Key:</span>
                                            <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top"
                                                data-bs-original-title="GLOBUS.studio API Key"></i>
                                        </div>
                                        <div class="bbcs_text_input_inner">
                                            <input type="text" class="bbcs_text_input_input" name="bbcs_api_gs_key" value="<?php  echo isset($settings['bbcs_api_gs_key']) ? $settings['bbcs_api_gs_key'] : ''; ?>">
                                        </div>
                                    </div>

                                    <div class="bbcs_text_input mb-2">
                                        <div class="bbcs_label_input_box">
                                            <span class="bbcs_label_input">GLOBUS.studio API Secret:</span>
                                            <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top"
                                                data-bs-original-title="GLOBUS.studio API Secret"></i>
                                        </div>
                                        <div class="bbcs_text_input_inner">
                                            <input type="text" class="bbcs_text_input_input" name="bbcs_api_gs_secret" value="<?php echo isset($settings['bbcs_api_gs_secret']) ? $settings['bbcs_api_gs_secret'] : ''; ?>">
                                        </div>
                                    </div>

                                    -->

                                </div>

                            </div>
                        </form>
                    </div>
                </section>
            </div>

            <div class="col-md-2">
                <?php include('botblocker-section-right-sidebar.php'); ?>
            </div>
    </form>
    </div>
</section>