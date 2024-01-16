<?php

namespace src\Utils;


class CaptchaGenerator
{

    // session object
    private $session;

    // constructor with no parameters
    public function __construct() 
    {
        // access to session
        $this->session = &$_SESSION; 
    }

    // function that generate random value between 1000 and 9999
    public function generateRandomCode(): string
    {
        //return the random int needed
        return strval(rand(1000, 9999));
    }

    // function that return the captcha code
    public function getSessionCaptchaCode(): ?string
    {
        return $this->session['captcha_code'] ?? null;
    }

    // function that generate the image you need for captcha
    public function getCaptcha()
    {
        $code = $this->generateRandomCode();
        $this->session['captcha_code'] = $code;
    
        $image = $this->generateCaptchaImage($code);

    }

    // function that check the captcha
    public function verifyCaptcha($userCode): bool
    {
        $captchaCode = $this->getSessionCaptchaCode();
        
        // return true or not depending is the captcha enterred by user is the captcha code previously generated
        return $userCode === $captchaCode;
    }


    // generate img of captcha
    private function generateCaptchaImage($code):void
    {
        // creating empty image of 24 by 50 pixels
        $image = imagecreatetruecolor(50, 24);

        // random color of text
        $c1 = rand(0, 255);
        $c2 = rand(0, 255);
        $c3 = rand(0, 255);

        // random color of background based on text (ensure text is more readable)
        $b1 = min($c1 + 128, 255);
        $b2 = min($c2 + 128, 255);
        $b3 = min($c3 + 128, 255);

        // colorate background and foreground
        $background = imagecolorallocate($image, $c1, $c2, $c3); 
        $forground = imagecolorallocate($image, $b1, $b2, $b3);

        // fill image using background
        imagefill($image, 0, 0, $background);
        // put text on captcha img
        imagestring($image, 5, 5, 5, $code, $forground);

        // generate header of image before generating as png
        header("Cache-Control: no-cache, must-revalidate");
        header('Content-type: image/png');
        imagepng($image);

        // Leave memory
        imagedestroy($image);
    }

}