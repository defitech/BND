<?php

/**
 * Gestion ultra basique d'utilisateurs et droits
 *
 * @package Library
 * @copyright Library
 */
class Library_User extends Zend_Db_Table_Abstract {

    /**
     * La colonne primaire/unique/auto-incrémentale
     * @var string
     */
    protected $_primary = 'id';

    protected $_dependentTables = array('Library_User_Download');

}