<?php

include_once '../config.php';

// On ne met un header JSON que s'il n'y a pas d'un upload fichier
if (!isset($_FILES['csv']) && !isset($_FILES['thumbfile']) && !isset($_FILES['pdffile']) && !isset($_FILES['bgfile'])) {
    header('Content-type: application/json');
}

echo Zend_Json::encode(Library_Controller::output($_REQUEST));