<?php

global $BBCS;

// Несколько кнопок с выбором похожей КАРТИНКИ
$color_base64['RED'] = '1';
$color_base64['BLACK'] = '2';
$color_base64['YELLOW'] = '3';
$color_base64['GRAY'] = '4';
$color_base64['BLUE'] = '5';
$color_base64['GREEN'] = '6';
$color_base64['MAROON'] = '7';
$color_base64['PURPLE'] = '8';

shuffle($BBCS['colors']);

$color = $BBCS['colors'][0]; // оригинал названия цвета
// хэш правильного цвета:
$colorhash = hash('sha256', $BBCS['salt'] . $color . $BBCS['time'] . $BBCS['license_pass'] . $BBCS['ip']);

$buttons = array();
$jsf = array();
foreach ($BBCS['colors'] as $BBCS['color']) {
  $md = md5($BBCS['time'] . $BBCS['salt'] . $color_base64[$BBCS['color']]);
  $buttons[] = '<span id=\"' . $md . '\" style=\"cursor: pointer;\" onclick=\"' . $cloud_test_func_name . '(\'post\', data, \'' . $BBCS['color'] . '|' . $colorhash . '\')\"></span> ';
  $jsf[] = 'fetchAndSetImage("' . $color_base64[$BBCS['color']] . '", "' . $md . '");';
}
shuffle($buttons);
shuffle($jsf);
$buttons = '<p style=\"max-width: 500px;\">' . implode('', $buttons) . '</p>';

$red = rand(10, 50);
$green = rand(10, 50);
$blue = rand(10, 50);
$im = imagecreatefromjpeg($BBCS['dirs']['public'] . 'img/'.$this->BBCS['bbcs_captcha_img_pack'].'/' . $color_base64[$color] . '.jpg');
imagefilter($im, IMG_FILTER_COLORIZE, $red, $green, $blue);
imageflip($im, IMG_FLIP_HORIZONTAL); // отражение по горизонтале
imagegammacorrect($im, 1.0, 1.537); // гамма коррекция
// накладывание рандом точек:
for ($i = 0; $i < 1000; $i++) {
  $red = imagecolorallocate($im, rand(1, 255), rand(1, 255), rand(1, 255));
  imagesetpixel($im, rand(1, 100), rand(1, 100), $red);
}
$im = imagerotate($im, rand(-20, 20), imageColorAllocateAlpha($im, 0, 0, 0, 127)); // поворот по кругу
ob_start();
imagepng($im);
$image_data1 = ob_get_contents();
imagedestroy($im);
ob_end_clean();
unset($im);
echo '
document.getElementById("content").innerHTML = "<img src=\"data:image/png;base64,' . base64_encode($image_data1) . '\" /><p>' . bbcs_customTranslate('If you are human, click on the similar image') . ' </p>' . $buttons . '";
';
?>

function fetchAndSetImage(param, imageId) {
var url = '<?php echo admin_url('admin-ajax.php'); ?>';
var formData = new FormData();
formData.append('action', 'botblocker_check');
formData.append('nonce', '<?php echo wp_create_nonce('botblocker_nonce'); ?>');
formData.append('img', param);
formData.append('time', "<?php echo $BBCS['time']; ?>");
formData.append('<?php echo $BBCS['request_mode']; ?>', 'img');

var requestOptions = {
method: 'POST',
body: formData
};

fetch(url, requestOptions)
.then(response => response.blob())
.then(blob => {
var imageUrl = URL.createObjectURL(blob);
var img = document.createElement('img'); // Создание элемента <img>
img.src = imageUrl; // Установка URL-адреса изображения
var span = document.getElementById(imageId);
// span.innerHTML = ''; // Очистка содержимого <span> (если нужно)
  span.appendChild(img); // Вставка изображения в элемент <span>
    })
    .catch(error => console.error('Произошла ошибка при получении изображения:', error));
    }

    <?php echo implode("\n", $jsf); ?>