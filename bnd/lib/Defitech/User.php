<?php

/**
 * Gestion ultra basique d'utilisateurs et droits
 *
 * @package Defitech
 * @copyright Defitech
 */
class Defitech_User extends Zend_Db_Table_Abstract {

    /**
     * La colonne primaire/unique/auto-incrémentale
     * @var string
     */
    protected $_primary = 'id';

    /**
     * Les autres colonnes de cette tables sont:
     *
     * - login (varchar) : le login
     * - pass (varchar) : le mot de passe (en md5)
     * - right (smallint) : le droit
     * - last_connected (timestamp) : la date de dernière connexion
     */

}