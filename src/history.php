<?php
    // Active l'affichage des erreurs
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

    <title><?php echo ($lang_data['titleHistory']); ?></title>

    <link href="node_modules/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="/css/StuQr.css">

    <link rel="icon" href="/img/fav.webp" type="image/x-icon">

</head>


<body>

    <?php
        require_once(__DIR__ . '/parts/header.php');
    ?>


    <div class="container">        
        <div class="row">
            <div class="col-12 mt-5 mb-5">
                
                <?php // show history consent text ?>

                <h1><?php echo($lang_data['historicalConditionsTitle']); ?></h1>
                <p><?php echo($lang_data['dearUser']); ?></p>
                <p><?php echo($lang_data['improveExperience']); ?></p>
                <h2><?php echo($lang_data['implicationsTitle']); ?></h2>
                <p><?php echo($lang_data['quickAccess']); ?></p>
                <p><?php echo($lang_data['noDataSharing']); ?></p>
                <p><?php echo($lang_data['accessFromCurrentDevice']); ?></p>
                <p><?php echo($lang_data['deletionAfter30Days']); ?></p>

            </div>
        </div>
    </div>    

    <?php
        require_once(__DIR__ . '/parts/footer.php');
    ?>

    <script src="/js/StuQr.js"></script>

</body>
</html>

