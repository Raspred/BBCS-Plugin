<?php

global $BBCS;

// ReCAPTCHA v2 без кнопок

// хэш правильной кнопки:
$hash0 = '1|'.hash('sha256', $BBCS['salt'].$BBCS['time'].$BBCS['license_pass']);

echo '
var script = document.createElement("script");
script.src = "https://www.google.com/recaptcha/api.js";
document.body.appendChild(script);
script.onload = function() {
document.getElementById("content").innerHTML = "<div style=\"max-width: 302px; text-align: center;margin: 0 auto;\"><p>'.bbcs_customTranslate('Confirm that you are human:').'</p><p class=\"g-recaptcha\" style=\"display: inline-block;\" data-sitekey=\"'.$BBCS['recaptcha_key2'].'\" data-callback=\"onRecaptchaSuccess\">'.bbcs_customTranslate('Loading...').'</p></div>";
}

// разгадали рекапчу:
window.onRecaptchaSuccess = function(token) {
data += "&g-recaptcha-response=" + token;
document.getElementById("content").innerHTML = "'.bbcs_customTranslate('Loading...').'";
'.$cloud_test_func_name.'(\'post\', data, \''.$hash0.'\');
}

';
