<div class="container-fluid header">
    <div class="row">
        <div class="col-12">
            <div class="container">
                <div class="row">
                    <div class="col-12 col-md-2 d-flex flex-column align-items-center">
                        <img src="img/qrlogo.webp" class="qrlogo" alt="<?php echo($lang_data['altStuQrLogo']); ?>">    
                    </div>      
                    <div class="col-12 col-md-8">

                        <h1 class="mt-4 ms-5 mb-5 h1-header"><?php echo($lang_data['h1title']); ?></h1>
                    </div>
                    <div class="col-12 col-md-2">
                        <div class="container-fluid">
                            <div class="row">

                                <?php
                                // getting used protocol (http ou https)
                                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";

                                // get domain
                                $domain = $_SERVER['HTTP_HOST'];

                                // get subfolder
                                $subfolder = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');

                                // generate complete url
                                $fullUrl = "$protocol://$domain$subfolder/$_SERVER[SCRIPT_NAME]";

                                ?>


                                <div class="col-6 mt-3 navflag mb-3"><a href="<?php echo($fullUrl."?lang=".$lang_data['lng1']);?>"><img class="flag" src="<?php echo("/img/lang/".$lang_data['lng1'].".svg"); ?>" alt="<?php echo($lang_data['altLng1']);?>"></a></div>
                                <div class="col-6 mt-3 navflag mb-3"><a href="<?php echo($fullUrl."?lang=".$lang_data['lng2']);?>"><img class="flag" src="<?php echo("/img/lang/".$lang_data['lng2'].".svg"); ?>" alt="<?php echo($lang_data['altLng2']);?>"></a></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>    
    </div>
</div>
