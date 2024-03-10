<?php
session_start();

// Генерируем случайный набор символов
$random_alpha = md5(rand());
$captcha_code = substr($random_alpha, 0, 6);

// Сохраняем код в сессии для проверки
$_SESSION["captcha_code"] = $captcha_code;

// Создаем изображение
$target_layer = imagecreatetruecolor(200,25);
$captcha_background = imagecolorallocate($target_layer, 39, 39, 50);
imagefill($target_layer, 0, 0, $captcha_background);
$captcha_text_color = imagecolorallocate($target_layer, 255, 255, 255);
imagestring($target_layer, 5, 5, 5, $captcha_code, $captcha_text_color);

header("Content-type: image/jpeg");
imagejpeg($target_layer);