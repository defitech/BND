<?php

/**
 * Classe de configuration de l'environnement
 *
 * @package Library
 * @copyright Library
 */
class Library_Config {

    /**
     * Le singleton
     * @var Library_Config
     */
    public static $instance;

    /**
     * L'instance que l'on souhaite (p.ex. "local" ou "production")
     * @var string
     */
    private $inst;

    /**
     * L'objet de config Zend
     * @var Zend_Config_Ini
     */
    private $data;

    private $root;

    /**
     * Retourne l'instance unique signleton
     *
     * @param string $instance
     * @param string $dir le chemin + fichier ini, pour la config
     * @param string $root le chemin vers la racine du site
     * @return Library_Config
     */
    public static function getInstance($instance = null, $dir = null, $root = null) {
        if (!self::$instance) {
            self::$instance = new self($instance, $dir, $root);
        }
        return self::$instance;
    }

    /**
     * Constructeur privé. Utiliser getInstance pour récupérer l'objet
     *
     * @param string $instance
     * @param string $dir le chemin + fichier ini, pour la config
     * @param string $root le chemin vers la racine du site
     */
    private function __construct($instance, $dir, $root) {
        $this->inst = $instance;
        $this->root = $root;
        $this->data = new Zend_Config_Ini($dir, $instance);
    }

    /**
     * Retourne l'objet de config Zend
     *
     * @return Zend_Config_Ini
     */
    public function getData() {
        return $this->data;
    }

    public function getRoot() {
        return $this->root;
    }
    
    /**
     * Retourne le chemin web complet jusqu'à la racine, genre:
     * http://localhost/sousDossier/bnd/
     * 
     * @return string le chemin web avec un / à la fin
     */
    public function getWeb() {
        $host = 'http://' . $_SERVER['HTTP_HOST'];
        return $host . substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '/lib')) . '/';
    }

    /**
     * Check si un user est connecté
     *
     * @param integer $right le niveau de droit souhaité
     * @return Library_User un User s'il y en a, false sinon
     */
    public function issetAUser($right = null) {
        // récupération de la session
        $session = new Zend_Session_Namespace('Library');
        // check si un login + pass sont stockés dans la session
        if (isset($session->login) && isset($session->pass)) {
            $user = new Library_User();
            // récupération de la row dans la bd
            $result = $user->fetchRow($user->select()
                ->where('login = ?', $session->login)
                ->where('pass = ?', $session->pass)
            );
            if ($result) {
                // si on a pas demandé de droit particulier, on est ok
                if ($right === null) return $result;
                // si la row existe et que ses droits sont bons, on retourne
                // le user. Plus le droit tend vers 1, plus il est admin. Donc
                // si le droit de l'utilisateur connecté est plus petit que
                // celui demandé, ça passe.
                return $result->right && $result->right <= $right ? $result : false;
            }
        }
        return false;
    }

    /**
     * Récupère l'utilisateur connecté (false si personne ne l'est)
     *
     * @param integer $right le niveau de droit souhaité
     * @return Library_User
     */
    public function getUser($right = null) {
        return $this->issetAUser($right);
    }

    /**
     * Teste si un user est connecté. Si non, lance une exception
     *
     * @param integer $right le niveau de droit souhaité
     * @return boolean
     * @throws Exception
     */
    public function testIssetAuser($right = null) {
        if (!$this->getUser($right)) {
            throw new Exception(Library_Wording::get('no_connected_user'));
        }
    }
    
    /**
     * Retourne le poids maximal d'un fichier PDF pour l'upload
     * 
     * @return string 
     */
    public function getMaxPostSize() {
        // valeur max officielle du serveur
        $init = ini_get('post_max_size');
        // valeur qu'on set dans le plugin flash pour l'upload
        $conf = '500mb';
        return $conf;
    }
    
    /**
     * Retourne le fond d'écran choisi pour l'application
     * 
     * @return string le nom du fichier (avec ext.) dans resources/background/
     */
    public function getBackground() {
        return @file_get_contents($this->getRoot() . 'config/background.txt');
    }





    /**
     * --------------------------------------------------------------
     *              Gestion du log dans un fichier texte
     * --------------------------------------------------------------
     */

    /**
     * Objet de log
     * @var Zend_Log
     */
    private $_log;

    /**
     * Nom du fichier de log mensuel
     * @var string
     */
    private $logfile = 'log_%s.txt';

    /**
     * Retourne un loggeur Zend
     *
     * @return Zend_Log
     */
    public function getLog() {
        if (!$this->_log) {
            $this->_log = new Zend_Log();
            $path = $this->getData()->path->log;
            if (!is_dir($path)) {
                mkdir($path, 0766);
            }
            // définition du user connecté, s'il y en a un
            $u = $this->getUser();
            $user = $u ? $u->login : (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown');

            // définition du log
            $logfile = $path . sprintf($this->logfile, date('Y-m'));
            $writer = new Zend_Log_Writer_Stream($logfile);
            $format = '%timestamp% ' . $user . ' %priorityName% (%priority%): %message%' . PHP_EOL;
            $writer->setFormatter(new Zend_Log_Formatter_Simple($format));
            $this->_log->addWriter($writer);
        }
        return $this->_log;
    }

    /**
     * Retourne un loggeur Zend. Méthode raccourcis
     *
     * @param string $str si on la renseigne, enregistrera un log info()
     * @return Zend_Log
     */
    public static function log($str = null) {
        $c = self::getInstance();
        if ($str) {
            $c->getLog()->info($str);
        }
        return $c->getLog();
    }

}