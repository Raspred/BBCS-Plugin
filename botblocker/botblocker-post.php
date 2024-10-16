<?php
// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC') || !defined('BOTBLOCKER')) {
    exit;
}

// локальная страница проверки через click
ignore_user_abort(true);
header('Content-Type: text/html; charset=UTF-8');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: *');
header('X-Robots-Tag: noindex');

global $wpdb;
global $BBCS;

if ($BBCS['bbcs_captcha_disable'] == 1) {
    bbcsCheckDie('{"error": "Input Button Disabled"}');
}
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    bbcsCheckDie('{"error": "Error NoPost"}');
}


if (isset($_POST['cid'])) {
    $_POST['cid'] = trim(preg_replace("/[^0-9\.]/", "", $_POST['cid']));
} else {
    bbcsCheckDie('{"error": "CID not set"}');
}

if (isset($_SERVER['HTTP_USER_AGENT'])) {
    $BBCS['useragent'] = trim(strip_tags($_SERVER['HTTP_USER_AGENT']));
} else {
    $BBCS['useragent'] = '';
}

if (isset($_POST['ip'])) {
    $_POST['ip'] = trim(preg_replace("/[^0-9a-zA-Z\.\:]/", "", $_POST['ip']));
} else {
    bbcsCheckDie('{"error": "IP not set"}');
}

if (isset($_POST['xxx'])) {
    $_POST['xxx'] = trim(strip_tags($_POST['xxx']));
} else {
    bbcsCheckDie('{"error": "XXX not set"}');
}

if (isset($_POST['date'])) {
    $_POST['date'] = (int)trim(strip_tags($_POST['date']));
} else {
    bbcsCheckDie('{"error": "DATE not set"}');
}

if (isset($_POST['country'])) {
    $_POST['country'] = trim(preg_replace("/[^A-Z]/", "", $_POST['country']));
} else {
    $_POST['country'] = $BBCS['empty'];
}

if (isset($_SERVER['HTTP_REFERER'])) {
    $referer = strip_tags(trim($_SERVER['HTTP_REFERER']));
} else {
    $referer = '';
    bbcsCheckDie('{"error": "Referer not set"}');
}

// домен (host) с которого вызвали скрипт:
$refhost = parse_url($referer, PHP_URL_HOST);

// тут еще сделать провеки (и в ab.php тоже):
/* 
 * если ipv4 есть значит чекать его по базе 1.
 * если страна ipv4 есть и отличается от исходной чекать по 5 базе.
*/

if ($BBCS['time'] - $_POST['date'] > 600) bbcsCheckDie('{"cookie":"000"}');

if ($BBCS['bbcs_captcha_mode'] == 3 or $BBCS['bbcs_captcha_mode'] == 4) {
    // ReCAPTCHA v2 + кнопка "Я не робот"
    $g_recaptcha_response = isset($_POST['g-recaptcha-response']) ? strip_tags(trim($_POST['g-recaptcha-response'])) : '';
    $data = array(
        'secret' => $BBCS['recaptcha_secret2'],
        'response' => $g_recaptcha_response,
        'remoteip' => $_POST['ip']
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 600);
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_REFERER, '');
    curl_setopt($ch, CURLOPT_USERAGENT, $BBCS['useragent']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    //curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
    curl_setopt($ch, CURLOPT_FTP_SSL, CURLFTPSSL_TRY);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $re = @json_decode(trim(curl_exec($ch)), true);
    curl_close($ch);
    if (isset($re['success']) and $re['success'] != 1) {
        $BBCS['bbcs_captcha_mode'] = 1;
        if ($BBCS['time_ban'] < 1) {
            $BBCS['time_ban'] = '1';
        }
    }
}

if ($BBCS['bbcs_captcha_mode'] == 0 or $BBCS['bbcs_captcha_mode'] == 3 or $BBCS['bbcs_captcha_mode'] == 4) {
    $hash0 = '1|' . hash('sha256', $BBCS['salt'] . $_POST['date'] . $BBCS['license_pass']);
    if ($hash0 != $_POST['xxx']) {
        $BBCS['bbcs_captcha_mode'] = 1;
        if ($BBCS['time_ban'] < 1) {
            $BBCS['time_ban'] = '1';
        }
    }
}

if ($BBCS['bbcs_captcha_mode'] == 1 or $BBCS['bbcs_captcha_mode'] == 2) {
    $xxx2 = explode('|', $_POST['xxx']);
    if (!isset($xxx2[1])) bbcsCheckDie('{"error": "Error NoPost 1"}');
    $_POST['color'] = $xxx2[0];
    $_POST['color_hash'] = $xxx2[1];

    if ($_POST['color_hash'] != hash('sha256', $BBCS['salt'] . $_POST['color'] . $_POST['date'] . $BBCS['license_pass'] . $_POST['ip'])) {
        // не прошли цветные/картиночные кнопки, не даем пройти антибота, добавление ip в черный список:
        // проверка валидности ip:
        if (filter_var($_POST['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $BBCS['ip_version'] = 4;
        } elseif (filter_var($_POST['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $BBCS['ip_version'] = 6;
        } else {
            bbcsCheckDie('{"error": "Bad IP"}');
        }

        // проверка ip по логу: $BBCS['time']
        $fromdate = $BBCS['time']  - 86401;

        $miss_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}bbcs_hits WHERE date >= %d AND ip = %s AND passed = '8'", $fromdate, $_POST['ip']));
        $miss_count = (int)$miss_count;
        
        if ($miss_count > 0) {
            $BBCS['time_ban'] = $BBCS['time_ban_2'];
        }

// Перевод времени в секунды
$BBCS['time_ban'] = explode('.', $BBCS['time_ban']);
$BBCS['time_ban'] = isset($BBCS['time_ban'][1])
    ? $BBCS['time_ban'][0] * 3600 + $BBCS['time_ban'][1] * 60
    : $BBCS['time_ban'][0] * 3600;

if ($BBCS['time_ban'] == 0) {
    $BBCS['time_ban'] = 480; // 8 минут в секундах
}

$ip = $_POST['ip'];
$table_name = $wpdb->prefix . 'bbcs_ipv' . $BBCS['ip_version'] . 'rules';

// Проверяем существование записи
$existing_rule = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM $table_name WHERE search = %s",
    $ip
));

$expires = $BBCS['time'] + $BBCS['time_ban'];

if ($existing_rule) {
    // Если запись существует, обновляем время истечения
    $wpdb->update(
        $table_name,
        array('expires' => $expires),
        array('search' => $ip)
    );
} else {
    // Если записи нет, вставляем новую
    $data = array(
        'priority' => '1',
        'search' => $ip,
        'ip1' => bbcs_ipToNumeric($ip),
        'ip2' => bbcs_ipToNumeric($ip),
        'rule' => 'block',
        'comment' => 'Wrong Click ' . $_POST['country'],
        'expires' => $expires
    );

    $wpdb->insert($table_name, $data);
}

if ($wpdb->last_error) {
    error_log('WordPress database error: ' . $wpdb->last_error);
}


        // обновление лога miss:
        $ok = 1;
        if ($BBCS['botblocker_log_tests'] == 1) {

            $table_name = $wpdb->prefix .BOTBLOCKER_TABLE_PREFIX.'hits';
            $sql = $wpdb->prepare("UPDATE $table_name SET passed='8' WHERE passed='0' AND cid=%s", $_POST['cid']);
            $update = $wpdb->query($sql);
            if ($wpdb->rows_affected != 1) {
                $ok = 0;
            }

        }

        bbcsCheckDie('{"error": "Wrong Click"}');
    }
} elseif ($BBCS['bbcs_captcha_mode'] == 3) {
    // ReCAPTCHA v2 + кнопка "Я не робот"

} elseif ($BBCS['bbcs_captcha_mode'] == 0) {
    // единственная кнопка
}

// обновление лога о проходе заглушки:
$ok = 1;
if ($BBCS['botblocker_log_tests'] == 1) {

    $sql = $wpdb->prepare("UPDATE {$wpdb->prefix}bbcs_hits SET passed='2' WHERE passed='0' AND cid=%s", $_POST['cid']);
    $update = $wpdb->query($sql);
    if ($wpdb->rows_affected != 1) {
        $ok = 0;
    } 

}

$hash = md5($BBCS['salt'] . $BBCS['license_pass'] . $refhost . $BBCS['useragent'] . $_POST['ip'] . $BBCS['time']) . '-' . $BBCS['time']; // код для куки

echo '{"cookie":"' . $hash . '"}';

function bbcsCheckDie($msg = ''){
    if(!empty($msg)){
        die($msg);
    } else {
        die();
    }
}
