<?php

// get language from url
if (isset($_GET['lang'])) {

    // clean getted language
    $lang = filter_input(INPUT_GET, 'lang', FILTER_VALIDATE_REGEXP, [
        "options" => ["regexp" => "/^[a-zA-Z]{2}$/"] // Expression régulière pour deux lettres alphabétiques
    ]);

    // check if lang is not null and is valid
    if ($lang === false || $lang === null) {

        $lang = "en";

    }

} else {

    // set language
    $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);

    // if not ok, default language = en
    if (!preg_match("/^[a-zA-Z]{2}$/", $lang)) {
        $lang = "en";
    }
}

// Load xx.json
$lang_file = file_get_contents("./lang/". $lang . '.json');
$lang_data = json_decode($lang_file, true);

?>