<?php
// Одна большая кнопка "Я не робот"

global $BBCS;

// хэш правильной кнопки:
$hash0 = '1|'.hash('sha256', $BBCS['salt'].$BBCS['time'].$BBCS['license_pass']);
$style0 = 'o'.md5($hash0);
$onestyle[] = '.'.$style0.' {} ';
$onebtns[] = '<div style="cursor: pointer;" class="'.$style0.' '.'s'.md5('botblocker-btn-success'.$BBCS['time']).'" onclick="'.$cloud_test_func_name.'(\'post\', data, \''.$hash0.'\')">'.bbcs_customTranslate('I\'m not a robot').'</div>'; // валидный

for ($i = 0; $i < rand(2,6); $i++) {
$hash0 = '1|'.hash('sha256', $BBCS['salt'].$BBCS['time'].$BBCS['license_pass'].rand(1,99999));
$style0 = 'o'.md5($hash0);
$onestyle[] = '.'.$style0.' {display: none;} ';
$onebtns[] = '<div style="cursor: pointer;" class="'.$style0.' '.'s'.md5('botblocker-btn-success'.$BBCS['time']).'" onclick="'.$cloud_test_func_name.'(\'post\', data, \''.$hash0.'\')">'.bbcs_customTranslate('I\'m not a robot').'</div>'; // рандомная
}
shuffle($onebtns);
shuffle($onestyle);

echo '
document.getElementById("content").innerHTML = b64_to_utf8("'.base64_encode('<p>'.bbcs_customTranslate('Confirm that you are human:').'</p>'.implode('', $onebtns).'<style>'.implode(' ', $onestyle).'</style>').'");
';
