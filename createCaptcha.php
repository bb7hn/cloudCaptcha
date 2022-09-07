<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: X-Requested-With");
header('Content-type: image/png');


$captcha = generateRandomString(6);

$txt = $captcha;
// Bu yazı tipi dosyası olsun
$font_bold = $font ='./FiraCode-Bold.ttf';
$font_size=400;
// 300x150'lik bir görüntü oluştur
$bbox = imagettfbbox($font_size, 0, $font, $txt);

// Bunlar X ve Y koordinatları olsun
$x = $bbox[4] - $bbox[0];
$y = $bbox[3] - $bbox[5];
$paddingY   = 25;
$paddingX   = 25;
$im = imagecreatetruecolor($x+$paddingX*2, $y+$paddingY*2);
imagealphablending($im , false);
imagesavealpha($im , true);

$transparent = imagecolorallocatealpha($im , 255, 255, 255, 127);
$black = imagecolorallocate($im, 0, 0, 0);
$white = imagecolorallocate($im, 255, 255, 255);
// Artalan rengi beyaz olsun
imagefill($im,0,0,$transparent);
if(isset($_GET['r']) && isset($_GET['g']) && isset($_GET['b'])){
    try {
        $r = intval($_GET['r']);
        $g = intval($_GET['g']);
        $b = intval($_GET['b']);
        if($r>255){
            $r = 255;
        }
        if($g>255){
            $g = 255;
        }
        if($b>255){
            $b = 255;
        }
        $font_color = imagecolorallocate($im, $r, $g, $b);
    } catch (\Throwable $th) {
        $font_color = $black;
    }
}
else if(isset($_GET['theme'])){
    if($_GET['theme'] == 'dark'){
        $font_color = $white;
    }
    else{
        $font_color = $black;
    }
}
else{
    $font_color = $black;
}
$counter=0;
$old_word = "";
imagealphablending($im, true);
foreach(explode(" ",$txt) as $word){
    if($counter){
        $bbox = imagettfbbox($font_size, 0, $font, $old_word);
        // Bunlar X ve Y koordinatları olsun
        $x = $bbox[4] - $bbox[0];
        imagettftext($im, $font_size, 0, $x+$font_size/3,$font_size+7, $font_color, $font, $word);
    }
    else
        imagettftext($im, $font_size, 0, 0,$font_size+7, $font_color, $font_bold, $word);
    
    
    $old_word =$word;
    $counter+=1;
}

$test = imagepng($im);
imagedestroy($im);

function generateRandomString($length = 5) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@#';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}