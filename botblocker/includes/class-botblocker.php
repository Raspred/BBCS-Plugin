<?php
/**
 * The BotBlocker engine class.
 *
 * This class is responsible for all the operations against bots.
 * It handles detections, logging, and blocking of suspicious bot activities.
 *
 * @since      1.1.0
 * @package    Botblocker
 * @subpackage Botblocker/includes
 */

// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC') || !defined('BOTBLOCKER')) {
    exit;
} 

class BotBlocker
{
    private $BBCS;
    private $startTime;
    private $finishTime;

    /**
     * Initialize the class and set its properties.
     */
    public function __construct()
    {
        $this->BBCS = [];
    }

    /**
     * Initialize the bot blocking functionality.
     * This method could be used to set up hooks and perform other setup tasks.
     */
    public function initialize()
    {
        $this->BBCS['empty'] = '-';
        $this->BBCS['x-robots-tag'] = array();
        $this->BBCS['error_headers'] = bbcs_loadHeadersArray();

        $this->loadDirs();
        $this->loadData();
        $this->initialConfig();
        $this->loadSettings();

        $this->run();
    }

    public function run()
    {
        global $BBCS;               
        $BBCS = $this->BBCS;        

        $this->prefly();            // Preliminary preparation for request processing

        $this->collect();           // Collect user data for analyze
        $this->isProxy();           // Check Proxy IPs and headers
        $this->identifyByUA();      // Detect OS, browser and device type // TODO - exit by settings unset
        $this->collectCloud();      // Collect user data from IP (all license types)
        $this->selectMode();        // Check user / Check post data / Check captcha
        $this->checkSecretParam();  // Backdoor for parental servers // TODO - parentWay()

        $this->chain();             // BotBlocker main processing
        $this->finish();
    }

    public function chain()
    {
        global $BBCS;           
        if (php_sapi_name() != 'cli' && $this->BBCS['disable'] != 1) { // CLI stor OR BBCS if OFF // TODO -> run()

            $this->headerProcess();
            $this->cookieProcess();

            if ($this->BBCS['botblocker_cookies_valid'] == $this->BBCS['botblocker_cookies']) {
                $this->cookieCounter();
            } else {
                $this->checkReferGet();
                $this->isAnalytics();
                $this->isHosting();
                $this->isValidReferer();
                $this->isSelfRequest();

                if (!isset($this->BBCS['good_bot'])) {
                    $this->checkIpRules();
                } 
                $this->isFakeBrowser(); // BBDET is ready after first check()
                if (!isset($this->BBCS['good_bot'])) {
                    $this->checkConfigRules();
                }
                if (!isset($this->BBCS['good_bot'])) {
                    $this->checkPath();
                }
                if (!isset($this->BBCS['good_bot'])) {
                    $this->checkRules();
                }
                if (!isset($this->BBCS['good_bot'])) {
                    $this->isHuman();
                }
                $this->checkLastRule();

                if (isset($this->BBCS['good_bot']) && $this->BBCS['good_bot'] == 1) {
                    if ($this->BBCS['botblocker_log_goodip'] == 1) {
                        $this->BBCS['reason'] = '5'; // Good bot
                        $this->storeData();
                    }
                }
            }
            if (count($this->BBCS['x-robots-tag']) > 0) {
                header('X-Robots-Tag: ' . implode(', ', $this->BBCS['x-robots-tag']));
            }
        } else {
            $this->BBCS['good_bot'] = 0;
        }
        $BBCS = $this->BBCS;    
    }

    public function prefly()
    {
        global $BBCS;

        $this->BBCS['is_proxy'] = $this->BBCS['empty'];
        $this->BBCS['reason'] = $this->BBCS['empty'];

        $this->startTime = time();  // Processing start time     
        $this->BBCS['time'] = $this->startTime;
        $this->BBCS['date'] = date('Y.m.d', $this->BBCS['time']);
        $this->setCID(); // CID use BBCS['time']  

        if (date('I')) {
            $this->BBCS['admin_gmt_offset'] += 1; // Daylight saving time transition
            // TODO - Add client I fix
        }

        // Check if the error header code is not set in the error headers array
        if (!isset($this->BBCS['error_headers'][$this->BBCS['header_error_code']])) {
            $this->BBCS['header_error_code'] = 200; // Default - 200 (OK)
        }

        $this->BBCS['prefly'] = bbcs_prefly_check();   

        // Check if the salt file exists, if not, initialize the salt
        if (file_exists($this->BBCS['dirs']['data'] . 'salt.php')) {
            include($this->BBCS['dirs']['data'] . 'salt.php');
        } else {
            bbcs_initSalt();
        }

        // Combine the secondary salt and salt to form the final salt
        $this->BBCS['salt'] = $this->BBCS['salt_pz'] . $this->BBCS['salt'];

        /* TEST PRO */
        if ( defined( 'BOTBLOCKER_PRO' ) && BOTBLOCKER_PRO && class_exists( 'Cyber_Secure_Botblocker_PRO' ) ) {
            $bbcs_pro_instance = new Cyber_Secure_Botblocker_PRO();
            $this->BBCS['pro_motto'] = $bbcs_pro_instance->bbcs_helloPro();
        }

        $BBCS = $this->BBCS;
    }

    public function collect()
    {
        global $BBCS;

        // Get the HTTP host and remove any invalid characters
        if (isset($_SERVER['HTTP_HOST'])) {
            $this->BBCS['host'] = preg_replace("/[^0-9a-z-.:]/", "", $_SERVER['HTTP_HOST']);
        } else {
            $this->BBCS['host'] = 'errorhost.local';
        }
        $this->BBCS['host'] = rtrim($this->BBCS['host'], ".");

        // Get the request method and remove any non-alphabetic characters
        if (isset($_SERVER['REQUEST_METHOD'])) {
            $this->BBCS['request_method'] = (string) trim(preg_replace("/[^a-zA-Z]/", "", $_SERVER['REQUEST_METHOD']));
        } else {
            $this->BBCS['request_method'] = '';
        }

        // Get the remote IP address and remove any tags
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $this->BBCS['ip'] = trim(strip_tags($_SERVER['REMOTE_ADDR']));
        } else {
            bbcs_die('Remote Addr Error');
        }

        $this->parentWay(); //Checking the parent server

        /**
         * This code checks the validity of an IP address and sets the appropriate IP version and IP range.
         * If the IP address is not valid, it terminates the execution with an error message.
         */
        if (filter_var($this->BBCS['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $this->BBCS['ip_version'] = 4;
            $bbcsIParray = explode('.', $this->BBCS['ip']);
            $this->BBCS['ip_short'] = $bbcsIParray[0] . '.' . $bbcsIParray[1] . '.' . $bbcsIParray[2] . '.0/24';
        } elseif (filter_var($this->BBCS['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $this->BBCS['ip'] = bbcs_expandIPv6($this->BBCS['ip']);
            $this->BBCS['ip_version'] = 6;
            $bbcsIParray = explode(':', $this->BBCS['ip']);
            $this->BBCS['ip_short'] = $bbcsIParray[0] . ':' . $bbcsIParray[1] . ':' . $bbcsIParray[2] . ':' . $bbcsIParray[3] . ':0000:0000:0000:0000/64';
        } else {
            bbcs_die('IP Error - ' . $this->BBCS['ip']);
        }

        $this->BBCS['ipnum'] = bbcs_ipToNumeric($this->BBCS['ip']);

        $this->BBCS['country'] = $this->BBCS['empty'];

        $this->BBCS['ptr'] = bbcs_getPTR($this->BBCS['ip'], $this->BBCS['time']);

        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            $this->BBCS['scheme'] = trim(strip_tags($_SERVER['HTTP_X_FORWARDED_PROTO']));
        } elseif (isset($_SERVER['REQUEST_SCHEME'])) {
            $this->BBCS['scheme'] = trim(strip_tags($_SERVER['REQUEST_SCHEME']));
        } else {
            $this->BBCS['scheme'] = 'https';
        }

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $this->BBCS['useragent'] = trim(strip_tags($_SERVER['HTTP_USER_AGENT']));
        } else {
            $this->BBCS['useragent'] = '';
        }

        if (isset($_SERVER['REQUEST_URI'])) {
            $this->BBCS['uri'] = trim(strip_tags($_SERVER['REQUEST_URI']));
            $this->BBCS['uri'] = preg_replace('/\/+/', '/', $this->BBCS['uri']);
        } else {
            $this->BBCS['uri'] = '/';
        }

        if (isset($_SERVER['HTTP_REFERER'])) {
            $this->BBCS['referer'] = trim(strip_tags($_SERVER['HTTP_REFERER']));
            $this->BBCS['refhost'] = preg_replace("/[^0-9a-z-.:]/", "", (string)parse_url($this->BBCS['referer'], PHP_URL_HOST));
            if ($this->BBCS['referer'] != '' && $this->BBCS['refhost'] == '') {
                $this->BBCS['refhost'] = preg_replace("/[^0-9a-z-.]/", "", $this->BBCS['referer']);
            }
            $this->BBCS['refhost_scheme'] = preg_replace("/[^a-z]/", "", (string)parse_url($this->BBCS['referer'], PHP_URL_SCHEME));
        } else {
            $this->BBCS['referer'] = '';
            $this->BBCS['refhost'] = '';
            $this->BBCS['refhost_scheme'] = '';
        }

        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $this->BBCS['accept_lang'] = trim(strip_tags($_SERVER['HTTP_ACCEPT_LANGUAGE']));
            $this->BBCS['lang'] = mb_substr(mb_strtolower(trim(preg_replace("/[^a-zA-Z]/", "", $_SERVER['HTTP_ACCEPT_LANGUAGE'])), 'UTF-8'), 0, 2, 'utf-8');
        } else {
            $this->BBCS['accept_lang'] = '';
            $this->BBCS['lang'] = '';
        }

        if (isset($_SERVER['SERVER_PROTOCOL'])) {
            $this->BBCS['protocol'] = trim(strip_tags($_SERVER['SERVER_PROTOCOL']));
        } else {
            $this->BBCS['protocol'] = 'HTTP/1.0';
        }

        if (isset($_SERVER['HTTP_ACCEPT'])) {
            $this->BBCS['http_accept'] = trim(strip_tags($_SERVER['HTTP_ACCEPT']));
        } else {
            $this->BBCS['http_accept'] = '';
        }

        $this->BBCS['page'] = $this->BBCS['scheme'] . '://' . $this->BBCS['host'] . $this->BBCS['uri'];

        if ($this->BBCS['del_ref_query_string'] == 1) {
            $this->BBCS['save_referer'] = explode('?', $this->BBCS['referer']);
            $this->BBCS['save_referer'] = $this->BBCS['save_referer'][0];
        } else {
            $this->BBCS['save_referer'] = $this->BBCS['referer'];
        }

        if ($this->BBCS['del_page_query_string'] == 1) {
            $this->BBCS['save_page'] = explode('?', $this->BBCS['page']);
            $this->BBCS['save_page'] = $this->BBCS['save_page'][0];
        } else {
            $this->BBCS['save_page'] = $this->BBCS['page'];
        }

        $BBCS = $this->BBCS;
    }

    public function collectCloud()
    {
        global $BBCS;           
        $this->BBCS['license_status'] = $this->check_and_get_license_status();
        if ($this->BBCS['license_status'] !== null) {
            if (($this->BBCS['license_status'] == 'free') || ($this->BBCS['license_status'] == 'pro')){
                $data = [
                    'bbcs_license' => $this->BBCS['license_key'],
                    'ip' => $this->BBCS['ip']
                ];
                $cloud = bbcs_sendToCloud($data, BOTBLOCKER_API_URL);
                if ($cloud === false) {
                    $cloud = bbcs_sendToCloud($data, BOTBLOCKER_API_GS_URL);
                }
                if ($cloud === false) {
                    $this->geoFromIp();
                }
                $this->BBCS['country'] = $cloud['country'];
                $this->BBCS['cidr'] = $cloud['cidr'];
                $this->BBCS['asname'] = $cloud['asname'];
                $this->BBCS['asnum'] = $cloud['asnum'];
                $this->BBCS['hosting'] = $cloud['hosting'];
            }
        } else {
            $this->geoFromIp();
        }

        // for China, disable the recaptcha check:
        if ($this->BBCS['country'] == 'CN') {
            $this->BBCS['recaptcha_check'] = 0;
        }

        $BBCS = $this->BBCS;    
    }

    public function selectMode()
    {
        global $BBCS;           
        $this->BBCS['request_mode'] = 'x' . md5($this->BBCS['license_email'] . 'botblocker');

        if ($this->BBCS['request_method'] == 'POST' && isset($_POST[$this->BBCS['request_mode']])) {
            if ($_POST[$this->BBCS['request_mode']] == 'botblocker') {
                $BBCS = $this->BBCS;
                require_once($this->BBCS['dirs']['root'] . '/botblocker-local.php');
            } elseif ($_POST[$this->BBCS['request_mode']] == 'post') {
                $BBCS = $this->BBCS;
                require_once($this->BBCS['dirs']['root'] . '/botblocker-post.php');
            } elseif ($_POST[$this->BBCS['request_mode']] == 'img') {
                if (isset($_POST['img'])) {
                    $_POST['img'] = (int) preg_replace("/[^0-9]/", "", $_POST['img']);
                } else {
                    bbcs_die('Image Error - Empty');
                }
                if (isset($_POST['time'])) {
                    $_POST['time'] = (int) preg_replace("/[^0-9]/", "", $_POST['time']);
                } else {
                    bbcs_die('Time Error - Empty');
                }
                if ($this->BBCS['time'] - $_POST['time'] > 60) { // TODO: Timeout to options
                    bbcs_die('Time Error - Expired');
                }
                $imagePath = $this->BBCS['dirs']['public'] . 'img/'.$this->BBCS['bbcs_captcha_img_pack'].'/' . $_POST['img'] . '.jpg';
                if (file_exists($imagePath)) {
                    header('Content-Type: image/jpeg');
                    header('Content-Length: ' . filesize($imagePath));
                    readfile($imagePath);
                } else {
                    bbcs_die('404 - Image not found ' . $imagePath);
                }
            }
            bbcs_die();
        }
        $BBCS = $this->BBCS;    
    }
  
    public function finish($echo = 0){
        global $BBCS;           
        $this->finishTime = time();
        $this->BBCS['exec_time'] = $this->getExecutionTime();
        if(!empty($echo) && $echo === 1){
            echo '<!-- BotBlocker Execution Time: ' . $this->BBCS['exec_time'] . ' -->';
        }
        $BBCS = $this->BBCS;    
    }

    /**
     * Returns the execution time of the bot blocking process.
     *
     * @return int|null The execution time in seconds or null if start and finish times are not set.
     */
    public function getExecutionTime()
    {
        if (isset($this->startTime) && isset($this->finishTime)) {
            return $this->finishTime - $this->startTime;
        }
        return null;
    }    

    public function check_and_get_license_status()
    {
        $license_type = bbcs_getLicenseType();
        $license_key = bbcs_getLicenseKey();
    
        if ($license_type === 'pro') {
            if (preg_match('/^[1M|1B|1S]-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[A-Z0-9]{6}-[0-9a-f]{2}$/', $license_key)) {
                return 'pro';
            } else {
                return 'free';
            }
        } elseif ($license_type === 'free') {
            if (preg_match('/^[1M|1B|1S]-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[A-Z0-9]{6}-[0-9a-f]{2}$/', $license_key)) {
                return 'free';
            } else {
                return 'pro';
            }
        }
        return null;
    }

    private function checkSecretParam()
    {
        global $BBCS; 
        if ($this->BBCS['secret_botblocker_get_param'] != '' && $this->BBCS['secret_botblocker_get_param'] != $this->BBCS['empty']) {
            if (isset($_GET[$this->BBCS['secret_botblocker_get_param']]) || isset($_COOKIE[$this->BBCS['secret_botblocker_get_param']])) {
                $this->BBCS['x-robots-tag']['noindex'] = 'noindex';
                bbcs_setcookie($this->BBCS['secret_botblocker_get_param'], 1, $this->BBCS['time'] + 2592000, true, $this->BBCS['samesite']); 
                $this->BBCS['disable'] = 1;
            }
        }
        $BBCS = $this->BBCS;
    }

    public function headerProcess()
    {
        global $BBCS; 
        if ($this->BBCS['noarchive'] == 1) {
            $this->BBCS['x-robots-tag']['noarchive'] = 'noarchive';
        }

        if (isset($_GET['utm_referrer']) && $this->BBCS['utm_noindex'] == 1) {
            $this->BBCS['x-robots-tag']['noindex'] = 'noindex';
        }
        $BBCS = $this->BBCS;
    }

    public function cookieProcess()
    {
        global $BBCS; 

        if (isset($_COOKIE['_ym_uid'])) {
            $this->BBCS['ym_uid'] = trim(preg_replace("/[^0-9]/", "", $_COOKIE['_ym_uid']));
        } else {
            $this->BBCS['ym_uid'] = '';
        }

        if (isset($_COOKIE['_ga'])) {
            $this->BBCS['ga_uid'] = trim(preg_replace("/[^a-zA-Z0-9\.]/", "", $_COOKIE['_ga']));
        } else {
            $this->BBCS['ga_uid'] = '';
        }

        if (isset($_COOKIE[$this->BBCS['cookie'] . '_hits'])) {
            $this->BBCS['botblocker_hits'] = (int)trim(preg_replace("/[^0-9]/", "", $_COOKIE[$this->BBCS['cookie'] . '_hits'])) + 1;
        } else {
            $this->BBCS['botblocker_hits'] = 1;
        }

        // User id in main cookie:
        if (isset($_COOKIE[$this->BBCS['cookie']])) {
            $this->BBCS['uid'] = preg_replace('/[^a-zA-Z0-9]/', '', $_COOKIE[$this->BBCS['cookie']]);
        } else {
            $this->BBCS['uid'] = bbcs_RandomWord(30);
            bbcs_setcookie($this->BBCS['cookie'], $this->BBCS['uid'], $this->BBCS['time'] + 31536000, false, $this->BBCS['samesite']); // per Year // TODO - set cookie life days in settings
        }

        $this->BBCS['botblocker_cs'] = isset($_COOKIE[$this->BBCS['uid']]) ? trim(strip_tags($_COOKIE[$this->BBCS['uid']])) : '';

        $bbcs_cookie = explode('-', $this->BBCS['botblocker_cs']);
        $this->BBCS['cookie_date'] = isset($bbcs_cookie[1]) ? (int)trim($bbcs_cookie[1]) : $this->BBCS['time'] - 864100; // TODO - set cookie life days in settings
        $this->BBCS['botblocker_cookies'] = isset($bbcs_cookie[0]) ? trim($bbcs_cookie[0]) : 0;
        $this->BBCS['botblocker_cookies_valid'] = md5($this->BBCS['salt'] . $this->BBCS['license_pass'] . $this->BBCS['host'] . $this->BBCS['useragent'] . $this->BBCS['ip'] . $this->BBCS['cookie_date']);

        if ($this->BBCS['time'] - $this->BBCS['cookie_date'] > 864000) { // 10 days // TODO - set cookie life days in settings
            $this->BBCS['botblocker_cookies'] = 'delete';
        }
        $BBCS = $this->BBCS;
    }

    public function cookieCounter(){
        global $BBCS;
        if ($this->BBCS['botblocker_hits'] > $this->BBCS['hits_per_user']) {
            bbcs_setcookie($this->BBCS['cookie'] . '_hits', 0, $this->BBCS['time'] - 100, false, $this->BBCS['samesite']);
            bbcs_setcookie($this->BBCS['uid'], 0, $this->BBCS['time'] - 100, false, $this->BBCS['samesite']);
        } else {
            bbcs_setcookie($this->BBCS['cookie'] . '_hits', $this->BBCS['botblocker_hits'], $this->BBCS['time'] + 86400, false, $this->BBCS['samesite']);
        }
        if ($this->BBCS['botblocker_log_local'] == 1) {
            $this->BBCS['reason'] = '3'; // Allow cookies (LOCAL)
            $this->storeData();
        }
        $this->BBCS['good_bot'] = 0;
        $BBCS = $this->BBCS;
    }

    public function checkReferGet()
    {
        global $BBCS;           
        global $wpdb;
        if ($this->BBCS['check_get_ref'] == 1) {
            $bbcs_query = parse_url($this->BBCS['referer']);
            if (isset($bbcs_query['query'])) {
                mb_parse_str($bbcs_query['query'], $mb_parse_str);
                $this->BBCS['bad_get_ref'] = explode(' ', $this->BBCS['bad_get_ref']);
                foreach ($this->BBCS['bad_get_ref'] as $bad_get_ref) {
                    if (isset($mb_parse_str[$bad_get_ref])) {
                        $this->BBCS['suspect'] = 1;
                        $this->BBCS['result'] = $wpdb->prepare('GRAY By rule (from conf): %s', $bad_get_ref);
                        break;
                    }
                }
            }
            // TODO ACTION by SUSPECT / RESULT
        }
        $BBCS = $this->BBCS;    
    }

    private function geoFromIp(){
        global $BBCS;
    
        // TODO: store data to REDIS
        // TODO: store data to MMC
        $this->BBCS['country'] = $this->BBCS['empty'];
        $this->BBCS['cidr'] = $this->BBCS['empty'];
        $this->BBCS['asname'] = $this->BBCS['empty'];
        $this->BBCS['asnum'] = $this->BBCS['empty'];
        $this->BBCS['hosting'] = $this->BBCS['empty'];

        if ($this->BBCS['ip_version'] == 4) {
            require_once $this->BBCS['dirs']['vendor'] . 'SypexGeo/SxGeo.php';
            $SxGeo = new SxGeo('SxGeo.dat');
            $country = $SxGeo->getCountry($this->BBCS['ip']);

            if (empty($country)) {
                $ip2c = file_get_contents('https://ip2c.org/' . $this->BBCS['ip']);
                if ($ip2c !== false && strlen($ip2c) > 0) {
                    $reply_ip2c = explode(';', $ip2c);
                    if ($reply_ip2c[0] == '1' && isset($reply_ip2c[1])) {
                        $this->BBCS['country'] = mb_strtoupper($reply_ip2c[1]); 
                    }
                }
            } else {
                $this->BBCS['country'] = $country; 
            }
        }

        $BBCS = $this->BBCS;
    }
    

    private function loadData()
    {
        global $BBCS;

        if (file_exists($this->BBCS['dirs']['data'] . 'search_engines.php')) {
            $rules = include($this->BBCS['dirs']['data'] . 'search_engines.php');
            $this->BBCS['bbcs_rule'] = $rules['bbcs_rule'] ?? [];
            $this->BBCS['bbcs_se'] = $rules['bbcs_se'] ?? [];
        } else {
            $this->BBCS['bbcs_rule'] = [];
            $this->BBCS['bbcs_se'] = [];
        }
    
        if (file_exists($this->BBCS['dirs']['data'] . 'data.php')) {
            $data = include($this->BBCS['dirs']['data'] . 'data.php');
            $this->BBCS['bbcs_good_bots'] = $data['bbcs_good_bots'] ?? [];
        } else {
            $this->BBCS['bbcs_good_bots'] = [];
        }
    
        if (file_exists($this->BBCS['dirs']['data'] . 'proxy.php')) {
            $proxy = include($this->BBCS['dirs']['data'] . 'proxy.php');
            $this->BBCS['bbcs_proxy'] = $proxy['bbcs_proxy'] ?? [];
        } else {
            $this->BBCS['bbcs_proxy'] = [];
        }

        if (file_exists($this->BBCS['dirs']['data'] . 'path.php')) {
            $path = include($this->BBCS['dirs']['data'] . 'path.php');
            $this->BBCS['bbcs_path'] = $path['bbcs_path'] ?? [];
        } else {
            $this->BBCS['bbcs_path'] = [];
        }

        // Extra data
        // Logo:
        $this->BBCS['logo'] = BOTBLOCKER_URL . 'admin/img/logo-small-transparent.png';
        $this->BBCS['logo_webp'] = BOTBLOCKER_URL . 'admin/img/logo-small-transparent.webp';

        $BBCS = $this->BBCS;
    }

    public function loadSettings() {
        global $BBCS;
    
        $settingsFile = $this->BBCS['dirs']['data'] . 'settings.php';
    
        if (file_exists($settingsFile)) {
            $settings = include($settingsFile);
            foreach ($settings as $key => $value) {
                $this->BBCS[$key] = $value;
            }
        } else {
            global $wpdb;
            $table_name = $wpdb->prefix .BOTBLOCKER_TABLE_PREFIX. 'settings';
    
            $results = $wpdb->get_results("SELECT `key`, `value` FROM $table_name", ARRAY_A);
    
            $settings = [];
            foreach ($results as $row) {
                $key = $row['key'];
                $value = $row['value'];
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $settings[$key] = $decoded;
                } else {
                    if (is_numeric($value)) {
                        $settings[$key] = $value + 0;
                    } else {
                        $settings[$key] = $value;
                    }
                }
            }
    
            $settingsContent = "<?php\nreturn " . var_export($settings, true) . ";\n";
            file_put_contents($settingsFile, $settingsContent);
    
            foreach ($settings as $key => $value) {
                $this->BBCS[$key] = $value;
            }
        }
    
        $BBCS = $this->BBCS;
    }

    private function initialConfig()
    {
        global $BBCS;
        $initConfig = [
            'colors' => ['BLACK', 'GRAY', 'RED', 'YELLOW', 'GREEN', 'BLUE', 'MAROON', 'PURPLE'],
            'country' => $this->BBCS['empty'],
            'cidr' => $this->BBCS['empty'],
            'asname' => $this->BBCS['empty'],
            'asnum' => $this->BBCS['empty'],
            'time' => time(),
            'result' => $this->BBCS['empty'],
            'hosting' => $this->BBCS['empty'],
            'rowid' => 0, 
            'x-robots-tag' => [],
            'suspect' => 0, 
            'timezone' => $this->BBCS['empty'],
            'js_error_msg' => 'Your request has been denied.',
            'tpl_lang' => $this->BBCS['empty'],
            'bad_get_ref' => 'q text utm_source yclid ysclid utm_referrer',
            'secret_botblocker_get_param' => $this->BBCS['empty'],
            'del_ref_query_string' => 0,
            'del_page_query_string' => 0,
            'bbdet' => $this->BBCS['empty']
        ];

        foreach ($initConfig as $key => $value) {
            $this->BBCS[$key] = $value;
        }

        $BBCS = $this->BBCS;
    }

    public function loadDirs()
    {
        global $BBCS;
        
        $this->BBCS['botblockerUrl'] = BOTBLOCKER_URL;
        $this->BBCS['version'] = BOTBLOCKER_VERSION;
    
        $this->BBCS['dirs'] = array(
            'root'      => BOTBLOCKER_DIR,
            'public'    => BOTBLOCKER_DIR . 'public/',
            'languages' => BOTBLOCKER_DIR . 'languages/',
            'includes'  => BOTBLOCKER_DIR . 'includes/',
            'admin'     => BOTBLOCKER_DIR . 'admin/',
            'data'      => BOTBLOCKER_DIR . 'data/',
            'vendor'    => BOTBLOCKER_DIR . 'vendor/',
        );
    
        $BBCS = $this->BBCS;
    }

    public function identifyByUA()
    {
        global $BBCS;               
        if ($this->BBCS['get_browser_type'] == 1) {
            $this->BBCS['browser'] = bbcs_getBrowserType($this->BBCS['useragent']);
        } else {
            $this->BBCS['browser'] = $this->BBCS['empty'];
        }
        if ($this->BBCS['get_os_type'] == 1) {
            $this->BBCS['os'] = bbcs_getOSType($this->BBCS['useragent']);
        } else {
            $this->BBCS['os'] = $this->BBCS['empty'];
        }
        if ($this->BBCS['get_device_type'] == 1) {
            $this->BBCS['device'] = bbcs_getDeviceType($this->BBCS['useragent']);
        } else {
            $this->BBCS['device'] = $this->BBCS['empty'];
        }
        $BBCS = $this->BBCS;        
    }

    public function isHuman(){
        global $BBCS;               
        if ($this->BBCS['botblocker_cookies_valid'] != $this->BBCS['botblocker_cookies']) {
            $this->check();
            bbcs_die();
        }
        $BBCS = $this->BBCS;        
    }

    public function isHosting(){
        global $BBCS;               
            if ($this->BBCS['hosting_block'] == 1 && in_array($this->BBCS['hosting'], [1, '1'], true) && !isset($this->BBCS['good_bot'])) {
                $this->BBCS['result'] = 'BLOCK By rule: Hosting or Bad IP';
                $BBCS = $this->BBCS;
                $this->block();
                bbcs_die();
            }
        $BBCS = $this->BBCS;            
    }

    public function isProxy(){
        global $BBCS;               
        // Replace with the actual client IP address passed via the header
        if (filter_var($this->BBCS['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            foreach ($this->BBCS['bbcs_proxy'] as $proxy_mask => $proxy_attr) {
                if (bbcs_netMatch($proxy_mask, $this->BBCS['ip']) == 1 && isset($_SERVER[$proxy_attr])) {
                    $this->BBCS['ip'] = $_SERVER[$proxy_attr];
                    $this->BBCS['is_proxy'] = 'PROXY_v4'; 
                    break;
                }
            }
        }

        if (filter_var($this->BBCS['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            foreach ($this->BBCS['bbcs_proxy'] as $proxy_mask => $proxy_attr) {
                if (bbcs_netMatch($proxy_mask, $this->BBCS['ip']) == 1 && isset($_SERVER[$proxy_attr])) {
                    $this->BBCS['ip'] = $_SERVER[$proxy_attr];
                    $this->BBCS['is_proxy'] = 'PROXY_v6'; 
                    break;
                }
            }
        }     

        $proxy_headers = bbcs_loadProxyHeaders();
        
        if ($this->BBCS['is_proxy'] === $this->BBCS['empty']) {
            foreach ($proxy_headers as $header) {
                if (!empty($_SERVER[$header])) {
                    $this->BBCS['is_proxy'] = 'DETECTED';
                    break; 
                }
            }
        }
        $BBCS = $this->BBCS;                
    }

    public function isValidReferer(){
        global $BBCS;           
        if ($this->BBCS['block_fake_ref'] == 1 && $this->BBCS['referer'] != '' && !isset($this->BBCS['good_bot'])) {
            $parse_url = parse_url($this->BBCS['referer']);
            if (!isset($parse_url['scheme']) or !isset($parse_url['path'])) {
                $this->BBCS['result'] = 'BLOCK By rule: FAKE REFERER';
                $this->block();
                bbcs_die();
            }
        }
        $BBCS = $this->BBCS;           
    }

    public function isSelfRequest()
    {
        global $BBCS;           
        //TODO
        // Check Heartbeat and plugins (AJAX)
        $BBCS = $this->BBCS;    
    }

    public function isFakeBrowser()
    {
        global $BBCS;               
        $browserAnalyzeData = $BBCS['bbdet']; 
        // TODO
        // Check array of tags
        // Sync BBDET
        $BBCS = $this->BBCS;            
    }
 
    public function isAnalytics()
    {
        global $BBCS;           

        $this->BBCS['analytics_domains'] = bbcs_loadAnalyticsDomains();

        $ip_address = $this->BBCS['ip'];
        $host_by_ip = gethostbyaddr($ip_address); // TODO - Cache PTR

        if ($host_by_ip !== false) {
            foreach ($this->BBCS['analytics_domains'] as $domain) {
                if (stripos($host_by_ip, $domain) !== false) {
                    $this->BBCS['good_bot'] = 1;
                    break;
                }
            }
        }

        $BBCS = $this->BBCS;    
    }

    public function checkLastRule()
    { // TODO - 2 step
        global $BBCS;
        if ($this->BBCS['last_rule'] != '' && !isset($this->BBCS['good_bot'])) {
            $rule_message = 'By rule: LAST RULE';
            if ($this->BBCS['last_rule'] == 'allow') {
                $BBCS = $this->BBCS;
                $this->allow(['search' => 'LAST RULE']);
            } elseif ($this->BBCS['last_rule'] == 'block') {
                $this->toBlock('BLOCK ' . $rule_message);
            } elseif ($this->BBCS['last_rule'] == 'dark') {
                $this->toDark('DARK ' . $rule_message);
            } elseif ($this->BBCS['last_rule'] == 'gray') {
                $this->toGray('GRAY ' . $rule_message);
            }
        }
    
        $BBCS = $this->BBCS; 
    }

    public function checkPath()
    {
        global $BBCS; 
    
        foreach ($this->BBCS['bbcs_path'] as $bbcs_line => $bbcs_sign) {
            if (stripos($this->BBCS['uri'], $bbcs_line) !== false) {
                if ($bbcs_sign == 'block') {
                    $this->toBlock('BLOCK By rule (url part): ' . $bbcs_line);
                } elseif ($bbcs_sign == 'dark') {
                    $this->toDark('DARK By rule (url part): ' . $bbcs_line);
                } elseif ($bbcs_sign == 'gray') {
                    $this->toGray('GRAY By rule (url part): ' . $bbcs_line);
                } elseif ($bbcs_sign == 'allow') {
                    $this->BBCS['good_bot'] = 0;
                        if ($this->BBCS['botblocker_log_allow'] == 1) {
                        $this->BBCS['reason'] = '4'; // Allow Path
                        $this->storeData();
                    }
                    break; 
                }
            }
        }
    
        $BBCS = $this->BBCS; 
    }

    public function checkConfigRules()
    {
        global $BBCS;           
        global $wpdb;

        foreach ($this->BBCS['bbcs_se'] as $bbcs_line => $bbcs_sign) {
            if (stripos($this->BBCS['useragent'], $bbcs_line, 0) !== false) {
                if ($this->BBCS['bbcs_rule'][$bbcs_line] == 'block') {
                    $result = $wpdb->prepare('BLOCK By rule (user-agent part): %s', $bbcs_line);
                    $this->toBlock($result);
                } elseif ($this->BBCS['bbcs_rule'][$bbcs_line] == 'dark') {
                    $result = $wpdb->prepare('DARK By rule (user-agent part): %s', $bbcs_line);
                    $this->toDark($result);
                } elseif ($this->BBCS['bbcs_rule'][$bbcs_line] == 'gray') {
                    $result = $wpdb->prepare('GRAY By rule (user-agent part): %s', $bbcs_line);
                    $this->toGray($result);
                }
            }
            if (stripos($this->BBCS['useragent'], $bbcs_line, 0) !== false && $this->BBCS['bbcs_rule'][$bbcs_line] == 'allow') {
                if (bbcs_testWhiteBot($this->BBCS['ip'], $bbcs_sign, $this->BBCS['time']) == 1) {
                    // if realy white bot by PTR
                    if (!in_array('.', $this->BBCS['bbcs_se'][$bbcs_line])) {
                        // save ip to white list by ptr:
                        $ips = bbcs_IpRange($this->BBCS['ip_short']);
                        $table_name = $wpdb->prefix .BOTBLOCKER_TABLE_PREFIX. 'ipv' . $this->BBCS['ip_version'] . 'rules';
                        $data = array(
                            'priority' => '10',
                            'search' => $this->BBCS['ip_short'],
                            'ip1' => bbcs_ipToNumeric($ips[0]),
                            'ip2' => bbcs_ipToNumeric($ips[1]),
                            'rule' => 'allow',
                            'comment' => $this->BBCS['useragent'] . ' (ip: ' . $this->BBCS['ip'] . ')',
                            'expires' => ($this->BBCS['time'] + 7776000)
                        );
                        $wpdb->insert($table_name, $data);
                    }
                    $this->BBCS['result'] = $wpdb->prepare('GOODIP By rule (user-agent part): %s', $bbcs_line);
                    $this->BBCS['good_bot'] = 1;
                    break;
                } else {
                    if ($this->BBCS['botblocker_log_fake'] == 1) {
                        $this->BBCS['result'] = $wpdb->prepare('FAKE By rule (user-agent part): %s', $bbcs_line);
                        $this->BBCS['reason'] = '7'; // FAKE bot 
                        $this->storeData();
                    }
                    $this->denied();
                }
                break;
            }
        }
        $BBCS = $this->BBCS;    
    }

    public function checkRules()
    {
        global $BBCS;           
        global $wpdb;
        $bbcs_search = array();

        // TODO  - check resulted request

        if (!empty($this->BBCS['useragent'])) {
            $bbcs_search[] = $wpdb->prepare("search=%s", 'useragent=' . $this->BBCS['useragent']);
        }
        if (!empty($this->BBCS['country'])) {
            $bbcs_search[] = $wpdb->prepare("search=%s", 'country=' . $this->BBCS['country']);
        }
        if (!empty($this->BBCS['lang'])) {
            $bbcs_search[] = $wpdb->prepare("search=%s", 'lang=' . $this->BBCS['lang']);
        }
        if (!empty($this->BBCS['refhost'])) {
            $bbcs_search[] = $wpdb->prepare("search=%s", 'referer=' . $this->BBCS['refhost']);
        }
        if (!empty($this->BBCS['ym_uid'])) {
            $bbcs_search[] = $wpdb->prepare("search=%s", 'ym_uid=' . $this->BBCS['ym_uid']);
        }
        if (!empty($this->BBCS['ga_uid'])) {
            $bbcs_search[] = $wpdb->prepare("search=%s", 'ga_uid=' . $this->BBCS['ga_uid']);
        }
        $this->BBCS['ptr_arr'] = explode('.', $this->BBCS['ptr']);
        $this->BBCS['ptr_arr'] = array_reverse($this->BBCS['ptr_arr'], false);
        if (isset($this->BBCS['ptr_arr'][1])) {
            $bbcs_search[] = $wpdb->prepare("search=%s", 'ptr=' . $this->BBCS['ptr_arr'][1] . '.' . $this->BBCS['ptr_arr'][0]);
        }
        if (isset($this->BBCS['ptr_arr'][2])) {
            $bbcs_search[] = $wpdb->prepare("search=%s", 'ptr=' . $this->BBCS['ptr_arr'][2] . '.' . $this->BBCS['ptr_arr'][1] . '.' . $this->BBCS['ptr_arr'][0]);
        }
        if (!empty($this->BBCS['asname'])) {
            $bbcs_search[] = $wpdb->prepare("search=%s", 'asname=' . $this->BBCS['asname']);
        }
        if (!empty($this->BBCS['asnum'])) {
            $bbcs_search[] = $wpdb->prepare("search=%s", 'asnum=' . $this->BBCS['asnum']);
        }
        if (!empty($this->BBCS['uri'])) {
            $bbcs_search[] = $wpdb->prepare("search=%s", 'uri=' . $this->BBCS['uri']);
        }
        if (!empty($_SERVER['SCRIPT_NAME'])) {
            $bbcs_search[] = $wpdb->prepare("search=%s", 'scriptname=' . trim(strip_tags($_SERVER['SCRIPT_NAME'])));
        }
        if (!empty($this->BBCS['http_accept'])) {
            $bbcs_search[] = $wpdb->prepare("search=%s", 'httpaccept=' . trim(strip_tags($this->BBCS['http_accept'])));
        }
        if (!empty($bbcs_search)) {
            $query = "SELECT * FROM {$wpdb->prefix}bbcs_rules WHERE " . implode(' OR ', $bbcs_search) . " ORDER BY priority ASC";
            $bbcsTest = $wpdb->get_results($query);
        }
        foreach ($bbcsTest as $echo) {
            if ($echo->disable == '0') {
                if ($echo->rule == 'allow') {
                    $BBCS = $this->BBCS;
                    $this->allow($echo);
                    break;
                } elseif ($echo->rule == 'block') {
                    $result = $wpdb->prepare('BLOCK By rule: %s', $echo->search);
                    $this->toBlock($result);
                } elseif ($echo->rule == 'dark') {
                    $this->BBCS['rowid'] = $echo->id;
                    $result = $wpdb->prepare('DARK By rule: %s', $echo->search);
                    $this->toDark($result);
                } elseif ($echo->rule == 'gray') {
                    $result = $wpdb->prepare('GRAY By rule: %s', $echo->search);
                    $this->toGray($result);
                }
            }
        }
        $BBCS = $this->BBCS;    
    }

    public function checkIpRules()
    {
        global $BBCS;           
        global $wpdb;
        $table_name = $wpdb->prefix .BOTBLOCKER_TABLE_PREFIX. 'ipv' . $this->BBCS['ip_version'] . 'rules';
        $bbcs_ip_test = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE disable = '0' AND ip1 <= %d AND ip2 >= %d ORDER BY priority ASC", $this->BBCS['ipnum'], $this->BBCS['ipnum']), ARRAY_A);
        if ($bbcs_ip_test) {
            // deleting at expired rule option
            if ($bbcs_ip_test['expires'] < $this->BBCS['time']) {
                $wpdb->delete($table_name, array('id' => $bbcs_ip_test['id']));
                $bbcs_ip_test['rule'] = 'gray';
            }
            if ($bbcs_ip_test['rule'] == 'allow') {
                $this->BBCS['good_bot'] = 1;
                $this->BBCS['result'] = $wpdb->prepare('GOODIP By rule: %s', $bbcs_ip_test['search']);
            } elseif ($bbcs_ip_test['rule'] == 'block') {
                $result = $wpdb->prepare('BLOCK By rule: %s', $bbcs_ip_test['search']);
                $this->toBlock($result);
            } elseif ($bbcs_ip_test['rule'] == 'dark') {
                $result = $wpdb->prepare('DARK By rule: %s', $bbcs_ip_test['search']);
                $this->toDark($result);
            } elseif ($bbcs_ip_test['rule'] == 'gray') {
                $result = $wpdb->prepare('GRAY By rule: %s', $bbcs_ip_test['search']);
                $this->toGray($result);
            }
        }
        $BBCS = $this->BBCS;    
    }        

    public function toBlock($result)
    {
        global $BBCS;
        $this->BBCS['result'] = $result;
        $BBCS = $this->BBCS;
        $this->block();
        bbcs_die();		
    }

    public function toDark($result)
    {
        global $BBCS;
        $this->BBCS['suspect'] = 2;
        $this->BBCS['result'] = $result;
        $BBCS = $this->BBCS;
        $this->check();
        bbcs_die();		
    }	

    public function toGray($result)
    {
        global $BBCS;
        $this->BBCS['suspect'] = 1;
        $this->BBCS['result'] = $result;
        $BBCS = $this->BBCS;
    }	

    public function allow($echo)
    {
        global $BBCS;           
        bbcs_setcookie($BBCS['uid'], md5($BBCS['salt'] . $BBCS['license_pass'] . $BBCS['host'] . $BBCS['useragent'] . $BBCS['ip'] . $BBCS['time']) . '-' . $BBCS['time'], $BBCS['time'] + 864000, false, $BBCS['samesite']);
        
        $this->BBCS['good_bot'] = 0;
        $search = isset($echo->search) ? esc_sql($echo->search) : '-';
        $this->BBCS['reason'] = '4'; // Allow by rule
        $this->BBCS['result'] = 'ALLOW By rule:'. $search;

        if ($BBCS['botblocker_log_allow'] == 1) {
            $this->storeData();
        }
        
        $BBCS = $this->BBCS;    
    }

    public function block($bbcs_ip_test = null)
    {
        global $BBCS;           

        if ($this->BBCS['botblocker_log_block'] == 1) {
            $this->BBCS['reason'] = '6'; // Block user
            $this->storeData();
        }
        if ($this->BBCS['iframe_stop'] == 1) {
            header('X-Frame-Options: SAMEORIGIN');
        }
          
          header('X-Robots-Tag: noindex');
          header($this->BBCS['protocol'] . ' ' . $this->BBCS['error_headers'][$this->BBCS['header_error_code']]);
          header('Status: ' . $this->BBCS['error_headers'][$this->BBCS['header_error_code']]);
          ob_start();
          require_once($this->BBCS['dirs']['public'] . 'denied.php');
          $error_tpl = ob_get_clean();
          $error_tpl = str_replace('<!--error-->', htmlspecialchars($this->BBCS['ip']) . ' ' . date('d.m.Y H:i:s', $this->BBCS['time']), $error_tpl);
          if (isset($bbcs_ip_test['expires']) && is_numeric($bbcs_ip_test['expires']) && $bbcs_ip_test['expires'] - $this->BBCS['time'] < 86401) {
              if ($this->BBCS['tpl_lang'] === $this->BBCS['empty']) {
                $this->BBCS['tpl_lang'] = $this->BBCS['lang'];
              }
              $lang_file = $this->BBCS['dirs']['languages'] . 'tpl/' . $this->BBCS['lang'] . '.php';
              if (file_exists($lang_file)) {
                  require_once($lang_file);
              }
              $secwait = $bbcs_ip_test['expires'] - $this->BBCS['time'] + 2;
              $ban_message = '<center><h2>' . bbcs_customTranslate('Your IP has been blocked.') . '</h2>
                  <h3>' . bbcs_customTranslate('Seconds left until the unlock:') . ' <span id="countdownTimer">' . $secwait . '</span></h3></center>
                  <script>
                  var count = ' . $secwait . ';
                  var countdown = setInterval(function() {
                      var timer = document.getElementById(\'countdownTimer\');
                      if (timer) {
                          timer.innerText = count;
                          count--;
                          if (count < 0) {
                              clearInterval(countdown);
                              location.reload();
                          }
                      }
                  }, 1000);
                  </script>
                  <style>.main_content {display: none;}</style>';
              
              $error_tpl = str_replace('<!--ip_ban_msg-->', $ban_message, $error_tpl);
          } else {
              $error_tpl = str_replace('<!--ip_ban_msg-->', '', $error_tpl);
          }
        $BBCS = $this->BBCS;    
        echo $error_tpl;
        unset($error_tpl);        
    }  
    
    public function check()
    {
        global $BBCS;           
        if ($this->BBCS['request_method'] == 'POST') {
            header('Location: ' . $this->BBCS['uri']);
            bbcs_die();
        }
        if ($this->BBCS['tpl_lang'] == $this->BBCS['empty']) {
            $this->BBCS['tpl_lang'] = $this->BBCS['lang'];
        }
        if (file_exists($this->BBCS['dirs']['languages'] . 'tpl/' . $this->BBCS['lang'] . '.php')) {
            require_once($this->BBCS['dirs']['languages'] . 'tpl/' . $this->BBCS['lang'] . '.php');
        }
        if ($this->BBCS['iframe_stop'] == 1) {
            header('X-Frame-Options: SAMEORIGIN');
        }
        
        header('Content-Type: text/html; charset=UTF-8');
        header('X-Robots-Tag: noindex');
        header($this->BBCS['protocol'] . ' ' . $this->BBCS['error_headers'][$this->BBCS['header_test_code']]);
        header('Status: ' . $this->BBCS['error_headers'][$this->BBCS['header_test_code']]);
        
        ob_start();
            require_once($this->BBCS['dirs']['public'] . 'tpl.php');
            $tpl = ob_get_clean();
        ob_start();
            require_once($this->BBCS['dirs']['public'] . 'js.php');
            $tpl_js = ob_get_clean();
                $tpl = str_replace('</body>', $tpl_js . '</body>', $tpl);
                $time = $this->BBCS['time'] ?? time();
                $replacements = [
                    'botblocker-btn-success' => 's' . md5('botblocker-btn-success' . $time),
                    'botblocker-btn-color' => 's' . md5('botblocker-btn-color' . $time)
                ];
                foreach ($replacements as $search => $replace) {
                    $tpl = str_replace($search, $replace, $tpl);
                }
        if ($this->BBCS['botblocker_log_tests'] == 1) {
            $this->BBCS['reason'] = '0'; // Check page show for user
            $this->storeData();
        }
        $BBCS = $this->BBCS;    
        echo $tpl;
        unset($tpl);
    }   

    public function denied()
    {
        global $BBCS;  
        header('X-Robots-Tag: noindex, noarchive');
        header($this->BBCS['protocol'] . ' ' . $this->BBCS['error_headers'][$this->BBCS['header_error_code']]);
        header('Status: ' . $this->BBCS['error_headers'][$this->BBCS['header_error_code']]);
        $error_tpl = file_get_contents($this->BBCS['dirs']['public'] . 'denied.php');
        $error_tpl = str_replace('ERROR', 'ERROR ' . $this->BBCS['ip'] . ' ' . date('d.m.Y H:i:s', $this->BBCS['time']), $error_tpl);
        echo $error_tpl;
        $BBCS = $this->BBCS;    
        bbcs_die();
    }

    public function storeData() {
        global $BBCS;  
        global $wpdb;
    
        $empty_value = isset($this->BBCS['empty']) ? $this->BBCS['empty'] : '-';
  
        $this->BBCS['cid'] = !empty($this->BBCS['cid']) ? sanitize_text_field($this->BBCS['cid']) : $empty_value;
        $this->BBCS['time'] = !empty($this->BBCS['time']) ? sanitize_text_field($this->BBCS['time']) : $empty_value;
        $this->BBCS['ip'] = !empty($this->BBCS['ip']) ? sanitize_text_field($this->BBCS['ip']) : $empty_value;
        $this->BBCS['ptr'] = !empty($this->BBCS['ptr']) ? sanitize_text_field($this->BBCS['ptr']) : $empty_value;
        $this->BBCS['useragent'] = !empty($this->BBCS['useragent']) ? sanitize_text_field($this->BBCS['useragent']) : $empty_value;
        $this->BBCS['uid'] = !empty($this->BBCS['uid']) ? sanitize_text_field($this->BBCS['uid']) : $empty_value;
        $this->BBCS['country'] = !empty($this->BBCS['country']) ? sanitize_text_field($this->BBCS['country']) : $empty_value;
        $this->BBCS['save_referer'] = !empty($this->BBCS['save_referer']) ? sanitize_text_field($this->BBCS['save_referer']) : $empty_value;
        $this->BBCS['save_page'] = !empty($this->BBCS['save_page']) ? sanitize_text_field($this->BBCS['save_page']) : $empty_value;
        $this->BBCS['accept_lang'] = !empty($this->BBCS['accept_lang']) ? sanitize_text_field($this->BBCS['accept_lang']) : $empty_value;
        $this->BBCS['reason'] = !empty($this->BBCS['reason']) ? sanitize_text_field($this->BBCS['reason']) : $empty_value;
        $this->BBCS['recaptcha'] = !empty($this->BBCS['recaptcha']) ? sanitize_text_field($this->BBCS['recaptcha']) : $empty_value;
        $this->BBCS['refhost'] = !empty($this->BBCS['refhost']) ? sanitize_text_field($this->BBCS['refhost']) : $empty_value;
        $this->BBCS['adblock'] = !empty($this->BBCS['adblock']) ? sanitize_text_field($this->BBCS['adblock']) : $empty_value;
        $this->BBCS['asnum'] = !empty($this->BBCS['asnum']) ? sanitize_text_field($this->BBCS['asnum']) : $empty_value;
        $this->BBCS['asname'] = !empty($this->BBCS['asname']) ? sanitize_text_field($this->BBCS['asname']) : $empty_value;
        $this->BBCS['result'] = !empty($this->BBCS['result']) ? sanitize_text_field($this->BBCS['result']) : $empty_value;
        $this->BBCS['http_accept'] = !empty($this->BBCS['http_accept']) ? sanitize_text_field($this->BBCS['http_accept']) : $empty_value;
        $this->BBCS['request_method'] = !empty($this->BBCS['request_method']) ? sanitize_text_field($this->BBCS['request_method']) : $empty_value;
        $this->BBCS['ym_uid'] = !empty($this->BBCS['ym_uid']) ? sanitize_text_field($this->BBCS['ym_uid']) : $empty_value;
        $this->BBCS['ga_uid'] = !empty($this->BBCS['ga_uid']) ? sanitize_text_field($this->BBCS['ga_uid']) : $empty_value;
        $this->BBCS['ip_short'] = !empty($this->BBCS['ip_short']) ? sanitize_text_field($this->BBCS['ip_short']) : $empty_value;
        $this->BBCS['hosting'] = !empty($this->BBCS['hosting']) ? sanitize_text_field($this->BBCS['hosting']) : $empty_value;
        $this->BBCS['botblocker_hits'] = !empty($this->BBCS['botblocker_hits']) ? sanitize_text_field($this->BBCS['botblocker_hits']) : $empty_value;
        $this->BBCS['browser'] = !empty($this->BBCS['browser']) ? sanitize_text_field($this->BBCS['browser']) : $empty_value;
        $this->BBCS['os'] = !empty($this->BBCS['os']) ? sanitize_text_field($this->BBCS['os']) : $empty_value;
        $this->BBCS['device'] = !empty($this->BBCS['device']) ? sanitize_text_field($this->BBCS['device']) : $empty_value;
    
        // JS FIELDS
        $this->BBCS['js_w'] = !empty($this->BBCS['js_w']) ? sanitize_text_field($this->BBCS['js_w']) : $empty_value;
        $this->BBCS['js_h'] = !empty($this->BBCS['js_h']) ? sanitize_text_field($this->BBCS['js_h']) : $empty_value;
        $this->BBCS['js_cw'] = !empty($this->BBCS['js_cw']) ? sanitize_text_field($this->BBCS['js_cw']) : $empty_value;
        $this->BBCS['js_ch'] = !empty($this->BBCS['js_ch']) ? sanitize_text_field($this->BBCS['js_ch']) : $empty_value;
        $this->BBCS['js_co'] = !empty($this->BBCS['js_co']) ? sanitize_text_field($this->BBCS['js_co']) : $empty_value;
        $this->BBCS['js_pi'] = !empty($this->BBCS['js_pi']) ? sanitize_text_field($this->BBCS['js_pi']) : $empty_value;

        $exec_time = round(microtime(true) - $this->BBCS['time'], 3);

        $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'hits';
    
        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO $table_name (cid, date, ip, ptr, useragent, uid, country, referer, page, lang, generation, passed, recaptcha, js_w, js_h, js_cw, js_ch, js_co, js_pi, refhost, adblock, asnum, asname, result, http_accept, method, ym_uid, ga_uid, ip_short, hosting, hit, browser, os, device) 
                VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %f, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                $this->BBCS['cid'],
                $this->BBCS['time'],
                $this->BBCS['ip'],
                $this->BBCS['ptr'],
                $this->BBCS['useragent'],
                $this->BBCS['uid'],
                $this->BBCS['country'],
                $this->BBCS['save_referer'],
                $this->BBCS['save_page'],
                $this->BBCS['accept_lang'],
                $exec_time,  // %f
                $this->BBCS['reason'],
                $this->BBCS['recaptcha'],
                $this->BBCS['js_w'],
                $this->BBCS['js_h'],
                $this->BBCS['js_cw'],
                $this->BBCS['js_ch'],
                $this->BBCS['js_co'],
                $this->BBCS['js_pi'],
                $this->BBCS['refhost'],
                $this->BBCS['adblock'],
                $this->BBCS['asnum'],
                $this->BBCS['asname'],
                $this->BBCS['result'],
                $this->BBCS['http_accept'],
                $this->BBCS['request_method'],
                $this->BBCS['ym_uid'],
                $this->BBCS['ga_uid'],
                $this->BBCS['ip_short'],
                $this->BBCS['hosting'],
                $this->BBCS['botblocker_hits'],
                $this->BBCS['browser'],
                $this->BBCS['os'],
                $this->BBCS['device']
            )
        );
        $BBCS = $this->BBCS;  
    }

    public function parentWay(){
        //Checking the parent server
        global $BBCS;
        // TODO: create rules for servers GLOBUS.studio and CyberSecure
        if(($this->BBCS['ip'] === '185.237.225.43') || ($this->BBCS['ip'] === '2a0a:8c44::2aa')){ // TODO - move to config: array of BBCS Cloud IPs
            $this->BBCS['good_bot'] = 1;
        }
        $BBCS = $this->BBCS;
    }

    public function setCID(){
        global $BBCS;               
        $this->BBCS['cid'] = $this->BBCS['time'] . '.' . rand(11111, 99999); 
        $BBCS = $this->BBCS;        
    }

}