<?php 
// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC') || !defined('BOTBLOCKER')) {
	exit;
}

if ( defined( 'BOTBLOCKER_PRO' ) && BOTBLOCKER_PRO ) {
    $bbcs_pro_instance = new Cyber_Secure_Botblocker_PRO();
    $test = $bbcs_pro_instance->bbcs_helloPro();
}


/**
 * Get the license type from the database
 *
 * @return string The license type
 */
function bbcs_getLicenseType()
{
    global $wpdb;
    $table_name_settings = $wpdb->prefix . 'bbcs_settings';

    $license_type = $wpdb->get_var($wpdb->prepare("SELECT value FROM $table_name_settings WHERE `key` = %s", 'license'));

    if (!empty($license_type)) {
        return $license_type;
    } else {
        return 'Unknown';
    }
}


/**
 * Get the license key from the database
 *
 * @return string The license key
 */
function bbcs_getLicenseKey()
{
    global $wpdb;
    $table_name_settings = $wpdb->prefix . 'bbcs_settings';

    $license_key = $wpdb->get_var($wpdb->prepare("SELECT value FROM $table_name_settings WHERE `key` = %s", 'license_key'));

    if (!empty($license_key)) {
        return $license_key;
    } else {
        return 'Unknown';
    }
}

/**
 * Get the license secret from the database
 *
 * @return string The license secret
 */
function bbcs_getLicenseSecret()
{
    global $wpdb;
    $table_name_settings = $wpdb->prefix . 'bbcs_settings';

    $license_secret = $wpdb->get_var($wpdb->prepare("SELECT value FROM $table_name_settings WHERE `key` = %s", 'license_secret'));

    if (!empty($license_secret)) {
        return $license_secret;
    } else {
        return 'Unknown';
    }
}

function bbcs_generatePriceList()
{
    $license_endpoint = constant('BOTBLOCKER_LICENSE_ENDPOINT');
    $domain = constant('BOTBLOCKER_SITE_URL');
    $api_key = md5(constant('BOTBLOCKER_SITE_NAME'));
    $email = wp_get_current_user()->user_email;

    $url = 'https://cybersecure.top/botblocker_get_products/';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($response, true);

    $output = '';

    if ($data['success'] === true) {
        $total_products = count($data['data']);
        $counter = 0;

        foreach ($data['data'] as $product) {
            $counter++;
            $product_id = $product['product_id'];
            $name = $product['name'];
            $license_duration = $product['license_duration'];
            $max_requests = $product['max_requests'];
            $price = $product['price'];
            $buy_link = $license_endpoint . '?domain=' . $domain . '&api_key=' . $api_key . '&email=' . $email . '&tariff=' . $product_id;

            $card_class = ($counter === $total_products) ? 'card bbcs-price-item' : 'card bbcs-price-item me-1';

            $output .= '
            <section class="' . $card_class . '">
                <div class="card-body bbcs-card-body">
                    <!--<span class="bbcs-badge">Free</span>-->
                    <h2 class="bbcs-price">$' . htmlspecialchars($price) . ' <span class="bbcs-price-duration">/ mo</span></h2>
                    <p class="bbcs-description">' . htmlspecialchars($name) . '</p>
                    <hr class="bbcs-divider">
                    <ul class="bbcs-features">
                        <li>Period: ' . htmlspecialchars($license_duration) . ' days</li>
                        <li>Cloud requests: ' . htmlspecialchars(number_format($max_requests)) . '</li>
                    </ul>
                    <a href="' . htmlspecialchars($buy_link) . '" class="bbcs-btn-primary" target="_blank">Try for Free</a>
                    <!--<p class="bbcs-footer-text">Can be upgraded.</p>-->
                </div>
            </section>';
        }
    } else {
        $output = '<p>Error: Could not fetch product data.</p>';
    }

    return $output;
}

function bbcs_handleBotblockerLicense()
{
    add_action('template_redirect', function () {
        if (get_query_var('botblocker_license') == '1') {
            if (isset($_GET['email']) && isset($_GET['api_key']) && isset($_GET['api_secret'])) {
                $email = sanitize_text_field($_GET['email']);
                $api_key = sanitize_text_field($_GET['api_key']);
                $api_secret = sanitize_text_field($_GET['api_secret']);

                global $wpdb;
                $table_name_settings = $wpdb->prefix . 'bbcs_settings';

                $wpdb->update(
                    $table_name_settings,
                    array('value' => 'PRO'),
                    array('key' => 'license')
                );

                $wpdb->update(
                    $table_name_settings,
                    array('value' => $api_key),
                    array('key' => 'license_key')
                );

                $wpdb->update(
                    $table_name_settings,
                    array('value' => $api_secret),
                    array('key' => 'license_secret')
                );

                // SAVE FILES
                bbcs_generateSettingsFileFromDb();

                wp_send_json_success(array('message' => 'License activated successfully'));
            } else {
                wp_die('Invalid or missing parameters', 'Error', array('response' => 400));
            }
        }
    });
}



/**
 * Generate a license key based on the given parameters
 *
 * @param string $series The series of the license key (Metric, BotBlocker, ShieldWP)
 * @param string $email The user's email address
 * @return string The generated license key
 */
function bbcs_generateUuid()
{
    return sprintf(
        '%04x-%04x-%04x',
        random_int(0, 0xffff),
        random_int(0, 0xffff),
        random_int(0, 0xffff)
    );
};
function bbcs_generateLicenseKey($series, $email)
{
    $seriesMap = [
        'Metric'     => '1M',
        'BotBlocker' => '1B',
        'ShieldWP'   => '1S'
    ];

    $series = $seriesMap[$series] ?? '0X';

    $serial = strtoupper(substr(md5($email), 0, 6));
    $key = bbcs_generateUuid();
    $checksum = substr(md5($series . $key . $serial), 0, 2);

    return "{$series}-{$key}-{$serial}-{$checksum}";
};

