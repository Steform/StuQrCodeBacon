<?php

// starting session if needed
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}

//require the Class of CaptchaGenerator
require_once __DIR__ . '/CaptchaGenerator.php';
    
// if we have already a captcha instance
if (isset($captchaGenerator) == true){
    // if the instance is not an instance of CaptchaGenerator Class
    if (!($captchaGenerator instanceof \src\Utils\CaptchaGenerator)) {
        // create a captcha instance
        $captchaGenerator = new \src\Utils\CaptchaGenerator();
    }
}else{
    // create a captcha instance
    $captchaGenerator = new \src\Utils\CaptchaGenerator();

}

// generate captcha img
$captchaGenerator->getCaptcha();
