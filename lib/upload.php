<?php

// Code for Session Cookie workaround
if     (isset($_POST['PHPSESSID'])) session_id($_POST['PHPSESSID']);
elseif (isset($_GET['PHPSESSID']))  session_id($_GET['PHPSESSID']);

include_once '../config.php';

try {
    $json = array(
        'success' => true,
        'msg' => 'OK'
    );

    $fieldname = 'Filedata';

    $upload = new Zend_File_Transfer_Adapter_Http();
    $upload->setDestination(Library_Book::getUploadPdfPath(true));

    if (!$upload->isUploaded($fieldname)) {
        ob_start();
        print_r($_SERVER);
        $c = ob_get_contents();
        ob_end_clean();
        throw new Exception('Fichier PDF non fourni. ' . $c);
    }
    $upload->addValidator('Count', false, 1);
    $upload->addValidator('Extension', false, 'pdf');
    if (!$upload->isValid($fieldname)) {
        throw new Exception('Le fichier fourni n\'est pas valide');
    }

    if (!$upload->receive()) {
        throw new Exception(implode(', ', $upload->getMessages()));
    }

    $json['name'] = Library_Book::getUploadPdfFolder() . $upload->getFileName();
    Library_Config::log('Document uploadé');
    
} catch (Exception $e) {
    Library_Config::log()->err($e->getMessage() . ' : ' . $e->getTraceAsString());
    $json = array(
        'success' => false,
        'error' => $e->getMessage() . ' : ' . $e->getTraceAsString()
    );
}

echo Zend_Json::encode($json);