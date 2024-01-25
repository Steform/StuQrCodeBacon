<?php

    namespace App\Utils;

    // load bacon lib
    use BaconQrCode\Common\ErrorCorrectionLevel;
    use BaconQrCode\Encoder\Encoder;
    use BaconQrCode\Renderer\ImageRenderer;
    use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
    use BaconQrCode\Renderer\RendererStyle\RendererStyle;
    use BaconQrCode\Writer;
    // use Imagick;
    // use ImagickPixel;


    class QrCodeGenerator
    {
        private $outputPath;
        private static $instance;
        
        /**
         * Constructor for the QRCodeGenerator class.
         *
         * @param array  $lang_data   Language-specific data for error messages and logging.
         * @param string $outputPath  The path where the generated QR code images will be saved.
         */

        private function __construct($outputPath)
        {
            $this->outputPath = $outputPath;
            $this->clean();
        }

        /**
         * Get an instance of the QRCodeGenerator using the Singleton pattern.
         *
         * @param array  $lang_data   Language-specific data for error messages and logging.
         * @param string $outputPath  The path where the generated QR code images will be saved.
         *
         * @return QRCodeGenerator  The instance of the QRCodeGenerator class.
         */
        public static function getInstance($outputPath)
        {
            if (!isset(self::$instance)) {
                self::$instance = new self($outputPath);
            }

            return self::$instance;
        }

        /**
         * Clean the instance of QRCodeGenerator using the Singleton pattern.
         * This method invokes the 'clean' method of the existing instance to perform cleanup operations.
         */
        public static function cleanInstance()
        {
            if (isset(self::$instance)) {
                self::$instance->clean();
            }
        }

        /**
         * Generate a QR code using the Bacon library.
         *
         * @param string $url        The URL or data to encode in the QR code.
         * @param string $name       The name of the QR code file (excluding extension).
         * @param string $correction The error correction level ('L', 'M', 'Q', 'H').
         * @param int    $size       The size of the QR code (128 to 2048 pixels).
         * @param int    $margin     The margin size (0 to 128 pixels).
         * @param string $logoPath   The path to the logo image file (optional).
         *
         * @return int Returns 0 on success. 
         *             Returns -1 if the correction level is invalid.
         *             Returns -2 if the size is invalid. 
         *             Returns -3 if the margin is invalid.
         *             Returns -4 if the margin is too large. 
         *             Returns -5 if the correction level is 'H' and a logo is provided.
         *             Returns -6 if the logo is not square.
         *             Returns -7 if server don't have permissions to create outputfolder
         *             Returns -8 if the outputfolder newly created don't have permission to write qr inside
         */
        function generateQRCode($url, $name, $correction, $size, $margin, $logoPath = "")
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

                        // if win
                        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                            $qrImagePath = $this->outputPath . $name . '.png';
                        } else {
                            $qrImagePath = $this->outputPath . $name . '.png';
                        }

                        // If the directory is not null and does not exist
                        if ($this->outputPath && !is_dir($this->outputPath)) {
                            // Attempt to create the directory
                            if (!mkdir($this->outputPath, 0777, true)) {
                                // If directory creation fails, return an error code
                                return -7;
                            }
                        }

                        // Check if the directory has write and read permissions
                        if (!is_writable($this->outputPath) || !is_readable($this->outputPath)) {
                            // Change the directory permissions to 0777
                            chmod($this->outputPath, 0777);

                            // Check again if the directory has the correct permissions
                            if (!is_writable($this->outputPath) || !is_readable($this->outputPath)) {
                                // If permissions are still incorrect, return an error code
                                return -8;
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

        /**
         * Clean up old files (qr) in the output directory.
         *
         * This method scans the output directory for files and removes those that are older than 30 days.
         * It helps maintain the cleanliness of the output directory, especially when generating QR codes over time.
         *
         * @return void
         */
        public function clean():void {

            // check if outputPath folder exist
            if ($this->outputPath !== null && is_dir($this->outputPath)) {
                // Open folder
                $files = scandir($this->outputPath);
                
                // loop inside the folder
                foreach ($files as $file) {
                    if ($file !== '.' && $file !== '..') {
                        $filePath = $this->outputPath . $file;
                        
                        // if it's a file
                        if (is_file($filePath)) {
                            // get last modified date
                            $lastModified = filemtime($filePath);
                            
                            // calculating day difference
                            $difference = time() - $lastModified;

                            // convert difference to " day "
                            $daysDifference = floor($difference / (60 * 60 * 24));
                            
                            // if file is older than 30 days
                            if ($daysDifference > 30) {
                                
                                // delete file
                                unlink($filePath); 
                            }
                        }
                    }
                }
            }
        }
    }
?>