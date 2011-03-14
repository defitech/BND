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

    private $_log;

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
     * Check si un user est connecté
     *
     * @param integer $right le niveau de droit souhaité
     * @return Library_User un User s'il y en a, false sinon
     */
    public function issetAUser($right = 1) {
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
                // si la row existe et que ses droits sont bons, on retourne
                // le user
                return $result->right >= $right ? $result : false;
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
    public function getUser($right = 1) {
        return $this->issetAUser($right);
    }

    /**
     * Teste si un user est connecté. Si non, lance une exception
     *
     * @param integer $right le niveau de droit souhaité
     * @return boolean
     * @throws Exception
     */
    public function testIssetAuser($right = 1) {
        if (!$this->getUser($right)) {
            throw new Exception('Pas de user connecté');
        }
    }

    /**
     * Retourne un loggeur Zend
     *
     * @return Zend_Log
     */
    public function log() {
        if (!$this->_log) {
            $this->_log = new Zend_Log();
            $logger->addWriter(new Zend_Log_Writer_Stream($this->getData()->path->log));
        }
        return $this->_log;
    }

}