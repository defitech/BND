<?php

include_once '../config.php';
header("Content-Type: image/jpeg" ); 

// récupération du nom de l'image sans chemin
$image = $_GET['i'];
// récupération de l'info thumb (=1) ou mini (inexistant ou =0)
$thumb = isset($_GET['t']) ? $_GET['t'] : null;

if (!$thumb) {
    // si on affiche les minis, on check si elle existe et si oui on l'affiche
    $path = Library_Book::getMiniPath(true);
    if (file_exists($path . $image)) {
        echo file_get_contents($path . $image);
        exit;
    }
}

// si y'a pas de mini ou qu'on veut afficher la thumb, on le fait si elle existe
$path = Library_Book::getThumbPath(true);
if (file_exists($path . $image)) {
    echo file_get_contents($path . $image);
    exit;
}

// si rien n'existe, on affiche la photo "pas d'aperçu"
$path = Library_Config::getInstance()->getRoot() . 'resources/images/';
echo file_get_contents($path . 'empty' . ($thumb ? '' : 'small') . '.jpg');
