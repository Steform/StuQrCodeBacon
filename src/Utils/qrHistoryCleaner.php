<?php


$folder = './qrgen';

// check if qrgen folder exist
if (is_dir($folder)) {
    // Open folder
    $files = scandir($folder);
    
    // loop inside the folder
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $filePath = $folder . $file;
            
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