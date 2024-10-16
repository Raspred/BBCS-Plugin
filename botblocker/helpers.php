<?php
// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC') || !defined('BOTBLOCKER')) {
    exit;
}

include_once BOTBLOCKER_DIR . 'includes/inc-botblocker-env.php';
include_once BOTBLOCKER_DIR . 'includes/inc-botblocker-ajax.php';
include_once BOTBLOCKER_DIR . 'includes/inc-botblocker-hook.php';
include_once BOTBLOCKER_DIR . 'includes/inc-botblocker-shortcode.php';
include_once BOTBLOCKER_DIR . 'includes/inc-botblocker-tools.php';
include_once BOTBLOCKER_DIR . 'includes/inc-botblocker-pro.php';

/**
 * Custom translation function
 *
 * @param string $current_phrase The phrase to translate
 * @return string The translated phrase
 */
function bbcs_customTranslate($current_phrase)
{
    global $pt;
    return isset($pt[$current_phrase]) ? $pt[$current_phrase] : $current_phrase;
}

/**
 * Expand a shortened IPv6 address to the full format
 *
 * @param string $ip The shortened IPv6 address
 * @return string The full format of the IPv6 address
 */
function bbcs_expandIPv6($ip)
{
    $hex = unpack("H*hex", inet_pton($ip));
    $ip = substr(preg_replace("/([A-f0-9]{4})/", "$1:", $hex['hex']), 0, -1);
    return $ip;
}

/**
 * Convert an IP address to a numeric format
 *
 * @param string $ip The IP address
 * @return int|string The numeric format of the IP address
 */
function bbcs_ipToNumeric($ip)
{
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        return ip2long($ip);
    } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        return (string) bbcs_gmp_import(inet_pton($ip));
    } else {
        return 0;
    }
}

/**
 * Import a number into GMP from a binary string
 *
 * @param string $data The binary string
 * @param int $word_size The size of the word
 * @param int $options The options for the import
 * @return resource The GMP number
 */
function bbcs_custom_gmp_import($data, $word_size = 1, $options = GMP_MSW_FIRST | GMP_BIG_ENDIAN)
{
    if ($word_size != 1) {
        throw new Exception("Unsupported word size: $word_size");
    }

    $value = 0;
    $length = strlen($data);

    if ($options & GMP_MSW_FIRST) {
        if ($options & GMP_BIG_ENDIAN) {
            // MSB first
            for ($i = 0; $i < $length; $i++) {
                $value = ($value << 8) | ord($data[$i]);
            }
        } else {
            // MSB last
            for ($i = $length - 1; $i >= 0; $i--) {
                $value = ($value << 8) | ord($data[$i]);
            }
        }
    } else {
        if ($options & GMP_BIG_ENDIAN) {
            // LSB first
            for ($i = 0; $i < $length; $i++) {
                $value = ($value << 8) | ord($data[$length - 1 - $i]);
            }
        } else {
            // LSB last
            for ($i = $length - 1; $i >= 0; $i--) {
                $value = ($value << 8) | ord($data[$length - 1 - $i]);
            }
        }
    }

    return $value;
}

/**
 * Import a number into GMP from a binary string
 *
 * @param string $data The binary string
 * @return resource The GMP number
 */
function bbcs_gmp_import($data)
{
    if (extension_loaded('gmp')) {
        return gmp_import($data);
    } else {
        return bbcs_custom_gmp_import($data);
    }
}

/**
 * Get the PTR record of an IP address with caching in the database
 *
 * @param string $ip The IP address
 * @param int $time The current time
 * @return string The PTR record of the IP address
 */
function bbcs_getPTR($ip, $time)
{

    //TODO: Redis store
    //TODO: MMC store

    global $wpdb;
    $table_name_ptrcache = $wpdb->prefix . 'bbcs_ptrcache';
    $get_ptr = $wpdb->get_row($wpdb->prepare("SELECT ptr, date FROM $table_name_ptrcache WHERE ip = %s", $ip), ARRAY_A);
    if (isset($get_ptr['ptr'])) {
        return $get_ptr['ptr'];
    } else {
        $start_time = microtime(true);
        $ptr = trim(preg_replace("/[^0-9a-z-.:]/", "", mb_strtolower(gethostbyaddr($ip), 'UTF-8')));
        $exec_time = round(microtime(true) - $start_time, 3);
        $add = $wpdb->insert($table_name_ptrcache, array('ip' => $ip, 'ptr' => $ptr, 'date' => $time, 'etime' => $exec_time));
        return $ptr;
    }
}

/**
 * Check if the IP address is a white bot
 *
 * @param string $ip The IP address
 * @param array $ptr_ok The PTR records of the white bots
 * @param int $time The current time
 * @return bool Whether the IP address is a white bot
 */
function bbcs_testWhiteBot($ip, $ptr_ok, $time)
{
    // $ptr_ok - массив
    if (in_array('.', $ptr_ok)) {
        return 1;
    } else {
        $ptr = bbcs_getPTR($ip, $time); // получаем ptr хост по ip
        if ($ptr === false) {
            $result = array();
        } else {
            $result = @dns_get_record($ptr, DNS_A + DNS_AAAA); // ipv4 & ipv6 у ptr хоста
            if (!is_array($result)) {
                $result = array();
            }
        }
        $ip2 = array(); // массив всех IP принадлежащих PTR хосту
        if ($ptr == $ip) $ip2[] = $ip;
        foreach ($result as $line) {
            if (isset($line['ipv6'])) {
                $ip2[] = bbcs_expandIPv6($line['ipv6']);
            }
            if (isset($line['ip'])) {
                $ip2[] = $line['ip'];
            }
        }
        $test_ptr = 0;
        foreach ($ptr_ok as $ptr_line) {
            if ($ptr_line == '.') {
                $test_ptr = 1;
                break;
            }
            if (stripos($ptr, $ptr_line, 0) !== false) {
                $test_ptr = 1;
                break;
            }
        }
        if (in_array($ip, $ip2) and $test_ptr == 1) {
            return 1;
        } else {
            return 0;
        }
    }
}

/**
 * Check if the IP address is in the specified network
 *
 * @param string $network The network in CIDR notation
 * @param string $ip The IP address
 * @return bool Whether the IP address is in the network
 */
function bbcs_netMatch($network, $ip)
{
    $ip_arr = explode('/', $network);
    $network_long = ip2long($ip_arr[0]);
    $x = ip2long($ip_arr[1]);
    $mask =  long2ip($x) == $ip_arr[1] ? $x : 0xffffffff << (32 - $ip_arr[1]);
    $ip_long = ip2long($ip);
    return ($ip_long & $mask) == ($network_long & $mask);
}

/**
 * Get the IP range from the CIDR notation
 *
 * @param string $cidr The CIDR notation
 * @return array The IP range
 */
function bbcs_IpRange($cidr)
{
    $range = array();
    $cidr = explode('/', trim($cidr));
    if (!isset($cidr[1])) {
        $range = array(0, 0, 0); // $range[2] = error
    } elseif (filter_var($cidr[0], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        $range[0] = long2ip((ip2long($cidr[0])) & ((-1 << (32 - (int)$cidr[1]))));
        $range[1] = long2ip((ip2long($range[0])) + pow(2, (32 - (int)$cidr[1])) - 1);
    } elseif (filter_var($cidr[0], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        // Split in address and prefix length
        $addr_given_str = $cidr[0];
        $prefixlen = $cidr[1];
        // Parse the address into a binary string
        $addr_given_bin = inet_pton($addr_given_str);
        // Convert the binary string to a string with hexadecimal characters
        $addr_given_hex = bin2hex($addr_given_bin);
        // Overwriting first address string to make sure notation is optimal
        $addr_given_str = inet_ntop($addr_given_bin);
        // Calculate the number of 'flexible' bits
        $flexbits = 128 - $prefixlen;
        // Build the hexadecimal strings of the first and last addresses
        $addr_hex_first = $addr_given_hex;
        $addr_hex_last = $addr_given_hex;
        // We start at the end of the string (which is always 32 characters long)
        $pos = 31;
        while ($flexbits > 0) {
            // Get the characters at this position
            $orig_first = substr($addr_hex_first, $pos, 1);
            $orig_last = substr($addr_hex_last, $pos, 1);
            // Convert them to an integer
            $origval_first = hexdec($orig_first);
            $origval_last = hexdec($orig_last);
            // First address: calculate the subnet mask. min() prevents the comparison from being negative
            $mask = 0xf << (min(4, $flexbits));
            // AND the original against its mask
            $new_val_first = $origval_first & $mask;
            // Last address: OR it with (2^flexbits)-1, with flexbits limited to 4 at a time
            $new_val_last = $origval_last | (pow(2, min(4, $flexbits)) - 1);
            // Convert them back to hexadecimal characters
            $new_first = dechex($new_val_first);
            $new_last = dechex($new_val_last);
            // And put those character back in their strings
            $addr_hex_first = substr_replace($addr_hex_first, $new_first, $pos, 1);
            $addr_hex_last = substr_replace($addr_hex_last, $new_last, $pos, 1);
            // We processed one nibble, move to previous position
            $flexbits -= 4;
            $pos -= 1;
        }
        // Convert the hexadecimal strings to a binary string
        $addr_bin_first = hex2bin($addr_hex_first);
        $addr_bin_last = hex2bin($addr_hex_last);
        // And create an IPv6 address from the binary string
        $range[0] = inet_ntop($addr_bin_first);
        $range[1] = inet_ntop($addr_bin_last);
    } else {
        $range = array(0, 0, 0); // $range[2] = error
    }
    return $range;
}

/**
 * Set a cookie with the specified parameters
 *
 * @param string $name The name of the cookie
 * @param string $value The value of the cookie
 * @param int $expires The expiration time of the cookie
 * @param bool $dot Whether to include the domain in the cookie
 */
function bbcs_setcookie($name, $value, $expires, $dot, $samesite)
{
    $samesites = array('Lax', 'Strict', 'None');
    if (!in_array($samesite, $samesites)) {
        $samesite = 'None';
    }
    if (!headers_sent()) {
        if (version_compare(PHP_VERSION, '7.3.0', '>=')) {
            setcookie($name, $value, [
                'expires' => $expires,
                'path' => '/',
                'domain' => (($dot === true) ? '.' . $_SERVER['HTTP_HOST'] : ''),
                'secure' => (($samesite == 'None') ? true : false),
                'httponly' => false,
                'samesite' => $samesite,
            ]);
        } else {
            setcookie($name, $value, $expires, '/');
        }
    }
}

/**
 * Generate a random word of the specified length
 *
 * @param int $length The length of the random word
 * @return string The generated random word
 */
function bbcs_RandomWord($length = 4)
{
    return substr(str_shuffle("qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM"), 0, $length);
}

/**
 * Get the avatar path of a user
 *
 * @param int $user_id The ID of the user
 * @return string The avatar path of the user
 */
function bbcs_get_avatar_path($user_id)
{
    $avatar = get_avatar($user_id);
    if (!empty(esc_url($avatar))) {
        return esc_url($avatar);
    } else {
        return '-';
    }
}

/**
 * Get the display name of a user
 *
 * @param int $user_id The ID of the user
 * @return string The display name of the user
 */
function bbcs_get_display_name($user_id)
{
    $user = get_userdata($user_id);
    if ($user) {
        return $user->display_name;
    } else {
        return 'Bot Blocker User';
    }
}


/**
 * Get the email address of a user
 *
 * @param int $user_id The ID of the user
 * @return string The email address of the user
 */
function bbcs_get_email($user_id)
{
    $user = get_userdata($user_id);
    if ($user) {
        return $user->user_email;
    } else {
        return '';
    }
}

/**
 * Get the role of a user
 *
 * @param int $user_id The ID of the user
 * @return string The role of the user
 */
function bbcs_get_user_role($user_id)
{
    $user = get_userdata($user_id);
    if ($user) {
        return implode(', ', $user->roles);
    } else {
        return '';
    }
}

/**
 * Generate settings file from database.
 *
 * This function retrieves settings data from the database table and generates a PHP settings file.
 * The settings are stored in an associative array and converted to the appropriate data types.
 * The generated settings file is saved to the specified directory.
 *
 * @return bool Returns true if the settings file is successfully generated and saved, false otherwise.
 */

function bbcs_generateSettingsFileFromDb()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'bbcs_settings';

    $results = $wpdb->get_results("SELECT `key`, `value` FROM $table_name", ARRAY_A);

    $settings = [];
    foreach ($results as $row) {
        $key = $row['key'];
        $value = $row['value'];

        $decoded = json_decode($value, true);
        if (json_last_error() == JSON_ERROR_NONE) {
            $settings[$key] = $decoded;
        } else {
            if (is_numeric($value)) {
                if (strpos($value, '.') !== false) {
                    $settings[$key] = (float)$value;
                } else {
                    $settings[$key] = (int)$value;
                }
            } elseif ($value === 'true' || $value === 'false') {
                $settings[$key] = $value === 'true';
            } else {
                $settings[$key] = $value;
            }
        }
    }

    $settingsContent = "<?php\nreturn " . var_export($settings, true) . ";\n";

    // Записываем файл
    $settingsFile = BOTBLOCKER_DIR . 'data/settings.php';
    file_put_contents($settingsFile, $settingsContent);

    return true;
}

/**
 * Determine the device type based on the user agent
 *
 * @param string $userAgent The user agent
 * @return string The device type (phone, tablet, tv, box, pc)
 */
function bbcs_getDeviceType($userAgent)
{
    require_once BOTBLOCKER_DIR . 'vendor/MobDetect/MobileDetect.php';

    $detect = new \Detection\MobileDetect();

    $detect->setUserAgent($userAgent);

    if ($detect->isTablet()) {
        return 'tablet';
    } elseif ($detect->isMobile()) {
        return 'phone';
    } elseif (preg_match('/smart-tv|smarttv|googletv|appletv|hbbtv|pov_tv|netcast.tv/i', $userAgent)) {
        return 'tv';
    } elseif (preg_match('/xbox|playstation|nintendo/i', $userAgent)) {
        return 'box';
    } else {
        return 'pc';
    }
}

function calculateSiteHealth()
{
    global $BBCS;
    $health = 0;
    $totalFactors = 18;

    if ($BBCS['disable'] == 0) {
        if ($BBCS['bbcs_captcha_mode'] != -1) $health++;
        if ($BBCS['bbcs_captcha_disable'] == 0) $health++;
        if ($BBCS['block_empty_ua'] == 1) $health++;
        if ($BBCS['block_empty_lang'] == 1) $health++;
        if ($BBCS['block_nojs_users'] == 1) $health++;
        if ($BBCS['block_proxy_users'] == 1) $health++;
        if ($BBCS['block_vpn_users'] == 1) $health++;
        if ($BBCS['block_tor_users'] == 1) $health++;
        if ($BBCS['block_IPv6_users'] == 1) $health++;
        if ($BBCS['unresponsive'] == 1) $health++;
        if ($BBCS['time_ban'] > 0) $health++;
        if ($BBCS['time_ban_2'] > 0) $health++;
        if ($BBCS['hosting_block'] == 0) $health++;
        if ($BBCS['block_fake_ref'] == 1) $health++;

        if ($BBCS['check'] == 1) {
            $health += 3;
            $totalFactors += 3;
        }

        if (
            $BBCS['recaptcha_check'] == 1 &&
            !empty($BBCS['recaptcha_key3']) &&
            !empty($BBCS['recaptcha_secret3'])
        ) {
            $health++;
            $totalFactors++;
        }

        $normalizedHealth = ($health / $totalFactors) * 100;

        return max(1, min(100, round($normalizedHealth)));
    } else {
        return 0;
    }
}

function generateSiteHealthList()
{
    global $BBCS;
    if ($BBCS['disable'] == 1) {
        return '<div class="bbcs-health-list"><span class="bbcs-health-list-item text-danger"><i class="fa-regular fa-circle-xmark"></i> BBCS is disabled</span></div>';
    }

    $healthItems = [
        'check' => ['Cloud protection', 3],
        'unresponsive' => ['Unresponsive IP blocking', 1],
        'bbcs_captcha_mode' => ['CAPTCHA mode enabled', 1],
        'bbcs_captcha_disable' => ['CAPTCHA not disabled', 1],
        'block_empty_ua' => ['Blocking empty User-Agent', 1],
        'block_empty_lang' => ['Blocking empty language', 1],
        'block_nojs_users' => ['Blocking users without JavaScript', 1],
        'block_proxy_users' => ['Blocking proxy users', 1],
        'block_vpn_users' => ['Blocking VPN users', 1],
        'block_tor_users' => ['Blocking Tor users', 1],
        'block_IPv6_users' => ['Blocking IPv6 users', 1],
        'time_ban' => ['Time ban enabled', 1],
        'time_ban_2' => ['Secondary time ban enabled', 1],
        'hosting_block' => ['Hosting not blocked', 1],
        'block_fake_ref' => ['Blocking fake referrers', 1],
        'recaptcha_check' => ['reCAPTCHA enabled', 1],
    ];

    $output = '<div class="bbcs-health-list">';

    foreach ($healthItems as $key => $item) {
        $text = $item[0];
        $value = isset($BBCS[$key]) ? $BBCS[$key] : 0;

        $isEnabled = false;
        switch ($key) {
            case 'bbcs_captcha_mode':
                $isEnabled = $value != -1;
                break;
            case 'hosting_block':
                $isEnabled = $value == 0;
                break;
            case 'time_ban':
            case 'time_ban_2':
                $isEnabled = $value > 0;
                break;
            case 'recaptcha_check':
                $isEnabled = $value == 1 && !empty($BBCS['recaptcha_key3']) && !empty($BBCS['recaptcha_secret3']);
                break;
            default:
                $isEnabled = $value == 1;
        }

        $icon = $isEnabled ? '<i class="fa-regular fa-circle-check"></i>' : '<i class="fa-regular fa-circle-xmark"></i>';
        $statusClass = $isEnabled ? 'text-success' : 'text-danger';
        $statusText = $isEnabled ? '' : ' (disabled)';

        $output .= "<span class='bbcs-health-list-item $statusClass'>$icon $text$statusText</span>";
    }

    return $output . '</div>';
}

function bbcs_die($msg = '')
{
    unset($pt); // TRANSLATE
    if(!empty($msg)){
        die($msg);
    } else {
        die();
    }
}

function bbcs_initSalt(){
    global $BBCS;
    
    $saltFile = $BBCS['dirs']['data'] . 'salt.php';

    if (file_exists($saltFile)) {
        include($saltFile);
    } else {
        $BBCS['salt_pz'] = time();
        $BBCS['salt_ps'] = '_CyberSecure_by_Globus_Studio_';
        $BBCS['salt_bb'] = '_BotBlocker_';
        $BBCS['host_key'] = 'globus.studio';

        $fileContent = "<?php\n";
        $fileContent .= '$this->BBCS[\'host_key\'] = \'' . $BBCS['host_key'] . "';\n";
        $fileContent .= '$this->BBCS[\'salt_bb\'] = \'' . $BBCS['salt_bb'] . "';\n";
        $fileContent .= '$this->BBCS[\'salt_ps\'] = \'' . $BBCS['salt_ps'] . "';\n";
        $fileContent .= '$this->BBCS[\'salt_pz\'] = \'' . $BBCS['salt_pz'] . "';\n";

        file_put_contents($saltFile, $fileContent);
    }
}

function bbcs_isBot($useragent)
{
    return preg_match("/(apache|bot|cfnetwork|crawler|curl|facebookexternalhit|feed|google.com|headless|index|mediapartners|python|spider|yahoo)/i", $useragent);
}

function bbcs_localResult($status, $msg, $cookie)
{
    global $BBCS, $wpdb;

    if ($status == 'cookie') {
        $passed = 'passed=\'1\', ';
        $result = $msg;
        $return = json_encode(['cookie' => $cookie]);

        if ($msg == 'ALLOW By rule: timezone=' . $BBCS['tz']) {
            $passed = 'passed=\'4\', ';
        }
    } else {
        $passed = '';
        $result = $msg;

        if ($msg == 'BLOCK By rule: timezone=' . $BBCS['tz']) {
            $msg = $BBCS['js_error_msg'];
            $passed = 'passed=\'6\', ';
        }

        $return = json_encode(['error' => $msg]);
    }

    if ($BBCS['botblocker_log_tests'] == 1) {
        $save_result = ($result != 'gray') ? ', result=\'' . esc_sql($result) . '\'' : '';
        $exec_time = round(microtime(true) - $BBCS['start_time'], 4);
        $data = array(
            'passed'      => esc_sql($passed),
            'ipv4'        => esc_sql($BBCS['ipv4']),
            'generation2' => $exec_time,
            'recaptcha'   => esc_sql($BBCS['score']),
            'js_w'        => esc_sql($BBCS['w']),
            'js_h'        => esc_sql($BBCS['h']),
            'js_cw'       => esc_sql($BBCS['cw']),
            'js_ch'       => esc_sql($BBCS['ch']),
            'js_co'       => esc_sql($BBCS['co']),
            'js_pi'       => esc_sql($BBCS['pi']),
            'adblock'     => esc_sql($BBCS['adb']),
            'timezone'    => esc_sql($BBCS['tz']),
            'result'      => esc_sql($save_result),
        );

        $update = $wpdb->update(
            $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'hits',
            $data,
            array('cid' => esc_sql($BBCS['cid']))
        );
    }

    return $return;
}

function bbcs_decodeDetectionParam($param){
    $decodedParam = urldecode($param);
    $jsonString = base64_decode($decodedParam, true);
    if ($jsonString === false) {
        return false; 
    }
    $detectionData = json_decode($jsonString, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return false;
    }
    return $detectionData;    
}

function bbcs_sendToCloud($data, $url) {

    $fullURL = $url . 'botblocker';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $fullURL);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2); 
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_REFERER, BOTBLOCKER_SITE_URL);
    curl_setopt($ch, CURLOPT_USERAGENT, BOTBLOCKER_USER_AGENT);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $paramsFilePath =  BOTBLOCKER_DIR.'params_log.txt'; 
    $formattedParams = $fullURL .'-'.$http_code."\r\n" . print_r($data, true) . "\r\n" .'Response:'."\r\n" . $response . "\r\n\r\n"; 
    file_put_contents($paramsFilePath, $formattedParams, FILE_APPEND); 

    if ($http_code === 200 && !empty($response) && bbcs_isJson($response)) {
        return json_decode(trim($response), true);
    }
    return false;
}

function bbcs_isJson($string) {
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}
