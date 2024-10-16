<?php
// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC') || !defined('BOTBLOCKER')) {
  exit;
}


global $wpdb;
global $BBCS;

echo '<script>var adb_var = 1; </script>
<script id="adblock-blocker" src="' . $BBCS['botblockerUrl'] . 'public/js/rails.js?bannerid=' . $BBCS['time'] . '"></script>
'; 

$cloud_test_func_name = 'f' . md5($BBCS['ip'] . $BBCS['time']);

if ($BBCS['recaptcha_check'] == 1) {
  echo '<script src="https://www.google.com/recaptcha/api.js?render=' . $BBCS['recaptcha_key3'] . '"></script>';
}

$botblocker_output = array();
$botblocker_parse_url = parse_url($BBCS['uri']); // текущий урл
if ($BBCS['utm_referrer'] == 1 and $BBCS['referer'] != '') {
  if (isset($botblocker_parse_url['query'])) {
    parse_str($botblocker_parse_url['query'], $botblocker_output);
  }

  $botblocker_output['utm_referrer'] = isset($_GET['utm_referrer']) ? trim(strip_tags($_GET['utm_referrer'])) : $BBCS['referer'];
  if (!isset($botblocker_parse_url['path']) or $botblocker_parse_url['path'] == '') {
    $botblocker_parse_url['path'] = '/';
  }
  $bbcsNewUrl = $botblocker_parse_url['path'] . '?' . http_build_query($botblocker_output);
} else {
  $bbcsNewUrl = $BBCS['uri'];
}

?><script>

function bbcs_detectNavigatorMismatch() {
    const nav = window.navigator;
    const userAgent = nav.userAgent;
    const appVersion = nav.appVersion;
    const platform = nav.platform;
    const vendor = nav.vendor;

    const browserNames = ['Chrome', 'Firefox', 'Safari', 'Edge', 'Opera'];
    for (const name of browserNames) {
        if ((userAgent.indexOf(name) > -1) !== (appVersion.indexOf(name) > -1)) {
            return true;
        }
    }

    if (nav.webdriver !== undefined) {
        return true; 
    }

    const osPlatforms = ['Win', 'Mac', 'Linux', 'iPhone', 'iPad', 'Android'];
    let osMismatch = true;
    for (const os of osPlatforms) {
        if (userAgent.indexOf(os) > -1 && platform.indexOf(os) > -1) {
            osMismatch = false;
            break;
        }
    }

    if (osMismatch) {
        return true; 
    }

    return false; 
}

function bbcs_detectUnsupportedFeatures() {
    const requiredFeatures = [
        'Promise',
        'fetch',
        'ServiceWorker',
        'IntersectionObserver',
        'WebAssembly',
        'Intl',
        'BigInt',
        'Map',
        'Set',
        'Symbol'
    ];

    for (const feature of requiredFeatures) {
        if (!(feature in window)) {
            return true; 
        }
    }

    const requiredNavigatorFeatures = [
        'deviceMemory',
        'hardwareConcurrency',
        'mediaDevices',
        'permissions'
    ];

    for (const feature of requiredNavigatorFeatures) {
        if (!(feature in navigator)) {
            return true; 
        }
    }

    return false; 
}

function bbcs_detectFakePlugins() {
    const plugins = navigator.plugins;
    const mimeTypes = navigator.mimeTypes;

    if (plugins.length === 0 && mimeTypes.length === 0) {
        return true; 
    }

    const expectedPlugins = ['Chrome PDF Plugin', 'Chrome PDF Viewer', 'Native Client'];
    let foundExpected = false;
    for (const plugin of plugins) {
        if (expectedPlugins.includes(plugin.name)) {
            foundExpected = true;
            break;
        }
    }
    if (!foundExpected) {
        return true; 
    }

    return false;
}

function bbcs_detectFontRenderMismatch() {
    const canvas = document.createElement('canvas');
    canvas.width = 200;
    canvas.height = 50;
    const context = canvas.getContext('2d');

    context.textBaseline = 'alphabetic';
    context.fillStyle = '#f60';
    context.fillRect(125, 1, 62, 20);
    context.fillStyle = '#069';
    context.font = '16pt Arial';
    context.fillText('Cwm fjordbank glyphs vext quiz', 2, 15);
    context.fillStyle = 'rgba(102, 204, 0, 0.7)';
    context.font = '18pt Arial';
    context.fillText('Cwm fjordbank glyphs vext quiz', 4, 45);

    const data = canvas.toDataURL();

    function hash(str) {
        let hash = 0;
        if (str.length === 0) return hash;
        for (let i = 0; i < str.length; i++) {
            let char = str.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash;
        }
        return hash;
    }

    const canvasHash = hash(data);
// TODO HASHES
    const expectedHashes = [/*GOOD*/];

    if (expectedHashes.length > 0 && !expectedHashes.includes(canvasHash)) {
        return true; 
    }

    return false;
}

function bbcs_detectChromiumProperties() {
    const isChromium = !!window.chrome;

    if (!isChromium) {
        return true; 
    }

    const unusualProps = [
        '_phantom',
        '__nightmare',
        'callPhantom',
        'spawn',
        'emit'
    ];

    for (const prop of unusualProps) {
        if (window[prop] !== undefined) {
            return true; 
        }
    }

    return false; 
}

function bbcs_detectJitter() {
    const timings = [];
    for (let i = 0; i < 100; i++) {
        const start = performance.now();
        for (let j = 0; j < 1000; j++) {}
        const end = performance.now();
        timings.push(end - start);
    }

    const sum = timings.reduce((a, b) => a + b, 0);
    const avg = sum / timings.length;

    if (avg > 0.5) { 
        return true; 
    }

    return false; 
}

function bbcs_detectWebGL() {
    const canvas = document.createElement('canvas');
    let gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');

    if (!gl) {
        return true; 
    }

    const debugInfo = gl.getExtension('WEBGL_debug_renderer_info');
    if (debugInfo) {
        const renderer = gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL);
        const vendor = gl.getParameter(debugInfo.UNMASKED_VENDOR_WEBGL);

        const ua = navigator.userAgent;

        const osVendorMapping = {
            'Windows': 'Google Inc.',
            'Mac OS': 'Apple Inc.',
            'Linux': 'Google Inc.',
            'Android': 'Google Inc.',
            'iOS': 'Apple Inc.'
        };

        let osDetected = null;
        for (let os in osVendorMapping) {
            if (ua.includes(os)) {
                osDetected = os;
                break;
            }
        }

        if (osDetected && !vendor.includes(osVendorMapping[osDetected])) {
            return true;
        }
    } else {
        return true; 
    }

    return false; 
}

function bbcs_detectTouchEvent() {
    const hasTouch = 'ontouchstart' in window || navigator.maxTouchPoints > 0 || navigator.msMaxTouchPoints > 0;
    const ua = navigator.userAgent;

    if (ua.includes('Mobile') && !hasTouch) {
        return true; 
    }

    return false; 
}

function bbcs_detectBatteryAPI() {
    if ('getBattery' in navigator) {
        return false; 
    } else {
        return true; 
    }
}

function bbcs_detectMediaDevices() {
    if ('mediaDevices' in navigator && 'enumerateDevices' in navigator.mediaDevices) {
        return false; 
    } else {
        return true; 
    }
}

function bbcs_detectPermissions() {
    if ('permissions' in navigator && 'query' in navigator.permissions) {
        return false; 
    } else {
        return true; 
    }
}

function bbcs_detectLanguageMismatch() {
    const nav = navigator;
    const lang = nav.language;
    const langs = nav.languages;

    if (langs && langs.length > 0 && lang !== langs[0]) {
        return true; 
    }

    return false; 
}
/*
function bbcs_detectTimeZoneMismatch(serverTimeZone) {
    const browserTimeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;
    if (browserTimeZone !== serverTimeZone) {
        return true; 
    }
    return false; 
}
*/
function bbcs_detectAll(/*serverTimeZone*/) {
    return {
        navigatorMismatch: bbcs_detectNavigatorMismatch(),
        unsupportedFeatures: bbcs_detectUnsupportedFeatures(),
        fakePlugins: bbcs_detectFakePlugins(),
        fontRenderMismatch: bbcs_detectFontRenderMismatch(),
        chromiumProperties: bbcs_detectChromiumProperties(),
        jitter: bbcs_detectJitter(),
        webGLMismatch: bbcs_detectWebGL(),
        touchEventMismatch: bbcs_detectTouchEvent(),
        batteryAPIMismatch: bbcs_detectBatteryAPI(),
        mediaDevicesMismatch: bbcs_detectMediaDevices(),
        permissionsMismatch: bbcs_detectPermissions(),
        languageMismatch: bbcs_detectLanguageMismatch(),
       // timeZoneMismatch: bbcs_detectTimeZoneMismatch(serverTimeZone)
    };
}

  function bbcs_getDetectionParam() {
    const detectionResult = bbcs_detectAll();
    const jsonString = JSON.stringify(detectionResult);
    const base64Encoded = btoa(jsonString); 
    return encodeURIComponent(base64Encoded); 
  }

  function areCookiesEnabled() {
    var cookieEnabled = navigator.cookieEnabled;
    if (cookieEnabled === undefined) {
      document.cookie = "testcookie";
      cookieEnabled = document.cookie.indexOf("testcookie") != -1;
    }
    return cookieEnabled;
  }
  if (!areCookiesEnabled()) {
    var cookieoff = 1;
  } else {
    var cookieoff = 0;
  }

  if (window.location.hostname !== window.atob("<?php echo base64_encode($BBCS['host']); ?>") && window.location.hostname !== window.atob("<?php echo base64_encode(strstr($BBCS['host'], ':', true)); ?>")) {
    window.location = window.atob("<?php echo base64_encode($BBCS['scheme'] . '://' . $BBCS['host'] . $BBCS['uri']); ?>");
    throw "stop";
  }

  function b64_to_utf8(str) {
    str = str.replace(/\s/g, '');
    return decodeURIComponent(escape(window.atob(str)));
  }

  document.getElementById("content").innerHTML = "<?php echo bbcs_customTranslate('Loading...'); ?>"; //

  function asyncFunction1() {
    return new Promise(function(resolve) {
      <?php if ($BBCS['recaptcha_check'] == 1) { ?>
        grecaptcha.ready(function() {
          grecaptcha.execute('<?php echo $BBCS['recaptcha_key3']; ?>', {
            action: '<?php echo $BBCS['country']; ?>'
          }).then(function(token) {
            rct = token; // token received
            resolve('Result of Async Function 1');
          });
        });
      <?php } else { ?>
        rct = ''; //
        resolve('Result of Async Function 1');
      <?php } ?>
    });
  }

  function asyncFunction2() {
    return new Promise(function(resolve) {
      <?php if ($BBCS['ip_version'] == 6) { ?>
        var gsApiReq = new XMLHttpRequest();
        // TODO
        gsApiReq.open('GET', 'https://api.globus.studio/v2/ip?v=6&format=json', true);
        gsApiReq.setRequestHeader("Content-Type", "application/json");
        gsApiReq.timeout = 5000; 
        gsApiReq.onload = function() {
          if (gsApiReq.readyState === 4 && gsApiReq.status === 200) {
            var json = JSON.parse(gsApiReq.responseText);
            ipv4 = json.ip;
            ipdbc = '<?php echo $BBCS['empty'];?>';
            resolve('Result of Async Function 2');
          } else {
            console.error('Request failed with status:', gsApiReq.status);
            resolve('Result Error of Async Function 2');
          }
        };
        gsApiReq.ontimeout = function() {
          console.error('Request timed out');
          resolve('Result Error of Async Function 2');
        };
        gsApiReq.onerror = function() {
          console.error('Error occurred');
          resolve('Result Error of Async Function 2');
        };
        gsApiReq.send();
      <?php } else { ?>
        ipv4 = '';
        ipdbc = '';
        resolve('Result of Async Function 2');
      <?php } ?>
    });
  }

  function anotherFunction(result1, result2) {
    bbcs_detectionParam = bbcs_getDetectionParam();
    data = 'test=<?php echo hash('sha256', $BBCS['useragent'] . $BBCS['ip'] . $BBCS['time'] . $BBCS['country'] . $BBCS['ptr'] . $BBCS['salt']); ?>&h1=<?php echo hash('sha256', $BBCS['license_email'] . $BBCS['license_pass'] . $BBCS['host'] . $BBCS['useragent'] . $BBCS['ip'] . $BBCS['time']); ?>&date=<?php echo $BBCS['time']; ?>&hdc=<?php echo $BBCS['hosting']; ?>&a=' + adb_var + '&country=<?php echo $BBCS['country']; ?>&ip=<?php echo $BBCS['ip']; ?>&version=<?php echo $BBCS['version']; ?>&cid=<?php echo $BBCS['cid']; ?>&ptr=<?php echo $BBCS['ptr']; ?>&w=' + screen.width + '&h=' + screen.height + '&cw=' + document.documentElement.clientWidth + '&ch=' + document.documentElement.clientHeight + '&co=' + screen.colorDepth + '&pi=' + screen.pixelDepth + '&ref=' + encodeURIComponent(document.referrer) + '&accept=<?php echo urlencode($BBCS['http_accept']); ?>&tz=' + Intl.DateTimeFormat().resolvedOptions().timeZone + '&ipdbc=' + ipdbc + '&ipv4=' + ipv4 + '&rct=' + rct + '&cookieoff=' + cookieoff +'&bbdet=' + bbcs_detectionParam;
    <?php echo $cloud_test_func_name; ?>('botblocker', data, '');
    console.log('Another Function executed with results:', result1, result2);
  }

  async function runAsyncFunctions() {
    try {
      const result1 = await asyncFunction1();
      const result2 = await asyncFunction2();
      anotherFunction(result1, result2);
    } catch (error) {
      console.error(error);
    }
  }

  runAsyncFunctions();

  function Button() {
    <?php if ($BBCS['bbcs_captcha_disable'] != 1) {
      require_once($BBCS['dirs']['public'] . 'buttons/' . $BBCS['bbcs_captcha_mode'] . '.php');
    } ?>
  }


  function <?php echo $cloud_test_func_name; ?>(s, d, x) {
    document.getElementById("content").innerHTML = "<?php echo bbcs_customTranslate('Loading...'); ?>";
    
    var data = new FormData();
    data.append('action', 'botblocker_check');
    data.append('nonce', '<?php echo wp_create_nonce('botblocker_nonce'); ?>');
    data.append('<?php echo $BBCS['request_mode']; ?>', s);
    data.append('xxx', x);
    data.append('rowid', '<?php echo $BBCS['rowid']; ?>');
    data.append('gray', '<?php echo $BBCS['suspect']; ?>');

    var additionalParams = new URLSearchParams(d);
    for (var pair of additionalParams.entries()) {
        data.append(pair[0], pair[1]);
    }

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '<?php echo admin_url('admin-ajax.php'); ?>', true);
    xhr.timeout = 5000;

    xhr.onload = function() {
        if (xhr.status == 200) {
            console.log('good: ' + xhr.status);
            var obj = JSON.parse(xhr.responseText);

            if (typeof(obj.cookie) == "string") {
                var d = new Date();
                d.setTime(d.getTime() + (7 * 24 * 60 * 60 * 1000));
                var expires = "expires=" + d.toUTCString();
                document.cookie = "<?php echo $BBCS['uid']; ?>=" + obj.cookie + "-<?php echo $BBCS['time']; ?>; SameSite=<?php echo $BBCS['samesite']; ?>;<?php echo (($BBCS['samesite'] == 'None') ? ' Secure' : ''); ?>; " + expires + "; path=/;";
                document.getElementById("content").innerHTML = "<?php echo bbcs_customTranslate('Loading...'); ?>";
                window.location.href = "<?php echo $bbcsNewUrl; ?>";
            } else {
                Button();
                console.log('Bad bot detected');
            }
            if (typeof(obj.error) == "string") {
                <?php if (!defined('botblocker_ADMIN')) { ?>
                    if (obj.error == "Account Not Found" || obj.error == "This domain is not licensed" || obj.error == "Subscription has expired" || obj.error == "This domain is blacklisted" || obj.error == "<?php echo $BBCS['js_error_msg']; ?>") {
                        const ErrorMsg = document.createElement('div');
                        ErrorMsg.innerHTML = '<h1 style="text-align:center; color:red;">' + obj.error + '</h1>';
                        document.body.insertAdjacentElement('afterbegin', ErrorMsg);
                        document.getElementById("content").style.visibility = "hidden";
                        document.getElementById("content").innerHTML = '';
                    } else if (obj.error == "Cookies disabled") {
                        document.getElementById("content").innerHTML = "<h2 style=\"text-align:center; color:red;\"><?php echo bbcs_customTranslate('Cookie is Disabled in your browser. Please Enable the Cookie to continue.'); ?></h2>";
                    }
                <?php } ?>
                if (obj.error == "Wrong Click") {
                    document.getElementById("content").innerHTML = "<?php echo bbcs_customTranslate('Loading...'); ?>";
                    window.location.href = "<?php echo $bbcsNewUrl; ?>";
                }
            }
        } else {
            console.log('Error: ' + xhr.status);
            Button();
        }
    };

    xhr.ontimeout = function() {
        console.log('timeout');
        Button();
    };

    xhr.onerror = function() {
        console.log('error');
        Button();
    };

    xhr.send(data);
}

</script>
<noscript>
  <h2 style="text-align:center; color:red;"><?php echo bbcs_customTranslate('JavaScript is Disabled in your browser. Please Enable the JavaScript to continue.'); ?></h2>
</noscript>