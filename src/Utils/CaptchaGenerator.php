<?php

namespace src\Utils;


class CaptchaGenerator
{

    // session object
    private $session;

    /**
     * Constructor for the CaptchaGenerator class.
     *
     * This constructor initializes the CaptchaGenerator instance and provides access to the session
     * for storing and retrieving captcha-related data.
     */
    public function __construct() 
    {
        // access to session
        $this->session = &$_SESSION; 
    }

    /**
     * Generate a random value between 1000 and 9999 as code for the captcha.
     *
     * @return string
     */
    public function generateRandomCode(): string
    {
        //return the random int needed
        return strval(rand(1000, 9999));
    }

    /**
     * Retrieve the current captcha code stored in the session.
     *
     * @return string|null The captcha code if set, or null if not available in the session.
     */
    public function getSessionCaptchaCode(): ?string
    {
        return $this->session['captcha_code'] ?? null;
    }

    /**
     * Generate a new captcha code, store it in the session, and generate the corresponding captcha image.
     */
    public function getCaptcha()
    {
        $code = $this->generateRandomCode();
        $this->session['captcha_code'] = $code;
    
        $image = $this->generateCaptchaImage($code);

    }

    /**
     * Verify if the provided user-entered captcha code matches the stored captcha code in the session.
     *
     * @param string $userCode The captcha code entered by the user.
     *
     * @return bool True if the user-entered captcha code is correct, false otherwise.
     */
    public function verifyCaptcha($userCode): bool
    {
        $captchaCode = $this->getSessionCaptchaCode();
        
        // return true or not depending is the captcha enterred by user is the captcha code previously generated
        return $userCode === $captchaCode;
    }


    /**
     * Generate a captcha image based on the provided code and output it to the browser.
     *
     * @param string $code The captcha code to be displayed in the image.
     */
    private function generateCaptchaImage($code)
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