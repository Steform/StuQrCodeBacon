<?php
    // Error if needed
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    // starting session if needed
    if(session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    require_once(__DIR__ . '/Utils/lang.php');

?>

<!DOCTYPE html>
<html lang="<?php echo ($lang_data['lang']); ?>">

<head>
    <meta charset="UTF-8">

    <title><?php echo ($lang_data['title']); ?></title>
    <link href="node_modules/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="/css/StuQr.css">

    <link rel="icon" href="/img/fav.webp" type="image/x-icon">

</head>


<?php
    require_once(__DIR__ . '/Utils/qrGen.php');
?>


<body>

    <div id="consentModal" class="modal">
        <div class="modal-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <h2><?php echo($lang_data['titleconsent']); ?></h2>
                        <p><?php echo($lang_data['consentinfo1']); ?></p>
                        <p><?php echo($lang_data['consentinfo2']); ?></p>
                        <p><?php echo($lang_data['consentinfo3']); ?></p>
                    </div>
                    <div class="col-12 col-md-6 mt-5 consent-div">
                        <a href="#" class="consent-link" onclick="handleConsent(true)"><p class="consent-text accept"><?php echo($lang_data['iconsent']); ?></p></a>
                    </div>
                    <div class="col-12 col-md-6 mt-5 consent-div">
                        <a href="#" class="consent-link" onclick="handleConsent(false)"><p class="consent-text reject"><?php echo($lang_data['inotconsent']); ?></p></a>
                    </div>
                    <div class="col-12 mt-5">
                        <a href="/history.php" target="_blank"><p><?php echo($lang_data['consentinfo4']); ?></p></a>
                    </div>
                </div>
            </div>


            
        </div>
    </div>

    <?php
        require_once(__DIR__ . '/parts/header.php');
    ?>


    <div class="container">        
        <div class="row">
            <div class="col-12 col-md-7">
                <form action="index.php" method="post" enctype="multipart/form-data">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12 mt-5">
                                <label for="url"><?php echo($lang_data['formtxt']); ?></label>
                            </div>
                            <div class="col-12 mt-2">
                                <textarea id="url" name="url" required pattern="^(https?|ftp)://.*$" rows="4" cols="25"></textarea>
                            </div>
                            <div class="col-12 mt-2">
                                <label for="correction"><?php echo($lang_data['formcorrectionlevel']); ?></label>
                            </div> 
                            <div class="col-12 mt-2">
                                <select id="correction" name="correction">
                                    <option value="" disabled hidden><?php echo($lang_data['formcorrectionleveldefault']); ?></option>
                                    <option value="L">L</option>
                                    <option value="M">M</option>
                                    <option value="Q">Q</option>
                                    <option value="H" selected>H</option>
                                </select>
                            </div>
                            <div id="fileInput">
                                <div class="col-12 mt-3">
                                    <label for="file"><?php echo($lang_data['addlogo']); ?></label>
                                </div>
                                <div class="col-12 mt-3">
                                    <input type="file" id="file" name="file" accept=".png, .jpg, .jpeg">
                                </div>
                            </div>
                            <div class="col-12 mt-2">
                                <label for="size"><?php echo($lang_data['sizeselect']); ?></label>
                            </div>
                            <div class="col-12 mt-2">
                                <select id="size" name="size">
                                    <?php
                                    // loop generating a size in px incremented in 128px steps
                                    for ($i = 128; $i <= 2048; $i += 128) {
                                        if ($i == 512){
                                            echo "<option value=\"$i\" selected>$i px</option>";
                                        }else{
                                            echo "<option value=\"$i\">$i px</option>";
                                        }
                                        
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-12 mt-2">
                                <label for="margin"><?php echo($lang_data['marginselect']); ?></label>
                            </div>
                            <div class="col-12 mt-2">
                                <select id="margin" name="margin">
                                    <?php
                                    // loop that generate a margin in px incremented in 2px steps
                                    for ($i = 0; $i <= 128; $i += 2) {
                                        if ($i == 8){
                                            echo "<option value=\"$i\" selected>$i px</option>";
                                        }else{
                                            echo "<option value=\"$i\">$i px</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-12 mt-2">
                                <label for="captcha"><?php echo($lang_data['captchacodetext']); ?></label>
                            </div>
                            <div class="col-12 mt-2">
                                <input type="text" id="captcha" name="captcha"> <img src="/Utils/CaptchaImg.php" alt="captcha code">
                            </div>
                            <div class="col-12 mt-2 mb-5">
                                <input type="hidden" name="csrf_token" value="<?php echo ($_SESSION['csrf_token']); ?>">
                                <input type="submit" value="<?php echo($lang_data['generatetext']); ?>">
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-12 col-md-5 mt-5">
                <div class="container-fluid">
                    <div class="row">
                        <?php

                            // if qr generated
                            if (isset($generated)){
                                // if qr is ok
                                if ($generated === 0) {
                                    echo'
                                        <div class="col-12 d-flex flex-column align-items-center">
                                            <img class="qrgen" src="./qrgen/' . htmlspecialchars($name) . '.png" alt="QR Code">
                                        </div>
                                        <a href="./qrgen/' . htmlspecialchars($name) . '.png" download="'.htmlspecialchars($name).'.png">
                                        <div class="col-12 d-flex flex-column align-items-center mt-5">
                                            
                                                <p class="qrbutton">'.$lang_data['downloadqr'].'</p>
                                            
                                        </div>
                                        </a>';
                                // if error when generated
                                } else {
                                    // echo logo
                                    echo '<img class="qrgen mb-5 mt-5" src="./img/qrcodes.webp" alt="Logo QR Code">';
                                }
                            // first start
                            } else {
                                // echo logo
                                echo '<img class="qrgen mb-5 mt-5" src="./img/qrcodes.webp" alt="Mogo QR Code">';
                            }
                        ?>
                    </div>
                </div>
            </div>
            <?php
                if(isset($message)){
                    echo('<div class="col-12 mt-5"><p>'.htmlspecialchars($message).'</p></div>');
                }
            ?>
        </div>
    </div>    

    <?php
        require_once(__DIR__ . '/parts/footer.php');
    ?>

    <script src="/js/StuQr.js"></script>
    <script>
    // retrieval of generated qr codes for insertion in user history
    const consent = localStorage.getItem('consentGiven');
    if (consent === null)
    {
        // modal by id
        const modal = document.getElementById('consentModal');
        // tell user to activate consent
        modal.style.display = 'flex';

    }else{
        // if user consent to history
        if (consent === 'true'){

            // Find the div ID qrCodeData
            const qrCodeDataDiv = document.getElementById('qrCodeData');

            // if we have
            if (qrCodeDataDiv) {
                // getting attribute for history
                const qrCodeName = qrCodeDataDiv.getAttribute('qrcode-name');

                // if we have attribute
                if (qrCodeName) {
                    // add the new qr to history
                    addToQRCodeHistory(qrCodeName);
                    }
                }

        }
    }
    </script>
</body>
</html>

