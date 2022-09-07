<?php
    use Ramsey\Uuid\Uuid;
    $method = "create";
    if(isset($_GET['method'])){
        $method = $_GET['method'];
    }
    switch ($method) {
        case 'validate':
            require_once('config.php');
            validate();
            break;
        
        default:
            require_once('config.php');
            create();
            break;
    }
    function create(){
        $expireTime = "15 minutes";
        $captcha = generateRandomString(6);
        //save captcha to validate folder
        
        $txt = $captcha;
        // font file
        $font ='./FiraCode-Bold.ttf';
        $font_size=400;
        $bbox = imagettfbbox($font_size, 0, $font, $txt);

        // X - Y Coordinates (image size)
        $x = $bbox[4] - $bbox[0];
        $y = $bbox[3] - $bbox[5];
        // Padding
        $paddingY   = 25;
        $paddingX   = 25;
        //create img
        $im = imagecreatetruecolor($x+$paddingX*2, $y+$paddingY*2);
        imagealphablending($im , false);
        imagesavealpha($im , true);
        // define default colors
        $transparent = imagecolorallocatealpha($im , 255, 255, 255, 127);
        $black = imagecolorallocate($im, 0, 0, 0);
        $white = imagecolorallocate($im, 255, 255, 255);
        // set bg transparent
        imagefill($im,0,0,$transparent);
        //set font color
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
        //create captcha
        imagealphablending($im, true);
        imagettftext($im, $font_size, 0, 0,$font_size+7, $font_color, $font, $txt);
        //create UUID for filename and db id
        require('./vendor/autoload.php');
        $UUID =Uuid::uuid4();
        imagepng($im,"./captcha/$UUID.png");
        imagedestroy($im);
        
        $stream = fopen("./validate/$UUID.json",'w');
        fwrite($stream,json_encode(['captcha'=>$captcha]));
        fclose($stream);

        $serverAddress = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
        $response = [
            "code"      => "200",
            "message"   => "Captcha created successfully and will expire in $expireTime.",
            "id"        => $UUID,
            "src"       => "$serverAddress/captcha/$UUID.png"
        ];
        echo json_encode($response);
        
    }
    function validate(){
        if(!isset($_GET['captcha'])){
            $response = [
                "code"      => "406",
                "message"   => "Missing info: Captcha.",
            ];
            echo json_encode($response);
            exit;
        }
        if(!isset($_GET['id'])){
            $response = [
                "code"      => "406",
                "message"   => "Missing info: Id.",
            ];
            echo json_encode($response);
            exit;
        }
        $captchaPattern = "/^[ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@#]+$/";
        if(!preg_match($captchaPattern,$_GET['captcha'])){
            $response = [
                "code"      => "406",
                "message"   => "Invalid info: Captcha.",
            ];
            echo json_encode($response);
            exit;
        }
        $uuidPattern   = "/^[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}$/";
        if(!preg_match($uuidPattern,$_GET['id'])){
            $response = [
                "code"      => "406",
                "message"   => "Invalid info: Id.",
            ];
            echo json_encode($response);
            exit;
        }
        $validCaptcha = getCaptcha($_GET['id']);
        if(!$validCaptcha){
            $response = [
                "code"      => "406",
                "message"   => "Invalid info: Id doesn't exists.",
            ];
            echo json_encode($response);
            exit;
        }
        if($validCaptcha != $_GET['captcha']){
            $response = [
                "code"      => "401",
                "message"   => "Captcha is wrong.",
            ];
            echo json_encode($response);
            exit;
        }
        $response = [
            "code"      => "200",
            "message"   => "Captcha is valid.",
        ];
        echo json_encode($response);
        exit;
    }
    function generateRandomString($length = 5) {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@#';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    function getCaptcha($id){
        $fileName = "./validate/$id.json";
        if(!is_file($fileName)){
            return false;
        }
        $captcha = json_decode(file_get_contents($fileName));
        if(empty($captcha)){
            return false;
        }
        unlink($fileName);
        return $captcha->captcha;
    }
?>