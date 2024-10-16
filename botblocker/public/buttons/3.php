<?php

global $BBCS;

// ReCAPTCHA v2 + кнопка "Я не робот"

// хэш правильной кнопки:
$hash0 = '1|'.hash('sha256', $BBCS['salt'].$BBCS['time'].$BBCS['license_pass']);
$style0 = 'o'.md5($hash0);
$onestyle[] = '.'.$style0.' {} ';
$onebtns[] = '<div style="cursor: pointer;" class="'.$style0.' '.'s'.md5('botblocker-btn-success'.$BBCS['time']).'" onclick="'.$cloud_test_func_name.'(\'post\', data, \''.$hash0.'\')">'.bbcs_customTranslate('Go to website').'</div>'; // валидный

for ($i = 0; $i < rand(2,6); $i++) {
$hash0 = '1|'.hash('sha256', $BBCS['salt'].$BBCS['time'].$BBCS['license_pass'].rand(1,99999));
$style0 = 'o'.md5($hash0);
$onestyle[] = '.'.$style0.' {display: none;} ';
$onebtns[] = '<div style="cursor: pointer;" class="'.$style0.' '.'s'.md5('botblocker-btn-success'.$BBCS['time']).'" onclick="'.$cloud_test_func_name.'(\'post\', data, \''.$hash0.'\')">'.bbcs_customTranslate('Go to website').'</div>'; // рандомная
}
shuffle($onebtns);
shuffle($onestyle);

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
//'.$cloud_test_func_name.'(\'post\', data, \''.$hash0.'\');
document.getElementById("content").innerHTML = "<div style=\"max-width: 302px; text-align: center;margin: 0 auto;\">"+b64_to_utf8("'.base64_encode(''.implode('', $onebtns).'</div><style>'.implode(' ', $onestyle).'</style>').'");
}

';
