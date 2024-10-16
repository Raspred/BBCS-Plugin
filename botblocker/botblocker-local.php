<?php

header('Content-Type: text/html; charset=UTF-8');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: *');
header('X-Robots-Tag: noindex');

global $BBCS;
global $wpdb;

$BBCS['start_time'] = microtime(true);
$BBCS['score'] = 0;
$BBCS['msg'] = '';

if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
    $BBCS['scheme'] = trim(strip_tags($_SERVER['HTTP_X_FORWARDED_PROTO']));
} elseif (isset($_SERVER['REQUEST_SCHEME'])) {
    $BBCS['scheme'] = trim(strip_tags($_SERVER['REQUEST_SCHEME']));
} else {
    $BBCS['scheme'] = 'https';
}

$BBCS['cfcountry'] = isset($_SERVER['HTTP_CF_IPCOUNTRY']) ? strip_tags(trim($_SERVER['HTTP_CF_IPCOUNTRY'])) : '';
$BBCS['uri'] = isset($_SERVER['REQUEST_URI']) ? trim(strip_tags($_SERVER['REQUEST_URI'])) : '/';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    bbcs_die('{"error": "Error NoPost"}');
}

if (isset($_POST['bbdet'])) {
    $forDecode = $_POST['bbdet'];
    $detectionData = bbcs_decodeDetectionParam($forDecode);
    if ($detectionData !== false) {
        $BBCS['bbdet'] = $detectionData;
    } else {
        $BBCS['bbdet'] = $BBCS['empty'];
    }
} else {
    $BBCS['bbdet'] = $BBCS['empty'];
}

//TODO -> TO DB

/*
$jsonData = json_encode($BBCS['bbdet'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
$result = file_put_contents($BBCS['dirs']['root'].'bbdet.txt', $jsonData);
*/

// 1 if cookies are disabled, don't allow such users
if (isset($_POST['cookieoff'])) {
    $BBCS['cookieoff'] = (int)trim(preg_replace("/[^0-9]/", "", $_POST['cookieoff']));
} else {
    $BBCS['cookieoff'] = 0;
}

// 0 - don't stop, 1 - gray, 2 - dark, stop these
if (isset($_POST['gray'])) {
    $BBCS['gray'] = (int)trim(preg_replace("/[^0-9]/", "", $_POST['gray']));
} else {
    $BBCS['gray'] = 0;
}

// Row number from table 5 if dark was triggered
if (isset($_POST['rowid'])) {
    $BBCS['rowid'] = (int)trim(preg_replace("/[^0-9]/", "", $_POST['rowid']));
} else {
    $BBCS['rowid'] = 0;
}

// Request send date
if (isset($_POST['date'])) {
    $BBCS['date'] = trim(preg_replace("/[^0-9]/", "", $_POST['date']));
} else {
    $BBCS['date'] = 0;
}

// Full hash (sha256)
if (isset($_POST['h1'])) {
    $h1 = trim(preg_replace("/[^0-9a-z]/", "", $_POST['h1']));
} else {
    $h1 = 'xxx';
}

// Another data integrity test
if (isset($_POST['test'])) {
    $test = trim(preg_replace("/[^0-9a-z]/", "", $_POST['test']));
} else {
    $test = 'xxx';
}

// 1 - server (suspicious) ip
if (isset($_POST['hdc'])) {
    $BBCS['hdc'] = (int)trim(preg_replace("/[^0-9]/", "", $_POST['hdc']));
} else {
    $BBCS['hdc'] = 0;
}

// Adblock, 1 - present, 0 - not present
if (isset($_POST['a'])) {
    $BBCS['adb'] = (int)trim(preg_replace("/[^0-9]/", "", $_POST['a']));
} else {
    $BBCS['adb'] = 0;
}

// Country code from local database
if (isset($_POST['country'])) {
    $BBCS['country'] = trim(preg_replace("/[^A-Z]/", "", $_POST['country']));
} else {
    $BBCS['country'] = $BBCS['empty'];
}

// IP from PHP, could be IPv6
if (isset($_POST['ip'])) {
    $BBCS['ip'] = trim(preg_replace("/[^0-9a-zA-Z\.\:]/", "", $_POST['ip']));
} else {
    $BBCS['ip'] = '';
}

// BotBlocker version
if (isset($_POST['version'])) {
    $BBCS['version'] = (float)trim(preg_replace("/[^0-9\.]/", "", $_POST['version']));
} else {
    $BBCS['version'] = '';
}

// Unique click ID
if (isset($_POST['cid'])) {
    $BBCS['cid'] = trim(preg_replace("/[^0-9\.]/", "", $_POST['cid']));
} else {
    bbcs_die('{"error": "Empty CID"}');
}

// PTR
if (isset($_POST['ptr'])) {
    $BBCS['ptr'] = trim(preg_replace("/[^0-9a-zA-Z\.\:\-]/", "", $_POST['ptr']));
} else {
    $BBCS['ptr'] = '';
}

// Monitor width
if (isset($_POST['w'])) {
    $BBCS['w'] = (int)trim(preg_replace("/[^0-9]/", "", $_POST['w']));
} else {
    $BBCS['w'] = 0;
}

// Monitor height
if (isset($_POST['h'])) {
    $BBCS['h'] = (int)trim(preg_replace("/[^0-9]/", "", $_POST['h']));
} else {
    $BBCS['h'] = 0;
}

// Browser window width
if (isset($_POST['cw'])) {
    $BBCS['cw'] = (int)trim(preg_replace("/[^0-9]/", "", $_POST['cw']));
} else {
    $BBCS['cw'] = 0;
}

// Browser window height
if (isset($_POST['ch'])) {
    $BBCS['ch'] = (int)trim(preg_replace("/[^0-9]/", "", $_POST['ch']));
} else {
    $BBCS['ch'] = 0;
}

// Color depth
if (isset($_POST['co'])) {
    $BBCS['co'] = (int)trim(preg_replace("/[^0-9]/", "", $_POST['co']));
} else {
    $BBCS['co'] = 0;
}

// Pixel depth
if (isset($_POST['pi'])) {
    $BBCS['pi'] = (int)trim(preg_replace("/[^0-9]/", "", $_POST['pi']));
} else {
    $BBCS['pi'] = 0;
}

// Full referrer (with which they came to the client's site)
if (isset($_POST['ref'])) {
    $BBCS['ref'] = trim(strip_tags($_POST['ref']));
} else {
    $BBCS['ref'] = '';
}

// Timezone from JS
if (isset($_POST['tz'])) {
    $BBCS['tz'] = trim(preg_replace("/[^0-9a-zA-Z\-\/\_\+]/", "", $_POST['tz']));
} else {
    $BBCS['tz'] = '';
}

// Country code from ipdb.cloud, only IPv4, may not be present
if (isset($_POST['ipdbc'])) {
    $BBCS['ipdbc'] = trim(preg_replace("/[^A-Z]/", "", $_POST['ipdbc']));
} else {
    $BBCS['ipdbc'] = '';
}

// IPv4 from ipdb.cloud, only IPv4, may not be present
if (isset($_POST['ipv4'])) {
    $BBCS['ipv4'] = trim(preg_replace("/[^0-9\.]/", "", $_POST['ipv4']));
} else {
    $BBCS['ipv4'] = '';
}

// reCAPTCHA token, if reCAPTCHA check is enabled
if (isset($_POST['rct'])) {
    $BBCS['rct'] = trim(strip_tags($_POST['rct']));
} else {
    $BBCS['rct'] = '';
}

// Some auxiliary data (not used in the cloud)
if (isset($_POST['xxx'])) {
    $BBCS['xxx'] = trim(strip_tags($_POST['xxx']));
} else {
    $BBCS['xxx'] = '';
}

// HTTP_ACCEPT
if (isset($_POST['accept'])) {
    $BBCS['accept'] = trim(strip_tags($_POST['accept']));
} else {
    $BBCS['accept'] = '';
}

// Domain from which this script was requested
if (isset($_SERVER['HTTP_REFERER'])) {
    $BBCS['referer'] = strip_tags(trim($_SERVER['HTTP_REFERER']));
} else {
    $BBCS['referer'] = '';
}

// Empty user agent
if (isset($_SERVER['HTTP_USER_AGENT'])) {
    $BBCS['useragent'] = trim(strip_tags($_SERVER['HTTP_USER_AGENT']));
} else {
    $BBCS['useragent'] = '';
}

// Language (normal browsers always have this)
if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    $BBCS['accept_lang'] = trim(strip_tags($_SERVER['HTTP_ACCEPT_LANGUAGE']));
} else {
    $BBCS['accept_lang'] = '';
}

// Domain (host) from which the script was called
$BBCS['refhost'] = parse_url($BBCS['referer'], PHP_URL_HOST);

/*
BBDET
{
    "navigatorMismatch": true,
    "unsupportedFeatures": false,
    "fakePlugins": false,
    "fontRenderMismatch": false,
    "chromiumProperties": false,
    "jitter": false,
    "webGLMismatch": false,
    "touchEventMismatch": false,
    "batteryAPIMismatch": false,
    "mediaDevicesMismatch": false,
    "permissionsMismatch": false,
    "languageMismatch": false
}
*/

if ($BBCS['cookieoff'] == 1) {
    echo bbcs_localResult('error', 'Cookies disabled', '');
    bbcs_die();
}

if ($BBCS['time'] - $BBCS['date'] > 3600) {
    echo bbcs_localResult('error', 'Token Expired', '');
    bbcs_die();
}
 
$BBCS['tz'] = esc_sql($BBCS['tz']);

if ($BBCS['rowid'] > 0) {
    $query = $wpdb->prepare(
        "SELECT id, * FROM {$wpdb->prefix}bbcs_rules WHERE search=%s OR id=%d ORDER by priority ASC",
        'timezone=' . $BBCS['tz'],
        $BBCS['rowid']
    );
    $bbcsRulesCheck = $wpdb->get_results($query, ARRAY_A);
} else {
    $query = $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}bbcs_rules WHERE search=%s",
        'timezone=' . $BBCS['tz']
    );
    $bbcsRulesCheck = $wpdb->get_results($query, ARRAY_A);
}

foreach ($bbcsRulesCheck as $echo) {
    // так проверим, что таймзона найдена до другого dark правила:
    if ($echo['disable'] == '0') {
        if ($echo['search'] == 'timezone=' . $BBCS['tz']) {
            if ($echo['rule'] == 'dark') {
                echo bbcs_localResult('error', 'DARK By rule: timezone=' . $BBCS['tz'], '');
                bbcs_die();
            } elseif ($echo['rule'] == 'block') {
                echo bbcs_localResult('error', 'BLOCK By rule: timezone=' . $BBCS['tz'], '');
                bbcs_die();
            } elseif ($echo['rule'] == 'allow') {
                // разрешающие куки:
                $BBCS['hash'] = md5($BBCS['salt'] . $BBCS['license_pass'] . $BBCS['refhost'] . $BBCS['useragent'] . $BBCS['ip'] . $BBCS['time']) . '-' . $BBCS['time']; 
                // код для куки
                echo bbcs_localResult('cookie', 'ALLOW By rule: timezone=' . $BBCS['tz'], $BBCS['hash']);
                bbcs_die();
            } elseif ($echo['rule'] == 'gray') {
                $BBCS['gray'] = 1;
            }
        }
    }
}

// особое условие:
if ($BBCS['gray'] == 1 or $BBCS['gray'] == 2) {
    echo bbcs_localResult('error', 'gray', '');
    bbcs_die();
}

// все это не может быть пустым:
if ($BBCS['version'] == '') {
    echo bbcs_localResult('error', 'Version', '');
    bbcs_die();
}
if ($BBCS['w'] < 300) {
    echo bbcs_localResult('error', 'Monitor Width', '');
    bbcs_die();
}
if ($BBCS['h'] < 300) {
    echo bbcs_localResult('error', 'Monitor Height', '');
    bbcs_die();
}
if ($BBCS['cw'] < 250) {
    echo bbcs_localResult('error', 'Browser Window Width', '');
    bbcs_die();
}
if ($BBCS['ch'] < 250) {
    echo bbcs_localResult('error', 'Browser Window Height', '');
    bbcs_die();
}
if ($BBCS['co'] < 24) {
    echo bbcs_localResult('error', 'Color Depth', '');
    bbcs_die();
}
if ($BBCS['pi'] < 24) {
    echo bbcs_localResult('error', 'Pixel Depth', '');
    bbcs_die();
}
if ($BBCS['referer'] == '') {
    echo bbcs_localResult('error', 'Empty Referer', '');
    bbcs_die();
}
if ($BBCS['useragent'] == '') {
    echo bbcs_localResult('error', 'Empty User-agent', '');
    bbcs_die();
}
if ($BBCS['accept_lang'] == '') {
    echo bbcs_localResult('error', 'Empty Lang', '');
    bbcs_die();
}

if ($BBCS['hdc'] == 1) {
    echo bbcs_localResult('error', 'Hosting or Bad IP', '');
    bbcs_die();
}
if (bbcs_isBot($BBCS['useragent'])) {
    echo bbcs_localResult('error', 'Bot', '');
    bbcs_die();
}

if ($h1 != hash('sha256', $BBCS['license_email'] . $BBCS['license_pass'] . $BBCS['refhost'] . $BBCS['useragent'] . $BBCS['ip'] . $BBCS['date'])) {
    echo bbcs_localResult('error', 'H1 Hash Error', '');
    bbcs_die();
}

if ($test != hash('sha256', $BBCS['useragent'] . $BBCS['ip'] . $BBCS['date'] . $BBCS['country'] . $BBCS['ptr'] . $BBCS['salt'])) {
    echo bbcs_localResult('error', 'Test Hash Error', '');
    bbcs_die();
}

if ($BBCS['time'] - $BBCS['date'] > 20) {
    echo bbcs_localResult('error', 'Long Request Error', '');
    bbcs_die();
}

// тут еще сделать провеки (и в post.php тоже):
/* 
 * если ipv4 есть значит чекать его по базе 1.
 * если страна ipv4 есть и отличается от исходной чекать по 5 базе.
*/

if ($BBCS['recaptcha_check'] == 1) {
    $data = array(
        'secret' => $BBCS['recaptcha_secret3'],
        'response' => $BBCS['rct'],
        'remoteip' => $BBCS['ip']
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_REFERER, $BBCS['referer']);
    curl_setopt($ch, CURLOPT_USERAGENT, $BBCS['useragent']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
     curl_setopt($ch, CURLOPT_FTP_SSL, CURLFTPSSL_TRY);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $re = @json_decode(trim(curl_exec($ch)), true);
    curl_close($ch);

    if (isset($re['score'])) {
        $BBCS['score'] = trim($re['score']);
    } else {
        $BBCS['score'] = 0;
        echo bbcs_localResult('error', 'Recaptcha Error', '');
        bbcs_die();
    }
}

if ($BBCS['check'] == 1) {
    $data = array(
        'license_api_key' => $BBCS['license_key'],
        'domain_api_key' => $BBCS['license_secret'],
        'cid' => $BBCS['cid'],
        'score' => $BBCS['score'],
        'cfcountry' => $BBCS['cfcountry'],
        'country' => $BBCS['country'],
        'ip' => $BBCS['ip'],
        'version' => $BBCS['version'],
        'ptr' => $BBCS['ptr'],
        'w' => $BBCS['w'],
        'h' => $BBCS['h'],
        'cw' => $BBCS['cw'],
        'ch' => $BBCS['ch'],
        'co' => $BBCS['co'],
        'pi' => $BBCS['pi'],
        'ref' => $BBCS['ref'],
        'tz' => $BBCS['tz'],
        'adb' => $BBCS['adb'],
        'ipdbc' => $BBCS['ipdbc'],
        'ipv4' => $BBCS['ipv4'],
        'accept' => $BBCS['accept'],
        'referer' => $BBCS['referer'],
        'useragent' => $BBCS['useragent'],
        'accept_lang' => $BBCS['accept_lang']
    );

    $cloud = bbcs_sendToCloud($data, BOTBLOCKER_API_URL);

    if ($cloud === false) {
        $cloud = bbcs_sendToCloud($data, BOTBLOCKER_API_GS_URL);
    }

    if ($cloud === false) {
        echo bbcs_localResult('error', 'Both Cloud APIs unresponsive', '');
        bbcs_die();
    }

    $BBCS['cloud_data'] = [];
    foreach ($cloud as $key => $value) {
        if (is_array($value)) {
            foreach ($value as $sub_key => $sub_value) {
                $BBCS['cloud_data'][$key . '_' . $sub_key] = $sub_value;
            }
        } else {
            $BBCS['cloud_data'][$key] = $value;
        }
    }

    // Проверка статуса и bbcs_score
    $status = $cloud['status'] ?? 'unknown';
    $bbcs_score = $cloud['bbcs_score'] ?? 0;

    // Если статус плохой и флаг unresponsive не установлен
    if (($status === 'bad' || $bbcs_score >= 5) && $BBCS['unresponsive'] == 0) {
        echo bbcs_localResult('error', 'Cloud API identified user as bad.', '');
        bbcs_die();
    }

    // Если статус серый и флаг unresponsive не установлен
    if ($status === 'gray' && $BBCS['unresponsive'] == 0) {
        echo bbcs_localResult('error', 'Cloud API identified user as gray.', '');
        bbcs_die();
    }

    // В случае неопределенности или флага unresponsive разрешаем доступ
    if ($status !== 'good' && $BBCS['unresponsive'] == 1) {
        $BBCS['msg'] = 'Cloud API unresponsive or user status not confirmed. Access allowed due to unresponsive flag.';
    }
}

if ($BBCS['score'] == 0.1 or $BBCS['score'] == 0.3 or $BBCS['score'] == 0.7) {
    echo bbcs_localResult('error', 'Recaptcha', '');
    bbcs_die();
}

// дальше успешный ответ:
$BBCS['hash'] = md5($BBCS['salt'] . $BBCS['license_pass'] . $BBCS['refhost'] . $BBCS['useragent'] . $BBCS['ip'] . $BBCS['time']) . '-' . $BBCS['time']; // код для куки

echo bbcs_localResult('cookie', $BBCS['msg'], $BBCS['hash']);