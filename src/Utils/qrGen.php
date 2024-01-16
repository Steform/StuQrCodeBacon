<?php


    // composer autoload
    require_once './vendor/autoload.php';

    // load bacon lib
    use BaconQrCode\Renderer\ImageRenderer;
    use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
    use BaconQrCode\Renderer\RendererStyle\RendererStyle;
    use BaconQrCode\Writer;
    use BaconQrCode\Common\ErrorCorrectionLevel;
    use BaconQrCode\Encoder\Encoder;

    // require captcha and qr
    require_once 'CaptchaGenerator.php';
    require_once 'qrHistoryCleaner.php';

    // You need Imagick, if you don't have the project will not work


    // function able to create a qr code using bacon
    function generateQRCode($url, $name, $correction, $size, $margin, $logoPath)
    {
        // if the correction value is L M Q or H
        if (in_array($correction, ['L', 'M', 'Q', 'H'])) {
            if (filter_var($size, FILTER_VALIDATE_INT) !== false && $size >= 128 && $size <= 2048){
                if (filter_var($margin, FILTER_VALIDATE_INT) !== false && $margin >= 0 && $margin <= 128){

                    // adaptation of size using margin
                    $size = $size - (2 * $margin);

                    //renderer object from imagick to do the qr
                    $renderer = new ImageRenderer(
                        new RendererStyle($size, 0),
                        new ImagickImageBackEnd(),
                    );

                    // writer object able to write the wr code
                    $writer = new Writer($renderer);

                    // path of qr
                    $qrImagePath = './qrgen/' . $name . '.png';

                    // folder where qr is stocked
                    $dossier = 'qrgen/';

                    // if not folder
                    if (!is_dir($dossier)) {
                        // www-data
                        
                        // try to create the folder
                        if (!mkdir($dossier, 0777, true)) {
                            // if unable to create folder
                            $message = $lang_data['err1'];
                        } else {
                            // check if the folder has the correct permissions
                            if (!is_writable($dossier) || !is_readable($dossier)) {
                                // change folder permissions to 777
                                chmod($dossier, 0777);
                                
                                // check again if the folder has the correct permissions
                                if (!is_writable($dossier) || !is_readable($dossier)) {
                                    $message = $lang_data['err2']; // Adjust this message according to your needs
                                }
                            }
                        }
                    }
                    
                    // writing qr code with parameters without logo
                    $writer->writeFile(
                        $url,
                        $qrImagePath,
                        Encoder::DEFAULT_BYTE_MODE_ECODING,
                        ErrorCorrectionLevel::valueOf($correction)
                    );

                    if (!empty($logoPath)) {
                        if ($correction === 'H'){

                            // Loading logo with Imagick
                            $logo = new Imagick($logoPath);

                            // getting logo size
                            $logoWidth = $logo->getImageWidth();
                            $logoHeight = $logo->getImageHeight();
                            
                            // if square logo
                            if ($logoWidth === $logoHeight) {

                                // Loading qr code previously generated
                                $qrCode = new Imagick($qrImagePath);
                                
                                // Defining logo size by the qr size

                                $logoSize = $size / 3; // the logo is 1/4 of the qr code
                                $logo->scaleImage(round($logoSize), round($logoSize));
                                $logoMargin = ($size / 2) - ($logoSize / 2);
                                
                                // round up margin of logo
                                $logoMargin = ceil($logoMargin);

                                // Positionning using margin the logo over the qr
                                $qrCode->compositeImage($logo, Imagick::COMPOSITE_OVER, $logoMargin, $logoMargin);

                                // Save new qr code
                                $qrCode->writeImage($qrImagePath);

                            } else {
                                
                                // logo not square
                                return -6;

                            }
                        }else {
                            // you need High level of correction to use a logo
                            return -5;
                        }
                    }

                    // deal with margin of entire qr code
                    if ($margin <= ($size / 4)){
                        
                        // reloading qr code with logo or not
                        $qrImg = new Imagick($qrImagePath);
                        // adding margin to size
                        $size = $size + (2 * $margin);
    
                        // Create empty white image
                        $newImage = new Imagick();
                        $newImage->newImage($size, $size, new ImagickPixel('white'));
                        
                        // Copy qr without margin to the new one, right positionned on the center
                        $newImage->compositeImage($qrImg, Imagick::COMPOSITE_OVER, $margin, $margin);
    
                        // save qr
                        $newImage->writeImage($qrImagePath);
                        return 0;
    

                    } else {
                        // margin is to big
                        return -4;

                    }

                } else {
                    // bad qr margin
                    return -3;
                }
            } else {
                //bad qr size
                return -2;
            }
        } else {
            // not L M Q H for correction
            return -1;
        }
    }

    // if no csrf_token
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); //generate a 32-byte CSRF token
    }    



    $captchaGenerator = new src\Utils\CaptchaGenerator();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        // needed data to generate qr
        $urltext = $_POST['url'] ?? '';

        // name with date hour second and 5 random chars
        $name = $timestamp = date('Y-m-d-H-i-s'). '-' . substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'), 0, 5);;
        $correction = htmlspecialchars($_POST['correction']) ?? '';
        $size = htmlspecialchars($_POST['size']) ?? '';
        $margin = htmlspecialchars($_POST['margin']) ?? '';
        $logoPath = "";
        if (isset($_FILES['file'])) {
            $fichier = $_FILES['file'];
        
            // check if file waz downloaded and exist on server as tmp file
            if ($fichier['size'] > 0 && file_exists($fichier['tmp_name'])) {
        
                // get extension
                $extension = pathinfo($fichier['name'], PATHINFO_EXTENSION);
        
                // get type of file
                $mime = exif_imagetype($fichier['tmp_name']);
        
                // Vérifie le type de fichier : PNG ou JPG et vérifie le MIME
                if (($extension === 'png' || $extension === 'jpg' || $extension === 'jpeg') && ($mime === IMAGETYPE_PNG || $mime === IMAGETYPE_JPEG)) {
        
                    // Vérifie la taille du fichier (limite à 2 Mo)
                    if ($fichier['size'] <= 2 * 1024 * 1024) {
        
                        // store tmp logo in logoPath
                        $logoPath = $fichier['tmp_name']; 
        
                    } else {

                        // Logo to big;
                        $message = $lang_data['oversizelogo'];
                        $logoPath = "";
                    }
        
                } else {

                    // not a jpg png
                    $message = $lang_data['onlyjpgpng'];

                }
            }
        }
        
        

        // get captcha from user
        $userProvidedCode = htmlspecialchars($_POST['captcha']) ?? '';

        // if csrf not empty and hash is ok
        if (!empty($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            // if captcha ok
            if ($captchaGenerator->verifyCaptcha($userProvidedCode)) {

                // if url, name, correction and size not empty
                if (!empty($urltext) && !empty($name) && !empty($correction) && !empty($size)&& !empty($margin)) {

                    // by baconQrCode and Imagick, generate qr code in png format
                    $generated = generateQRCode($urltext, $name, $correction, $size, $margin, $logoPath);
                    if ($generated === 0) {

                        // for history it's a target for localStorage Addition
                        echo '<div id="qrCodeData" style="display: none;" qrcode-name="' . htmlentities(json_encode(['qrCodeName' => $name])) . '"></div>';
                        
                        
                        $message = $lang_data['qrgenok'];

                    } else {
                        // error message
                        switch($generated){
                            case -1:
                                // bad correction level
                                $message = $lang_data['err2'];
                                break;
                            case -2:
                                // bad qr size
                                $message = $lang_data['err3'];
                                break;
                            case -3:
                                // bad qr margin
                                $message = $lang_data['err4'];
                                break;
                            case -4:
                                // margin to big
                                $message = $lang_data['err5'];
                                break;
                            case -5:
                                // correction level must be high if you want logo
                                $message = $lang_data['err6'];
                                break;
                            case -6:
                                // logo not sqare
                                $message = $lang_data['err7'];
                                break;
                        }

                    }
                } else {
                    // you must fill all needed field
                    $message = $lang_data['fillall'];
                }
            // if not good captcha
            } else {
                
                // new captcha generated
                $captchaCode = $captchaGenerator->getSessionCaptchaCode();

                // message for user about bad captcha
                $message=$lang_data['badcaptcha'];
            }
        // if not good CSRF
        } else {

            // message about CSRF 
            $message = $lang_data['badcsrf'];
        }
    // if not form post    
    } else {

        // generate new captcha
        $captchaCode = $captchaGenerator->getSessionCaptchaCode();
    }

?>