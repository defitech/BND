<?php

date_default_timezone_set('Europe/Berlin');

// chemin vers la racine du site
$dir = dirname(__FILE__) . '/';

// récupération du chemin vers le Zend Framework. On chope l'instance. C'était
// à l'époque un nom de section dans un fichier INI de Zend. Maintenant avec
// git, ce sera toujours "production", le fichier de config étant changé à la
// mano directement sur le serveur
$instance = 'production';

// on chope le config.ini qui contient toutes les infos de configuration
$configFile = $dir . 'config/config.ini';
$ini = parse_ini_file($configFile, true);
if (isset($ini[$instance])) {
    // si une config pour l'instance existe, on la prend
    $pathzend = $ini[$instance]['path.zend'];
    ini_set('display_errors', $ini[$instance]['php.display_errors']);
} else {
    // autrement, on prend la production par défaut
    $pathzend = $ini['production']['path.zend'];
    ini_set('display_errors', $ini['production']['php.display_errors']);
}


// préparation et initalisation du Zend_Loader afin de pouvoir loader les
// classes PHP à la volée
set_include_path(get_include_path() . PATH_SEPARATOR . $pathzend . PATH_SEPARATOR . $dir . 'lib/' . PATH_SEPARATOR . $dir . 'lang/');

require_once 'Zend/Loader/Autoloader.php';
$autoloader = Zend_Loader_Autoloader::getInstance();

$autoloader->registerNamespace('Library_');

// set de la config, qui se base sur le config.ini
$config = Library_Config::getInstance($instance, $configFile, $dir);

ini_set('magic_quotes_gpc', 0);

// initialisation de la base de données
$db = $config->getData()->db;
Zend_Registry::set('db', Zend_Db::factory('PDO_Mysql', array_merge($db->toArray(), array(
    'charset' => 'utf8',
    'driver_options' => array(
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8;'
    )
))));
Zend_Db_Table_Abstract::setDefaultAdapter(Zend_Registry::get('db'));
Zend_Registry::get('db')->getConnection();

Zend_Session::start();


register_shutdown_function('Library_Util::manageFatalErrors');
