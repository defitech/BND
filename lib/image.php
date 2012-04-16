<?php

include_once '../config.php';

$image = $_GET['i'];
$thumb = isset($_GET['t']) ? $_GET['t'] : null;

if (!$thumb) {
    $path = Library_Book::getMiniPath(true);
    if (file_exists($path . $image)) {
        echo file_get_contents($path . $image);
        exit;
    }
}

$path = Library_Book::getThumbPath(true);
if (file_exists($path . $image)) {
    echo file_get_contents($path . $image);
    exit;
}

$path = Library_Config::getInstance()->getRoot();
echo file_get_contents($path . 'resources/images/emptysmall.jpg');
