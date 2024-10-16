<?php

global $BBCS;

$color_base64['RED'] = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8DwHwAFBQIAX8jx0gAAAABJRU5ErkJggg=='; 
$color_base64['BLACK'] = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='; 
$color_base64['YELLOW'] = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5/hPwAIAgL/4d1j8wAAAABJRU5ErkJggg=='; 
$color_base64['GRAY'] = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNs+A8AAgUBgQvw1B0AAAAASUVORK5CYII='; 
$color_base64['BLUE'] = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPj/HwADBwIAMCbHYQAAAABJRU5ErkJggg==';
$color_base64['GREEN'] = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkaGD4DwACiQGBU29HsgAAAABJRU5ErkJggg=='; 
$color_base64['MAROON'] = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAAMSURBVBhXY2hgYAAAAYQAgVMkorQAAAAASUVORK5CYII='; // белый
$color_base64['PURPLE'] = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAAMSURBVBhXY/jP8B8ABAAB/4jQ/cwAAAAASUVORK5CYII='; 

shuffle($BBCS['colors']);
$color = $BBCS['colors'][0]; 

$colorhash = hash('sha256', $BBCS['salt'].$color.$BBCS['time'].$BBCS['license_pass'].$BBCS['ip']);

shuffle($BBCS['colors']);
$tags = array('div', 'span', 'b', 'strong', 'i', 'em');
shuffle($tags);
$buttons = array();
foreach ($BBCS['colors'] as $BBCS['color']) {

$buttons[] = '<'.$tags[0].' style=\"background-image: url(data:image/png;base64,'.$color_base64[$BBCS['color']].');\" class=\"'.'s'.md5('botblocker-btn-color'.$BBCS['time']).'\" onclick=\"'.$cloud_test_func_name.'(\'post\', data, \''.$BBCS['color'].'|'.$colorhash.'\')\"></'.$tags[0].'> ';

$buttons[] = '<'.$tags[0].' style=\"background-image: url(data:image/png;base64,'.$color_base64[$BBCS['color']].');display:none;\" class=\"'.'s'.md5('botblocker-btn-color'.$BBCS['time']).'\" onclick=\"'.$cloud_test_func_name.'(\'post\', data, \''.$BBCS['color'].'|'.md5($colorhash).'\')\"></'.$tags[0].'> ';
}
shuffle($buttons);
$buttons = '<p style=\"max-width: 200px;\">'.implode('',$buttons).'</p>';

$im = imagecreatetruecolor(rand(1,30), rand(1,30));

$color_code['RED'] = imagecolorallocate($im, rand(220,255), rand(0,30), rand(0,30)); 
$color_code['BLACK'] = imagecolorallocate($im, rand(0,15), rand(0,25), rand(0,25)); 
$color_code['YELLOW'] = imagecolorallocate($im, rand(245,255), rand(220,255), rand(0,25)); 
$color_code['GRAY'] = imagecolorallocate($im, rand(120,130), rand(125,135), rand(125,135)); 
$color_code['BLUE'] = imagecolorallocate($im, rand(0,30), rand(0,30), rand(155,255)); 
$color_code['GREEN'] = imagecolorallocate($im, rand(0,30), rand(125,250), rand(0,30)); 
$color_code['MAROON'] = imagecolorallocate($im, rand(120,130), rand(0,20), rand(0,20)); 
$color_code['PURPLE'] = imagecolorallocate($im, rand(120,130), rand(0,20), rand(120,130)); 

imagefill($im, 0, 0, $color_code[$color]);
ob_start(); 
imagepng($im);
imagedestroy($im);
$image_data = ob_get_contents(); 
ob_end_clean(); 

unset($color_base64);
unset($im);

echo '
document.getElementById("content").innerHTML = "<div class=\"s'.md5('botblocker-btn-color'.$BBCS['time']).'\" style=\"cursor: none; pointer-events: none; background-image: url(data:image/png;base64,'.base64_encode($image_data).');\" /></div><p>'.bbcs_customTranslate('If you are human, click on the similar color').'</p>'.$buttons.'";
';
