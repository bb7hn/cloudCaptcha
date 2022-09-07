<?php
    try {

        /* $db = new PDO("mysql:host=localhost;dbname=cloud_captcha", "root", "12345678");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $cleanDB = $db->query("DELETE FROM captcha WHERE createdAt <= NOW() - INTERVAL 15 MINUTE");
        $count = $cleanDB->rowCount(); */
        $cleanedFiles = deleteOldFiles();
        $clenedValidations = deleteOldFiles(1/15,'./validate/');
        $stream = fopen('logs.html','a');
        fwrite($stream,"FILES cleaned: <b>$cleanedFiles</b> captcha file/s deleted!".PHP_EOL.'<br/>'.PHP_EOL);
        fwrite($stream,"FILES cleaned: <b>$clenedValidations</b> json file/s deleted!".PHP_EOL.'<hr/>'.PHP_EOL);
        fclose($stream);
    } catch (PDOException $e) {
        echo json_encode([
            "code"      => 500,
            "message"   => "Database is not available. Please contact with server admin!"
        ]);
        exit;
    }

    function deleteOldFiles($hours=1/15/*  15 mins as default*/,$path = './captcha/'){
        $counter = 0;
        if ($handle = opendir($path)) {
            while (false !== ($file = readdir($handle))) { 
                $filelastmodified = filemtime($path . $file);
                if($file == ".."){
                    continue;
                }
                if($file == "."){
                    continue;
                }
                if($file == ".htaccess"){
                    continue;
                }
                //24 hours in a day * 3600 seconds per hour
                if((time() - $filelastmodified) > $hours*3600)
                {
                    unlink($path . $file);
                    $counter++;
                }
            }
            closedir($handle); 
        }
        return $counter;
    }
?>